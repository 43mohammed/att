<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * تسجيل الحضور
     */
    public function recordAttendance(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:attendance_sessions,id',
            'verification_method' => 'required|in:qrcode,nfc,manual,gps',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $session = AttendanceSession::findOrFail($validated['session_id']);

        // التحقق من أن الجلسة نشطة
        if ($session->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'الجلسة غير نشطة',
            ], 400);
        }

        // التحقق من أن الطالب مسجل في المقرر نفسه
        $enrolled = Enrollment::where('student_id', $user->id)
            ->where('course_id', $session->course_id)
            ->exists();

        if (! $enrolled) {
            return response()->json([
                'success' => false,
                'message' => 'أنت غير مسجل في هذا المقرر',
            ], 403);
        }

        // التحقق من تطابق القسم إن كان موجوداً
        if ($user->department && $session->course->department && $user->department !== $session->course->department) {
            return response()->json([
                'success' => false,
                'message' => 'أنت غير من نفس القسم لمقرر هذه الجلسة',
            ], 403);
        }

        // التحقق من عدم تسجيل الحضور مسبقاً
        $existing = AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'تم تسجيل حضورك مسبقاً في هذه الجلسة',
            ], 409);
        }

        // التحقق من GPS وموقع الغرفة إذا كانت معلومات الفصل متوفرة
        $distance = null;
        if ($session->classroom_latitude && $session->classroom_longitude) {
            if (! $validated['latitude'] || ! $validated['longitude']) {
                if ($session->gps_required) {
                    return response()->json([
                        'success' => false,
                        'message' => 'يتطلب هذا الفصل بيانات GPS لتسجيل الحضور',
                    ], 400);
                }
            } else {
                $distance = $this->calculateDistance(
                    $validated['latitude'],
                    $validated['longitude'],
                    $session->classroom_latitude,
                    $session->classroom_longitude
                );

                if ($distance > 100) { // 100 متر
                    return response()->json([
                        'success' => false,
                        'message' => 'أنت بعيد جداً عن الفصل الدراسي',
                        'distance' => round($distance, 2),
                    ], 403);
                }
            }
        }

        // تسجيل الحضور
        $record = AttendanceRecord::create([
            'session_id' => $session->id,
            'student_id' => $user->id,
            'course_id' => $session->course_id,
            'marked_at' => now(),
            'verification_method' => $validated['verification_method'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'distance_from_classroom' => $distance,
        ]);

        // تسجيل العملية
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'record_attendance',
            'description' => "تسجيل حضور في جلسة {$session->course->name}",
            'ip_address' => $request->ip(),
        ]);

        // التحقق من نسبة الغياب
        $this->checkAbsenceThreshold($user, $session->course_id);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الحضور بنجاح',
            'record' => $record,
        ]);
    }

    /**
     * الحصول على سجل الحضور للطالب
     */
    public function getStudentAttendance($courseId)
    {
        $user = Auth::user();
        $records = AttendanceRecord::where('student_id', $user->id)
            ->where('course_id', $courseId)
            ->with(['session', 'course'])
            ->orderBy('marked_at', 'desc')
            ->get();

        // حساب الإحصائيات
        $total = AttendanceSession::where('course_id', $courseId)
            ->where('status', 'closed')
            ->count();

        $attended = $records->count();
        $absent = $total - $attended;
        $percentage = $total > 0 ? ($attended / $total) * 100 : 0;

        return response()->json([
            'success' => true,
            'records' => $records,
            'stats' => [
                'total' => $total,
                'attended' => $attended,
                'absent' => $absent,
                'percentage' => round($percentage, 2),
            ],
        ]);
    }

    /**
     * الحصول على سجل الحضور للمحاضر
     */
    public function getSessionAttendance($sessionId)
    {
        $session = AttendanceSession::findOrFail($sessionId);
        $user = Auth::user();

        // التحقق من الصلاحيات
        if ($session->course->instructor_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد صلاحية',
            ], 403);
        }

        $records = AttendanceRecord::where('session_id', $sessionId)
            ->with('user')
            ->get();

        $students = $session->course->enrollments()->count();
        $attended = $records->count();
        $absent = $students - $attended;

        return response()->json([
            'success' => true,
            'records' => $records,
            'stats' => [
                'total_students' => $students,
                'attended' => $attended,
                'absent' => $absent,
                'percentage' => $students > 0 ? round(($attended / $students) * 100, 2) : 0,
            ],
        ]);
    }

    /**
     * تعديل سجل الحضور يدوياً
     */
    public function updateAttendance(Request $request, $recordId)
    {
        $record = AttendanceRecord::findOrFail($recordId);
        $user = Auth::user();

        // التحقق من الصلاحيات
        if ($record->session->course->instructor_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد صلاحية',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:present,absent,excused',
        ]);

        $oldStatus = $record->status;
        $record->update(['status' => $validated['status']]);

        // تسجيل العملية
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'update_attendance',
            'description' => "تحديث حضور الطالب {$record->user->name} من {$oldStatus} إلى {$validated['status']}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث السجل بنجاح',
        ]);
    }

    /**
     * حساب المسافة بين نقطتين (Haversine Formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // بالمتر

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * التحقق من نسبة الغياب
     */
    private function checkAbsenceThreshold($user, $courseId)
    {
        $sessions = AttendanceSession::where('course_id', $courseId)
            ->where('status', 'closed')
            ->count();

        $attended = AttendanceRecord::where('student_id', $user->id)
            ->where('course_id', $courseId)
            ->count();

        if ($sessions === 0) return;

        $absencePercentage = (($sessions - $attended) / $sessions) * 100;

        // إرسال إشعار عند الوصول إلى 15%
        if ($absencePercentage >= 15 && $absencePercentage < 25) {
            $existingNotification = Notification::where([
                'user_id' => $user->id,
                'type' => 'absence_warning',
            ])->where('data->course_id', $courseId)->first();

            if (!$existingNotification) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'absence_warning',
                    'title' => 'تحذير: نسبة غياب عالية',
                    'message' => "نسبة غيابك في المقرر وصلت إلى " . round($absencePercentage, 2) . "%",
                    'data' => json_encode(['course_id' => $courseId, 'percentage' => $absencePercentage]),
                    'is_read' => false,
                ]);
            }
        }

        // إرسال إشعار عند الوصول إلى 25%
        if ($absencePercentage >= 25) {
            $existingNotification = Notification::where([
                'user_id' => $user->id,
                'type' => 'absence_critical',
            ])->where('data->course_id', $courseId)->first();

            if (!$existingNotification) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'absence_critical',
                    'title' => 'تنبيه حرج: تجاوزت حد الغياب المسموح',
                    'message' => "نسبة غيابك في المقرر وصلت إلى " . round($absencePercentage, 2) . "% - قد تفقد حق الامتحان",
                    'data' => json_encode(['course_id' => $courseId, 'percentage' => $absencePercentage]),
                    'is_read' => false,
                ]);
            }
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * الحصول على التقارير
     */
    public function getReports(Request $request)
    {
        $user = Auth::user();
        $type = $request->query('type', 'daily'); // daily, weekly, monthly
        $courseId = $request->query('course_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Report::query();

        if ($user->role === 'student') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'instructor') {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $reports = $query->with(['course', 'user'])
            ->orderBy('date', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'reports' => $reports,
        ]);
    }

    /**
     * إنشاء تقرير يومي
     */
    public function generateDailyReport($courseId, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $course = Course::findOrFail($courseId);

        // الحصول على جميع الجلسات في هذا اليوم
        $sessions = AttendanceSession::where('course_id', $courseId)
            ->whereDate('session_date', $date)
            ->get();

        $students = $course->enrollments()->count();
        $attended = 0;
        $absent = 0;

        foreach ($sessions as $session) {
            $sessionAttended = AttendanceRecord::where('session_id', $session->id)->count();
            $attended += $sessionAttended;
            $absent += ($students - $sessionAttended);
        }

        $percentage = $students > 0 ? ($attended / ($attended + $absent)) * 100 : 0;

        $report = Report::create([
            'course_id' => $courseId,
            'type' => 'daily',
            'date' => $date,
            'total_students' => $students,
            'attended' => $attended,
            'absent' => $absent,
            'percentage' => $percentage,
            'data' => json_encode([
                'sessions' => $sessions->count(),
                'details' => $sessions->map(function ($session) {
                    return [
                        'session_id' => $session->id,
                        'attended' => AttendanceRecord::where('session_id', $session->id)->count(),
                    ];
                })->toArray(),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * إنشاء تقرير أسبوعي
     */
    public function generateWeeklyReport($courseId, $startDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        $course = Course::findOrFail($courseId);
        $students = $course->enrollments()->count();

        $sessions = AttendanceSession::where('course_id', $courseId)
            ->whereBetween('session_date', [$startDate, $endDate])
            ->get();

        $attended = AttendanceRecord::whereIn('session_id', $sessions->pluck('id'))->count();
        $totalPossible = $sessions->count() * $students;
        $absent = $totalPossible - $attended;
        $percentage = $totalPossible > 0 ? ($attended / $totalPossible) * 100 : 0;

        $report = Report::create([
            'course_id' => $courseId,
            'type' => 'weekly',
            'date' => $startDate,
            'total_students' => $students,
            'attended' => $attended,
            'absent' => $absent,
            'percentage' => $percentage,
            'data' => json_encode([
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'sessions' => $sessions->count(),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * إنشاء تقرير شهري
     */
    public function generateMonthlyReport($courseId, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $course = Course::findOrFail($courseId);
        $students = $course->enrollments()->count();

        $sessions = AttendanceSession::where('course_id', $courseId)
            ->whereBetween('session_date', [$startDate, $endDate])
            ->get();

        $attended = AttendanceRecord::whereIn('session_id', $sessions->pluck('id'))->count();
        $totalPossible = $sessions->count() * $students;
        $absent = $totalPossible - $attended;
        $percentage = $totalPossible > 0 ? ($attended / $totalPossible) * 100 : 0;

        $report = Report::create([
            'course_id' => $courseId,
            'type' => 'monthly',
            'date' => $startDate,
            'total_students' => $students,
            'attended' => $attended,
            'absent' => $absent,
            'percentage' => $percentage,
            'data' => json_encode([
                'month' => $month,
                'year' => $year,
                'sessions' => $sessions->count(),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * تصدير التقرير إلى PDF
     */
    public function exportPDF($reportId)
    {
        $report = Report::findOrFail($reportId);
        $user = Auth::user();

        // التحقق من الصلاحيات
        if ($user->role === 'student' && $report->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'لا توجد صلاحية'], 403);
        }

        if ($user->role === 'instructor' && $report->course->instructor_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'لا توجد صلاحية'], 403);
        }

        // هنا يمكن استخدام مكتبة PDF مثل DomPDF أو mPDF
        // للآن سنعيد رابط التحميل فقط

        return response()->json([
            'success' => true,
            'message' => 'جاري تحضير التقرير للتصدير',
            'download_url' => "/reports/{$reportId}/download-pdf",
        ]);
    }

    /**
     * تصدير التقرير إلى Excel
     */
    public function exportExcel($reportId)
    {
        $report = Report::findOrFail($reportId);
        $user = Auth::user();

        // التحقق من الصلاحيات
        if ($user->role === 'student' && $report->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'لا توجد صلاحية'], 403);
        }

        if ($user->role === 'instructor' && $report->course->instructor_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'لا توجد صلاحية'], 403);
        }

        // هنا يمكن استخدام مكتبة Excel مثل PhpSpreadsheet
        // للآن سنعيد رابط التحميل فقط

        return response()->json([
            'success' => true,
            'message' => 'جاري تحضير التقرير للتصدير',
            'download_url' => "/reports/{$reportId}/download-excel",
        ]);
    }

    /**
     * الحصول على إحصائيات الطالب
     */
    public function getStudentStats($studentId)
    {
        $student = \App\Models\User::findOrFail($studentId);
        $user = Auth::user();

        // التحقق من الصلاحيات
        if ($user->role === 'student' && $student->id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'لا توجد صلاحية'], 403);
        }

        $courses = $student->enrollments()->with('course')->get();

        $stats = [];
        foreach ($courses as $enrollment) {
            $course = $enrollment->course;
            $sessions = AttendanceSession::where('course_id', $course->id)
                ->where('status', 'closed')
                ->count();

            $attended = AttendanceRecord::where('user_id', $student->id)
                ->where('course_id', $course->id)
                ->count();

            $stats[] = [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'total_sessions' => $sessions,
                'attended' => $attended,
                'absent' => $sessions - $attended,
                'percentage' => $sessions > 0 ? ($attended / $sessions) * 100 : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'student' => $student,
            'stats' => $stats,
        ]);
    }
}

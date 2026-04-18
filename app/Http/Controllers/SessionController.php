<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function create(Request $request)
    {
        $user = Auth::user();
        if (!$user->isInstructor() && !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'session_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'gps_required' => 'boolean',
            'nfc_active' => 'boolean',
            'classroom_name' => 'nullable|string|max:255',
            'custom_classroom_name' => 'nullable|string|max:255',
        ]);

        $selectedRoom = $validated['classroom_name'] ?? null;
        $customRoom = trim($validated['custom_classroom_name'] ?? '');
        $classroomName = $customRoom !== '' ? $customRoom : $selectedRoom;
        $classroomData = config('classrooms')[$selectedRoom] ?? null;

        if (($validated['gps_required'] ?? false) && ! $classroomData) {
            return response()->json(['error' => 'لتفعيل GPS يجب اختيار قاعة معروفة من القائمة أو إيقاف التفعيل.'], 422);
        }

        $qrToken = Str::random(20);
        $endTime = date('H:i', strtotime($validated['start_time'] . ' +1 hour'));

        $session = AttendanceSession::create([
            'course_id' => $validated['course_id'],
            'instructor_id' => $user->id,
            'session_date' => $validated['session_date'],
            'start_time' => $validated['session_date'] . ' ' . $validated['start_time'],
            'end_time' => $validated['session_date'] . ' ' . $endTime,
            'qr_code_token' => $qrToken,
            'gps_required' => $validated['gps_required'] ?? false,
            'nfc_active' => $validated['nfc_active'] ?? false,
            'classroom_name' => $classroomName,
            'classroom_latitude' => $classroomData['latitude'] ?? null,
            'classroom_longitude' => $classroomData['longitude'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'session' => $session,
            'qr_code_url' => route('session.qrcode', $session->id),
        ]);
    }

    public function close($id)
    {
        $session = AttendanceSession::findOrFail($id);
        $user = Auth::user();

        if ($session->instructor_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session->close();

        return response()->json([
            'success' => true,
            'message' => 'تم إغلاق الجلسة بنجاح',
        ]);
    }

    public function getActive($courseId)
    {
        $session = AttendanceSession::where('course_id', $courseId)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد جلسة نشطة',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }

    public function getQRCode($id)
    {
        $session = AttendanceSession::findOrFail($id);

        // Generate QR code (using a simple library)
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . 
                     urlencode(route('attendance.record', ['session' => $session->id, 'token' => $session->qr_code_token]));

        return response()->json([
            'success' => true,
            'qr_code_url' => $qrCodeUrl,
            'qr_token' => $session->qr_code_token,
        ]);
    }
}

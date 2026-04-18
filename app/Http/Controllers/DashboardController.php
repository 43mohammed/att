<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isInstructor()) {
            return $this->instructorDashboard();
        } else {
            return $this->studentDashboard();
        }
    }

    private function adminDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'total_courses' => Course::count(),
            'total_sessions' => AttendanceSession::count(),
            'total_records' => AttendanceRecord::count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    private function instructorDashboard()
    {
        $user = Auth::user();
        $courses = $user->courses;
        $activeSessions = AttendanceSession::where('instructor_id', $user->id)
            ->where('status', 'active')
            ->count();

        $stats = [
            'courses_count' => $courses->count(),
            'active_sessions' => $activeSessions,
            'total_students' => $courses->sum(fn($course) => $course->students->count()),
        ];

        return view('dashboard.instructor', compact('stats', 'courses'));
    }

    private function studentDashboard()
    {
        $user = Auth::user();
        $courses = $user->courses;

        $stats = [];
        foreach ($courses as $course) {
            $totalSessions = AttendanceSession::where('course_id', $course->id)->count();
            $attendedSessions = AttendanceRecord::where('student_id', $user->id)
                ->where('course_id', $course->id)
                ->count();

            $stats[$course->id] = [
                'total' => $totalSessions,
                'attended' => $attendedSessions,
                'percentage' => $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100) : 0,
            ];
        }

        return view('dashboard.student', compact('stats', 'courses'));
    }
}

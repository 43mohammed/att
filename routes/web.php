<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Models\User;
use App\Models\Course;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\Report;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes (Bypass for testing)
Route::middleware('no.cache.auth')->group(function () {
    Route::get('/login', function () {
        return response()->view('auth.login')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('login');

    Route::post('/login', function (Request $request) {
        // Simple authentication for demo purposes
        $email = $request->input('email');
        $password = $request->input('password');
        
        // Find user by email
        $user = \App\Models\User::where('email', $email)->first();
        
        if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            // Regenerate session to prevent session fixation
            session()->regenerate(true);
            
            // Set session data based on actual user data
            session([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);
            
            // Return redirect with cache control headers
            return redirect('/dashboard')->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }
        
        return back()->with('error', 'بيانات الدخول غير صحيحة');
    })->name('login.post');

    Route::get('/register', function () {
        return response()->view('auth.register')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('register');

    Route::post('/register', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'role' => 'required|in:admin,instructor,student',
        'student_id' => 'nullable|string|max:255',
        'password' => 'required|string|min:6|confirmed',
    ]);

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'] ?? null,
        'department' => $validated['department'] ?? null,
        'role' => $validated['role'],
        'student_id' => $validated['student_id'] ?? null,
        'password' => bcrypt($validated['password']),
    ]);

    return redirect('/login')->with('success', 'تم التسجيل بنجاح، يرجى تسجيل الدخول')->withHeaders([
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ]);
})->name('register.post');

    Route::post('/logout', function () {
        // Clear all session data regardless of current state
        session()->flush();
        session()->regenerate(true);
        
        // Return redirect with cache control headers
        return redirect('/login')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('logout');
});

// Dashboard Routes
Route::get('/dashboard', function () {
    // Check if user is authenticated
    if (!session('user_id')) {
        return redirect('/login');
    }
    
    $role = session('user_role', 'admin');
    
    $stats = [
        'total_users' => User::count(),
        'total_students' => User::where('role', 'student')->count(),
        'total_instructors' => User::where('role', 'instructor')->count(),
        'total_courses' => Course::count(),
        'total_sessions' => AttendanceSession::count(),
        'total_records' => AttendanceRecord::count(),
    ];

    if ($role === 'admin') {
        $latestUsers = User::orderByDesc('created_at')->limit(3)->get();
        $latestCourses = Course::orderByDesc('created_at')->limit(3)->get();
        $recentActivities = \App\Models\AuditLog::orderByDesc('created_at')->limit(4)->get();

        return view('dashboard.admin', compact('stats', 'latestUsers', 'latestCourses', 'recentActivities'));
    } elseif ($role === 'instructor') {
        $instructorId = session('user_id');

        $courses = Course::where('instructor_id', $instructorId)
            ->withCount('enrollments')
            ->get();

        $activeSessions = AttendanceSession::where('instructor_id', $instructorId)
            ->where('status', 'active')
            ->with('course')
            ->get();

        $recentRecords = AttendanceRecord::whereHas('course', function ($query) use ($instructorId) {
                $query->where('instructor_id', $instructorId);
            })
            ->with(['student', 'course'])
            ->orderByDesc('marked_at')
            ->limit(5)
            ->get();

        return view('dashboard.instructor', compact('stats', 'courses', 'activeSessions', 'recentRecords'));
    } else {
        // student's personalized dashboard data
        $studentId = session('user_id');

        $enrollments = Enrollment::where('student_id', $studentId)
            ->with(['course.instructor'])
            ->get();

        $courseIds = $enrollments->pluck('course_id')->unique();

        $totalSessions = $courseIds->isEmpty()
            ? 0
            : AttendanceSession::whereIn('course_id', $courseIds)->count();

        $totalAttended = $courseIds->isEmpty()
            ? 0
            : AttendanceRecord::where('student_id', $studentId)
                ->whereIn('course_id', $courseIds)
                ->count();

        $studentStats = [
            'total_records' => $totalAttended,
            'total_courses' => $enrollments->count(),
            'total_sessions' => $totalSessions,
        ];

        // per-course summaries
        $courseSummaries = $enrollments->map(function ($enrollment) use ($studentId) {
            $course = $enrollment->course;
            $sessions = \App\Models\AttendanceSession::where('course_id', $course->id)->count();
            $attended = \App\Models\AttendanceRecord::where('student_id', $studentId)
                ->where('course_id', $course->id)
                ->count();
            $absent = max(0, $sessions - $attended);
            $percentage = $sessions > 0 ? round($attended / $sessions * 100) : 0;

            return [
                'course' => $course,
                'attended' => $attended,
                'absent' => $absent,
                'sessions' => $sessions,
                'percentage' => $percentage,
            ];
        });

        $recentRecords = AttendanceRecord::where('student_id', $studentId)
            ->with('course')
            ->orderBy('marked_at', 'desc')
            ->limit(5)
            ->get();

        $notifications = Notification::where('student_id', $studentId)
            ->with('course')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.student', compact('stats', 'studentStats', 'courseSummaries', 'enrollments', 'recentRecords', 'notifications'));
    }
})->name('dashboard');

// Admin Routes
Route::prefix('admin')->group(function () {
    // Users Management
    Route::get('/users', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }
        
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    })->name('admin.users.index');
    
    Route::get('/users/create', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }
        
        return view('admin.users.form');
    })->name('admin.users.create');
    
    Route::get('/users/{user}/edit', function ($user) {
        if (!session('user_id')) {
            return redirect('/login');
        }
        
        $user = User::findOrFail($user);
        return view('admin.users.form', compact('user'));
    })->name('admin.users.edit');

    Route::post('/users', function (Request $request) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,instructor,student',
            'department' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => $validated['department'] ?? null,
            'student_id' => $validated['student_id'] ?? null,
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'تم إنشاء المستخدم بنجاح');
    })->name('admin.users.store');

    Route::put('/users/{user}', function (Request $request, $user) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $userModel = User::findOrFail($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userModel->id,
            'role' => 'required|in:admin,instructor,student',
            'department' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $userModel->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => $validated['department'] ?? null,
            'student_id' => $validated['student_id'] ?? null,
            'password' => $validated['password'] ? bcrypt($validated['password']) : $userModel->password,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    })->name('admin.users.update');
    
    // Courses Management
    Route::get('/courses', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }
        
        $courses = Course::with('instructor')->withCount(['students', 'sessions'])->paginate(10);
        return view('admin.courses.index', compact('courses'));
    })->name('admin.courses.index');
    
    Route::get('/courses/create', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }
        
        $instructors = User::where('role', 'instructor')->get();
        return view('admin.courses.form', compact('instructors'));
    })->name('admin.courses.create');

    Route::post('/courses', function (Request $request) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:courses,code',
            'description' => 'nullable|string',
            'department' => 'required|string|max:255',
            'instructor_id' => 'required|exists:users,id',
            'status' => 'required|in:active,inactive',
            'credit_hours' => 'nullable|integer|min:1',
            'capacity' => 'nullable|integer|min:1',
        ]);

        Course::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'department' => $validated['department'],
            'status' => $validated['status'],
            'instructor_id' => $validated['instructor_id'],
            'credit_hours' => $validated['credit_hours'] ?? 3,
            'capacity' => $validated['capacity'] ?? 50,
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'تم إنشاء المقرر بنجاح');
    })->name('admin.courses.store');

    Route::get('/courses/{course}/edit', function ($course) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $course = Course::findOrFail($course);
        $instructors = User::where('role', 'instructor')->get();
        return view('admin.courses.form', compact('course', 'instructors'));
    })->name('admin.courses.edit');

    Route::put('/courses/{course}', function (Request $request, $course) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $courseModel = Course::findOrFail($course);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:courses,code,' . $courseModel->id,
            'description' => 'nullable|string',
            'department' => 'required|string|max:255',
            'instructor_id' => 'required|exists:users,id',
            'status' => 'required|in:active,inactive',
            'credit_hours' => 'nullable|integer|min:1',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $courseModel->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'department' => $validated['department'],
            'status' => $validated['status'],
            'instructor_id' => $validated['instructor_id'],
            'credit_hours' => $validated['credit_hours'] ?? 3,
            'capacity' => $validated['capacity'] ?? 50,
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'تم تحديث المقرر بنجاح');
    })->name('admin.courses.update');

    Route::get('/courses/{course}', function ($course) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $course = Course::with(['instructor', 'enrollments'])->findOrFail($course);
        return view('admin.courses.show', compact('course'));
    })->name('admin.courses.show');

    Route::delete('/courses/{course}', function ($course) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $course = Course::findOrFail($course);
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'تم حذف المقرر بنجاح');
    })->name('admin.courses.destroy');
    
    // Reports
    Route::get('/reports', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }
        
        $reports = Report::with(['course'])->paginate(10);
        $courses = Course::all();
        $totalReports = Report::count();
        $dailyReports = Report::where('report_type', 'daily')->count();
        $weeklyReports = Report::where('report_type', 'weekly')->count();
        $monthlyReports = Report::where('report_type', 'monthly')->count();
        
        return view('admin.reports.index', compact('reports', 'courses', 'totalReports', 'dailyReports', 'weeklyReports', 'monthlyReports'));
    })->name('admin.reports');

    Route::post('/reports/generate', function (Request $request) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'report_type' => 'required|in:daily,weekly,monthly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel,csv',
        ]);
        
        Report::create([
            'course_id' => $validated['course_id'],
            'created_by' => session('user_id', 1),
            'report_type' => $validated['report_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'file_format' => $validated['format'],
        ]);
        return redirect()->back()->with('success', 'تم إنشاء التقرير بنجاح');
    })->name('admin.reports.generate');

    Route::get('/reports/{report}', function ($report) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $report = Report::with(['course', 'createdBy'])->findOrFail($report);
        return view('admin.reports.show', compact('report'));
    })->name('admin.reports.view');

    Route::get('/reports/{report}/download', function ($report) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $report = Report::with(['course', 'createdBy'])->findOrFail($report);
        $extension = $report->file_format === 'excel' ? 'xls' : ($report->file_format === 'pdf' ? 'pdf' : 'csv');
        $fileName = 'report-' . $report->id . '.' . $extension;

        if ($report->file_format === 'excel') {
            $content = '<table border="1" cellpadding="5" cellspacing="0">'
                . '<tr><th>Report ID</th><th>Type</th><th>Course</th><th>Start Date</th><th>End Date</th><th>Format</th><th>Created By</th><th>Total Students</th><th>Total Sessions</th></tr>'
                . '<tr>'
                . '<td>' . $report->id . '</td>'
                . '<td>' . ($report->report_type === 'daily' ? 'يومي' : ($report->report_type === 'weekly' ? 'أسبوعي' : 'شهري')) . '</td>'
                . '<td>' . ($report->course->name ?? 'عام') . '</td>'
                . '<td>' . ($report->start_date ? $report->start_date->format('Y-m-d') : '-') . '</td>'
                . '<td>' . ($report->end_date ? $report->end_date->format('Y-m-d') : '-') . '</td>'
                . '<td>' . strtoupper($report->file_format) . '</td>'
                . '<td>' . ($report->createdBy->name ?? 'نظام') . '</td>'
                . '<td>' . ($report->total_students ?? '0') . '</td>'
                . '<td>' . ($report->total_sessions ?? '0') . '</td>'
                . '</tr></table>';
            $content .= '<p>تفاصيل التقرير:</p><pre>' . e($report->data ?? '') . '</pre>';
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ];
        } elseif ($report->file_format === 'pdf') {
            $content = implode("\n", [
                "Report ID: {$report->id}",
                "Type: " . ($report->report_type === 'daily' ? 'يومي' : ($report->report_type === 'weekly' ? 'أسبوعي' : 'شهري')),
                "Course: " . ($report->course->name ?? 'عام'),
                "Start Date: " . ($report->start_date ? $report->start_date->format('Y-m-d') : '-') ,
                "End Date: " . ($report->end_date ? $report->end_date->format('Y-m-d') : '-') ,
                "Format: " . strtoupper($report->file_format),
                "Created By: " . ($report->createdBy->name ?? 'نظام'),
                "Total Students: " . ($report->total_students ?? '0'),
                "Total Sessions: " . ($report->total_sessions ?? '0'),
                '',
                'تفاصيل التقرير:',
                $report->data ?? '',
            ]);
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ];
        } else {
            $content = implode("\n", [
                "Report ID,Type,Course,Start Date,End Date,Format,Created By,Total Students,Total Sessions",
                "{$report->id}," . ($report->report_type === 'daily' ? 'يومي' : ($report->report_type === 'weekly' ? 'أسبوعي' : 'شهري')) . ',"' . ($report->course->name ?? 'عام') . '",'
                    . ($report->start_date ? $report->start_date->format('Y-m-d') : '-') . ','
                    . ($report->end_date ? $report->end_date->format('Y-m-d') : '-') . ','
                    . strtoupper($report->file_format) . ',"' . ($report->createdBy->name ?? 'نظام') . '",'
                    . ($report->total_students ?? '0') . ',' . ($report->total_sessions ?? '0'),
                '',
                'تفاصيل التقرير:',
                $report->data ?? '',
            ]);
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ];
        }

        return response($content, 200, $headers);
    })->name('admin.reports.download');

    // Settings & Audit Logs (Placeholders)
    Route::get('/settings', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }
        
        $settings = [
            'dark_mode' => false,
            'font_size' => 'normal',
            'language' => 'ar',
            'attendance_notifications' => true,
            'absence_alerts' => true,
            'system_notifications' => true,
            'email_notifications' => false,
            'public_profile' => true,
            'show_attendance_status' => false,
            'data_collection' => true,
            'location_services' => false,
            'timezone' => 'GMT+3',
            'gps_verification' => true,
            'nfc_enabled' => true,
            'qr_scanning' => true,
            'storage_used' => '2.5 MB',
            'app_version' => '1.0.0',
        ];

        return view('settings.index', compact('settings'));
    })->name('admin.settings');

    Route::get('/audit-logs', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $auditLogs = AuditLog::with('user')->latest()->paginate(10);
        $totalLogs = AuditLog::count();
        $todayLogs = AuditLog::whereDate('created_at', today())->count();
        $activeUsers = AuditLog::distinct('user_id')->count('user_id');
        $failedAttempts = AuditLog::where('action', 'like', '%fail%')
            ->orWhere('action', 'like', '%فشل%')
            ->count();

        return view('admin.audit-logs', compact('auditLogs', 'totalLogs', 'todayLogs', 'activeUsers', 'failedAttempts'));
    })->name('admin.audit-logs');
    
    Route::get('/schedules', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $schedules = AttendanceSession::with(['course', 'instructor'])->get();
        $courses = Course::orderBy('name')->get();
        $totalSchedules = $schedules->count();
        $activeSchedules = $schedules->where('status', 'active')->count();
        $todaySessions = AttendanceSession::whereDate('session_date', today())->count();
        $days = $schedules->map(function ($schedule) {
            return strtolower($schedule->session_date->format('l'));
        })->unique()->values();

        return view('admin.schedules.index', compact('schedules', 'courses', 'days', 'totalSchedules', 'activeSchedules', 'todaySessions'));
    })->name('admin.schedules');

    Route::get('/schedules/create', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $courses = Course::orderBy('name')->get();
        $instructors = User::where('role', 'instructor')->get();
        return view('admin.schedules.form', compact('courses', 'instructors'));
    })->name('admin.schedules.create');

    Route::post('/schedules', function (Request $request) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:users,id',
            'session_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'classroom_latitude' => 'nullable|numeric',
            'classroom_longitude' => 'nullable|numeric',
            'status' => 'required|in:active,closed,cancelled',
        ]);

        AttendanceSession::create([
            'course_id' => $validated['course_id'],
            'instructor_id' => $validated['instructor_id'],
            'session_date' => $validated['session_date'],
            'start_time' => $validated['session_date'] . ' ' . $validated['start_time'],
            'end_time' => $validated['session_date'] . ' ' . $validated['end_time'],
            'classroom_latitude' => $validated['classroom_latitude'] ?? null,
            'classroom_longitude' => $validated['classroom_longitude'] ?? null,
            'status' => $validated['status'],
            'qr_code_token' => bin2hex(random_bytes(16)),
        ]);

        return redirect()->route('admin.schedules')->with('success', 'تم إنشاء الجدول بنجاح');
    })->name('admin.schedules.store');

    Route::get('/schedules/{schedule}/edit', function ($schedule) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $schedule = AttendanceSession::findOrFail($schedule);
        $courses = Course::orderBy('name')->get();
        $instructors = User::where('role', 'instructor')->get();

        return view('admin.schedules.form', compact('schedule', 'courses', 'instructors'));
    })->name('admin.schedules.edit');

    Route::put('/schedules/{schedule}', function (Request $request, $schedule) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $scheduleModel = AttendanceSession::findOrFail($schedule);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:users,id',
            'session_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'classroom_latitude' => 'nullable|numeric',
            'classroom_longitude' => 'nullable|numeric',
            'status' => 'required|in:active,closed,cancelled',
        ]);

        $scheduleModel->update([
            'course_id' => $validated['course_id'],
            'instructor_id' => $validated['instructor_id'],
            'session_date' => $validated['session_date'],
            'start_time' => $validated['session_date'] . ' ' . $validated['start_time'],
            'end_time' => $validated['session_date'] . ' ' . $validated['end_time'],
            'classroom_latitude' => $validated['classroom_latitude'] ?? null,
            'classroom_longitude' => $validated['classroom_longitude'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.schedules')->with('success', 'تم تحديث الجدول بنجاح');
    })->name('admin.schedules.update');

    Route::delete('/schedules/{schedule}', function ($schedule) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $schedule = AttendanceSession::findOrFail($schedule);
        $schedule->delete();

        return redirect()->route('admin.schedules')->with('success', 'تم حذف الجدول بنجاح');
    })->name('admin.schedules.destroy');
});

// Instructor Routes
Route::prefix('instructor')->group(function () {
    Route::get('/attendance', function () {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }

        $instructorId = session('user_id');

        $activeSessions = AttendanceSession::where('instructor_id', $instructorId)
            ->where('status', 'active')
            ->with(['course', 'attendanceRecords.student'])
            ->get();

        $pastSessions = AttendanceSession::where('instructor_id', $instructorId)
            ->whereIn('status', ['closed', 'cancelled'])
            ->with(['course', 'attendanceRecords.student'])
            ->get();

        $courses = Course::where('instructor_id', $instructorId)->get();

        $totalSessions = AttendanceSession::where('instructor_id', $instructorId)->count();
        $totalRecords = AttendanceRecord::whereIn('course_id', $courses->pluck('id'))->count();

        return view('instructor.attendance.index', compact(
            'activeSessions',
            'pastSessions',
            'courses',
            'totalSessions',
            'totalRecords'
        ));
    })->name('instructor.attendance.index');

    Route::post('/attendance/create', function (Request $request) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'session_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'gps_required' => 'nullable|boolean',
            'nfc_active' => 'nullable|boolean',
            'classroom_name' => 'nullable|string|max:255',
            'custom_classroom_name' => 'nullable|string|max:255',
        ]);

        $selectedRoom = $validated['classroom_name'] ?? null;
        $customRoom = trim($validated['custom_classroom_name'] ?? '');
        $classroomName = $customRoom !== '' ? $customRoom : $selectedRoom;
        $classroomData = config('classrooms')[$selectedRoom] ?? null;

        if ($request->has('gps_required') && $request->boolean('gps_required') && ! $classroomData) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['gps_required' => 'لتفعيل GPS يجب اختيار قاعة معروفة من القائمة أو إيقاف التفعيل.']);
        }

        $startDateTime = $validated['session_date'] . ' ' . $validated['start_time'];
        $endTime = date('H:i', strtotime($startDateTime . ' +1 hour'));

        AttendanceSession::create([
            'course_id' => $validated['course_id'],
            'instructor_id' => session('user_id'),
            'session_date' => $validated['session_date'],
            'start_time' => $startDateTime,
            'end_time' => $validated['session_date'] . ' ' . $endTime,
            'qr_code_token' => bin2hex(random_bytes(16)),
            'gps_required' => $request->has('gps_required'),
            'nfc_active' => $request->has('nfc_active'),
            'classroom_name' => $classroomName,
            'classroom_latitude' => $classroomData['latitude'] ?? null,
            'classroom_longitude' => $classroomData['longitude'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('instructor.attendance.index')->with('success', 'تم إنشاء جلسة الحضور بنجاح');
    })->name('instructor.attendance.create');

    Route::get('/attendance/{session}', function ($session) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }

        $attendanceSession = AttendanceSession::with(['course.enrollments.student', 'attendanceRecords.student'])
            ->where('instructor_id', session('user_id'))
            ->findOrFail($session);

        $records = $attendanceSession->attendanceRecords;
        $enrolledStudentIds = $attendanceSession->course->enrollments->pluck('student_id');
        $absentStudents = \App\Models\User::whereIn('id', $enrolledStudentIds)
            ->whereNotIn('id', $records->pluck('student_id'))
            ->get();

        $totalStudents = $enrolledStudentIds->count();

        return view('instructor.attendance.view', [
            'session' => $attendanceSession,
            'records' => $records,
            'absentStudents' => $absentStudents,
            'totalStudents' => $totalStudents,
        ]);
    })->name('instructor.attendance.view');

    Route::post('/attendance/{session}/close', function ($session) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }

        $attendanceSession = AttendanceSession::where('instructor_id', session('user_id'))
            ->findOrFail($session);

        $attendanceSession->update(['status' => 'closed', 'end_time' => now()]);

        return redirect()->route('instructor.attendance.index')->with('success', 'تم إغلاق الجلسة بنجاح');
    })->name('instructor.attendance.close');

    Route::get('/attendance/{session}/edit', function ($session) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }

        $attendanceSession = AttendanceSession::where('instructor_id', session('user_id'))
            ->findOrFail($session);
        $courses = Course::where('instructor_id', session('user_id'))->get();

        return view('instructor.attendance.edit', compact('attendanceSession', 'courses'));
    })->name('instructor.attendance.edit');

    Route::put('/attendance/{session}', function (Request $request, $session) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }

        $attendanceSession = AttendanceSession::where('instructor_id', session('user_id'))
            ->findOrFail($session);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'session_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'gps_required' => 'nullable|boolean',
            'nfc_active' => 'nullable|boolean',
            'status' => 'required|in:active,closed,cancelled',
        ]);

        $attendanceSession->update([
            'course_id' => $validated['course_id'],
            'session_date' => $validated['session_date'],
            'start_time' => $validated['session_date'] . ' ' . $validated['start_time'],
            'end_time' => $validated['end_time'] ? $validated['session_date'] . ' ' . $validated['end_time'] : $attendanceSession->end_time,
            'gps_required' => $request->has('gps_required'),
            'nfc_active' => $request->has('nfc_active'),
            'status' => $validated['status'],
        ]);

        return redirect()->route('instructor.attendance.view', $attendanceSession)->with('success', 'تم تحديث الجلسة بنجاح');
    })->name('instructor.attendance.update');

    Route::delete('/attendance/record/{record}', function ($record) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }

        $attendanceRecord = AttendanceRecord::findOrFail($record);
        $attendanceSession = AttendanceSession::findOrFail($attendanceRecord->session_id);

        if ($attendanceSession->instructor_id !== session('user_id')) {
            return redirect('/login');
        }

        $attendanceRecord->delete();

        return back()->with('success', 'تم حذف سجل الحضور');
    })->name('instructor.attendance.delete');

    Route::post('/attendance/{session}/add-manual', function (Request $request, $session) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }

        $attendanceSession = AttendanceSession::where('instructor_id', session('user_id'))
            ->findOrFail($session);

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        AttendanceRecord::create([
            'session_id' => $attendanceSession->id,
            'student_id' => $validated['student_id'],
            'course_id' => $attendanceSession->course_id,
            'marked_at' => now(),
            'verification_method' => 'manual',
        ]);

        return back()->with('success', 'تم تسجيل الحضور يدوياً');
    })->name('instructor.attendance.add-manual');

    Route::get('/courses', function () {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }
        $courses = Course::where('instructor_id', session('user_id'))->with('enrollments')->get();
        return view('instructor.courses.index', compact('courses'));
    })->name('instructor.courses');
    
    Route::get('/courses/{course}/attendance', function ($course) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }
        $course = Course::with(['enrollments.student', 'attendanceSessions'])->findOrFail($course);
        return view('instructor.courses.attendance', compact('course'));
    })->name('instructor.courses.attendance');
    
    Route::get('/reports', function () {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }
        $courses = Course::where('instructor_id', session('user_id'))->get();
        return view('instructor.reports.index', compact('courses'));
    })->name('instructor.reports');
    
    Route::get('/profile', function () {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }
        return view('instructor.profile.index');
    })->name('instructor.profile');
});

Route::get('/attendance/scan', function (Request $request) {
    if (!session('user_id')) {
        return redirect('/login');
    }

    $sessionId = $request->query('session');
    $token = $request->query('token');
    $courseId = $request->query('course_id');
    $scanDate = $request->query('date');
    $scanTime = $request->query('time');

    $attendanceSession = null;

    if ($sessionId && $token) {
        $attendanceSession = AttendanceSession::where('id', $sessionId)
            ->where('qr_code_token', $token)
            ->with('course')
            ->first();

        if (! $attendanceSession) {
            abort(404, 'جلسة الحضور غير موجودة أو الرمز غير صالح');
        }
    } elseif ($courseId && $scanDate && $scanTime) {
        $course = Course::find($courseId);

        if (! $course) {
            abort(404, 'المقرر غير موجود');
        }

        $attendanceSession = (object) [
            'course' => $course,
            'session_date' => $scanDate,
            'start_time' => $scanTime,
            'end_time' => null,
            'qr_code_token' => null,
            'status' => 'pending',
        ];
    } else {
        abort(404, 'الرمز غير صالح');
    }

    return view('attendance.scan', compact('attendanceSession'));
})->name('attendance.scan');

// Student Routes
Route::prefix('student')->group(function () {
    Route::get('/attendance', function () {
        if (!session('user_id') || session('user_role') !== 'student') {
            return redirect('/login');
        }

        $studentId = session('user_id');
        $enrollments = \App\Models\Enrollment::where('student_id', $studentId)
            ->with(['course.instructor', 'course.attendanceSessions', 'course.attendanceRecords'])
            ->get();

        $courseIds = $enrollments->pluck('course_id')->unique();

        $totalSessions = $courseIds->isEmpty()
            ? 0
            : \App\Models\AttendanceSession::whereIn('course_id', $courseIds)->count();

        $totalAttended = $courseIds->isEmpty()
            ? 0
            : \App\Models\AttendanceRecord::where('student_id', $studentId)
                ->whereIn('course_id', $courseIds)
                ->count();

        $totalAbsent = max(0, $totalSessions - $totalAttended);
        $overallPercentage = $totalSessions > 0
            ? round($totalAttended / $totalSessions * 100, 2)
            : 0;

        $hasWarning = false;
        $isCritical = false;

        return view('student.attendance.index', compact(
            'enrollments',
            'totalSessions',
            'totalAttended',
            'totalAbsent',
            'overallPercentage',
            'hasWarning',
            'isCritical'
        ));
    })->name('student.attendance');
    
    Route::get('/courses', function () {
        if (!session('user_id') || session('user_role') !== 'student') {
            return redirect('/login');
        }
        $enrollments = \App\Models\Enrollment::where('student_id', session('user_id'))
            ->with('course')->get();
        return view('student.courses.index', compact('enrollments'));
    })->name('student.courses');
    
    Route::get('/courses/{course}', function ($course) {
        if (!session('user_id') || session('user_role') !== 'student') {
            return redirect('/login');
        }
        $enrollment = \App\Models\Enrollment::where('student_id', session('user_id'))
            ->where('course_id', $course)
            ->with(['course', 'attendanceRecords'])->firstOrFail();
        return view('student.courses.show', compact('enrollment'));
    })->name('student.courses.show');
    
    Route::get('/reports', function () {
        if (!session('user_id') || session('user_role') !== 'student') {
            return redirect('/login');
        }
        $enrollments = \App\Models\Enrollment::where('student_id', session('user_id'))
            ->with(['course', 'attendanceRecords'])->get();
        return view('student.reports.index', compact('enrollments'));
    })->name('student.reports');
    
    Route::get('/profile', function () {
        if (!session('user_id') || session('user_role') !== 'student') {
            return redirect('/login');
        }
        return view('student.profile.index');
    })->name('student.profile');
    
    Route::get('/scan-qr', function () {
        if (!session('user_id')) {
            return redirect('/login')->with('error', 'يجب تسجيل الدخول أولاً');
        }
        if (session('user_role') !== 'student') {
            return redirect('/dashboard')->with('error', 'هذه الصفحة مخصصة للطلاب فقط');
        }
        return view('student.scan-qr');
    })->name('student.scan-qr');
    
    Route::post('/scan-qr', function (Request $request) {
        if (!session('user_id') || session('user_role') !== 'student') {
            return response()->json(['error' => 'غير مصرح'], 403);
        }
        
        $qrData = $request->input('qr_data');
        $studentId = session('user_id');
        
        // Parse QR data - could be URL or session_id:token
        if (str_contains($qrData, 'http')) {
            // Extract session and token from URL
            parse_str(parse_url($qrData, PHP_URL_QUERY), $params);
            $sessionId = $params['session'] ?? null;
            $token = $params['token'] ?? null;
        } else {
            // Parse QR data - assuming format: session_id:token
            $parts = explode(':', $qrData);
            if (count($parts) !== 2) {
                return response()->json(['error' => 'رمز QR غير صالح'], 400);
            }
            $sessionId = $parts[0];
            $token = $parts[1];
        }
        
        if (!$sessionId || !$token) {
            return response()->json(['error' => 'رمز QR غير صالح'], 400);
        }
        
        $attendanceSession = AttendanceSession::where('id', $sessionId)
            ->where('qr_code_token', $token)
            ->where('status', 'active')
            ->first();
        
        if (!$attendanceSession) {
            return response()->json(['error' => 'جلسة الحضور غير صالحة أو منتهية'], 400);
        }
        
        // Check if student is enrolled in the course
        $enrollment = Enrollment::where('student_id', $studentId)
            ->where('course_id', $attendanceSession->course_id)
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'غير مسجل في هذا المقرر'], 400);
        }
        
        // Check if already marked attendance
        $existingRecord = AttendanceRecord::where('session_id', $sessionId)
            ->where('student_id', $studentId)
            ->first();
        
        if ($existingRecord) {
            return response()->json(['error' => 'تم تسجيل الحضور مسبقاً'], 400);
        }
        
        // Mark attendance
        AttendanceRecord::create([
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'course_id' => $attendanceSession->course_id,
            'marked_at' => now(),
            'verification_method' => 'qr_scan',
        ]);
        
        return response()->json(['success' => 'تم تسجيل الحضور بنجاح']);
    })->name('student.scan-qr.post');
});

// Notifications
Route::get('/notifications', function () {
    if (!session('user_id')) {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return redirect('/login');
    }

    $notifications = Notification::where('student_id', session('user_id'))
        ->with('course')
        ->orderByDesc('created_at')
        ->limit(10)
        ->get();

    if (request()->expectsJson()) {
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    return view('notifications.index', compact('notifications'));
})->name('notifications');

// Profile & Settings
Route::get('/profile', function () {
    if (!session('user_id')) {
        return redirect('/login');
    }

    $user = User::find(session('user_id'));

    if (! $user) {
        return redirect('/login');
    }

    $attendanceCount = $user->attendanceRecords()->count();
    $courseIds = $user->enrollments()->pluck('course_id');
    $totalSessions = $courseIds->isEmpty()
        ? 0
        : AttendanceSession::whereIn('course_id', $courseIds)->count();
    $absenceCount = max(0, $totalSessions - $attendanceCount);
    $attendancePercentage = $totalSessions > 0 ? round($attendanceCount / $totalSessions * 100, 2) : 0;
    $lastUpdated = $user->updated_at ? $user->updated_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i');

    return view('profile.index', compact('user', 'attendanceCount', 'absenceCount', 'attendancePercentage', 'lastUpdated'));
})->name('profile');

Route::get('/settings', function () {
    if (!session('user_id')) {
        return redirect('/login');
    }

    $defaultSettings = [
        'dark_mode' => false,
        'font_size' => 'normal',
        'language' => 'ar',
        'attendance_notifications' => true,
        'absence_alerts' => true,
        'system_notifications' => true,
        'email_notifications' => false,
        'public_profile' => true,
        'show_attendance_status' => false,
        'data_collection' => true,
        'location_services' => false,
        'timezone' => 'GMT+3',
        'gps_verification' => true,
        'nfc_enabled' => true,
        'qr_scanning' => true,
        'storage_used' => '2.5 MB',
        'app_version' => '1.0.0',
    ];

    $settings = session('settings', $defaultSettings);

    return view('settings.index', compact('settings'));
})->name('settings');

Route::post('/settings', function (Request $request) {
    if (!session('user_id')) {
        return redirect('/login');
    }

    $settings = $request->only([
        'dark_mode',
        'font_size',
        'language',
        'attendance_notifications',
        'absence_alerts',
        'system_notifications',
        'email_notifications',
        'public_profile',
        'show_attendance_status',
        'data_collection',
        'location_services',
        'timezone',
        'gps_verification',
        'nfc_enabled',
        'qr_scanning',
    ]);

    $settings = array_merge([
        'dark_mode' => false,
        'font_size' => 'normal',
        'language' => 'ar',
        'attendance_notifications' => true,
        'absence_alerts' => true,
        'system_notifications' => true,
        'email_notifications' => false,
        'public_profile' => true,
        'show_attendance_status' => false,
        'data_collection' => true,
        'location_services' => false,
        'timezone' => 'GMT+3',
        'gps_verification' => true,
        'nfc_enabled' => true,
        'qr_scanning' => true,
        'storage_used' => '2.5 MB',
        'app_version' => '1.0.0',
    ], $settings);

    $settings['dark_mode'] = $request->has('dark_mode');
    $settings['attendance_notifications'] = $request->has('attendance_notifications');
    $settings['absence_alerts'] = $request->has('absence_alerts');
    $settings['system_notifications'] = $request->has('system_notifications');
    $settings['email_notifications'] = $request->has('email_notifications');
    $settings['public_profile'] = $request->has('public_profile');
    $settings['show_attendance_status'] = $request->has('show_attendance_status');
    $settings['data_collection'] = $request->has('data_collection');
    $settings['location_services'] = $request->has('location_services');
    $settings['gps_verification'] = $request->has('gps_verification');
    $settings['nfc_enabled'] = $request->has('nfc_enabled');
    $settings['qr_scanning'] = $request->has('qr_scanning');

    session(['settings' => $settings]);

    return back()->with('success', 'تم حفظ الإعدادات بنجاح');
})->name('settings.save');

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
use App\Models\Department;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    Route::get('/forgot-password', function () {
        return response()->view('auth.forgot_password')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('forgot-password');

    Route::post('/forgot-password', function (Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $token = bin2hex(random_bytes(32));

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $validated['email']],
                [
                    'token' => $token,
                    'created_at' => now(),
                ]
            );

            $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($validated['email']));

            try {
                \Illuminate\Support\Facades\Mail::to($validated['email'])->send(new \App\Mail\PasswordResetMail($user, $resetUrl));
            } catch (\Exception $e) {
                return back()->withErrors(['email' => 'فشل في إرسال رابط إعادة التعيين. يرجى المحاولة مرة أخرى.']);
            }
        }

        return back()->with('success', 'إذا كان البريد موجوداً في النظام فقد تم إرسال رابط إعادة تعيين كلمة المرور إليه.');
    })->name('forgot-password.post');

    Route::get('/reset-password/{token}', function ($token, Request $request) {
        $email = $request->query('email');

        if (! $email) {
            return redirect('/forgot-password')->withErrors(['email' => 'الرابط غير صالح.']);
        }

        $reset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (! $reset) {
            return redirect('/forgot-password')->withErrors(['email' => 'الرابط غير صالح أو لم يُطلب إعادة التعيين.']);
        }

        if (\Illuminate\Support\Carbon::parse($reset->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect('/forgot-password')->withErrors(['email' => 'انتهت صلاحية رابط إعادة تعيين كلمة المرور.']);
        }

        return response()->view('auth.reset_password', compact('token', 'email'))->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('reset-password');

    Route::post('/reset-password', function (Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->where('token', $validated['token'])
            ->first();

        if (! $reset) {
            return back()->withErrors(['email' => 'الرابط غير صالح أو لم يُطلب إعادة التعيين.']);
        }

        if (\Illuminate\Support\Carbon::parse($reset->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            return back()->withErrors(['email' => 'انتهت صلاحية رابط إعادة تعيين كلمة المرور.']);
        }

        $user = User::where('email', $validated['email'])->first();
        if (! $user) {
            return back()->withErrors(['email' => 'لم يتم العثور على حساب لهذا البريد الإلكتروني.']);
        }

        $user->update([
            'password' => bcrypt($validated['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return redirect('/login')->with('success', 'تم إعادة تعيين كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول.');
    })->name('reset-password.post');

    Route::get('/register', function () {
        $departments = Department::orderBy('name')->get();
        $specializations = Specialization::with('department')->orderBy('name')->get();

        return response()->view('auth.register', compact('departments', 'specializations'))->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('register');

    Route::post('/register', function (Request $request) {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    // التحقق من أن البريد ينتمي إلى إحدى قنوات الجامعة المسموح بها
                    if (!preg_match('/^[^@\s]+@(stu\.nbu\.edu\.sa|nbu\.edu\.sa)$/i', $value)) {
                        $fail('يجب أن يكون البريد الإلكتروني من جامعة الحدود الشمالية بصيغة الطالب (@stu.nbu.edu.sa) أو المحاضر (@nbu.edu.sa)');
                    }
                },
            ],
        ]);

        // إنشاء رمز تحقق عشوائي
        $verificationCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // حفظ البيانات في الجلسة مؤقتاً
        session([
            'registration_data' => [
                'email' => $validated['email'],
                'verification_code' => $verificationCode,
                'expires_at' => now()->addMinutes(10), // صالح لمدة 10 دقائق
            ]
        ]);

        // إرسال البريد الإلكتروني
        try {
            \Illuminate\Support\Facades\Mail::to($validated['email'])->send(new \App\Mail\EmailVerification((object)['name' => 'طالب جديد'], $verificationCode));
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'فشل في إرسال البريد الإلكتروني. يرجى المحاولة مرة أخرى.']);
        }

        return redirect('/register/verify')->with('success', 'تم إرسال رمز التحقق إلى بريدك الإلكتروني');
    })->name('register.post');

    Route::get('/register/verify', function () {
        if (!session('registration_data')) {
            return redirect('/register')->withErrors(['general' => 'يرجى إدخال البريد الإلكتروني أولاً']);
        }

        return response()->view('auth.verify_email')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('register.verify');

    Route::post('/register/verify', function (Request $request) {
        if (!session('registration_data')) {
            return redirect('/register')->withErrors(['general' => 'انتهت صلاحية الجلسة. يرجى البدء من جديد.']);
        }

        $registrationData = session('registration_data');

        // التحقق من انتهاء صلاحية الرمز
        if (now()->isAfter($registrationData['expires_at'])) {
            session()->forget('registration_data');
            return redirect('/register')->withErrors(['code' => 'انتهت صلاحية رمز التحقق. يرجى طلب رمز جديد.']);
        }

        $validated = $request->validate([
            'verification_code' => 'required|string|size:6',
        ]);

        if (strtoupper($validated['verification_code']) !== $registrationData['verification_code']) {
            return back()->withErrors(['verification_code' => 'رمز التحقق غير صحيح']);
        }

        // الرمز صحيح، الانتقال إلى إكمال التسجيل
        session(['email_verified' => true]);

        return redirect('/register/complete');
    })->name('register.verify.post');

    Route::get('/register/complete', function () {
        if (!session('email_verified') || !session('registration_data')) {
            return redirect('/register')->withErrors(['general' => 'يرجى إكمال عملية التحقق أولاً']);
        }

        $departments = Department::orderBy('name')->get();
        $specializations = Specialization::with('department')->orderBy('name')->get();

        return response()->view('auth.register_complete', compact('departments', 'specializations'))->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('register.complete');

    Route::post('/register/complete', function (Request $request) {
        if (!session('email_verified') || !session('registration_data')) {
            return redirect('/register')->withErrors(['general' => 'يرجى إكمال عملية التحقق أولاً']);
        }

        $registrationData = session('registration_data');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'role' => 'required|in:admin,instructor,student',
            'student_id' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // إنشاء الحساب
        User::create([
            'name' => $validated['name'],
            'email' => $registrationData['email'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'role' => $validated['role'],
            'student_id' => $validated['student_id'] ?? null,
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
        ]);

        // تنظيف الجلسة
        session()->forget(['registration_data', 'email_verified']);

        return redirect('/login')->with('success', 'تم إنشاء الحساب بنجاح. يمكنك الآن تسجيل الدخول.')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    })->name('register.complete.post');

    Route::post('/register/resend', function (Request $request) {
        if (!session('registration_data')) {
            return redirect('/register')->withErrors(['general' => 'يرجى إدخال البريد الإلكتروني أولاً']);
        }

        $registrationData = session('registration_data');

        // إنشاء رمز تحقق جديد
        $verificationCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // تحديث البيانات في الجلسة
        session([
            'registration_data' => [
                'email' => $registrationData['email'],
                'verification_code' => $verificationCode,
                'expires_at' => now()->addMinutes(10),
            ]
        ]);

        // إرسال البريد الإلكتروني
        try {
            \Illuminate\Support\Facades\Mail::to($registrationData['email'])->send(new \App\Mail\EmailVerification((object)['name' => 'طالب جديد'], $verificationCode));
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'فشل في إرسال البريد الإلكتروني. يرجى المحاولة مرة أخرى.']);
        }

        return back()->with('success', 'تم إرسال رمز تحقق جديد إلى بريدك الإلكتروني');
    })->name('register.resend');

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
        
        $departments = Department::orderBy('name')->get();
        $specializations = Specialization::with('department')->orderBy('name')->get();
        return view('admin.users.form', compact('departments', 'specializations'));
    })->name('admin.users.create');
    
    Route::get('/users/{user}/edit', function ($user) {
        if (!session('user_id')) {
            return redirect('/login');
        }
        
        $user = User::findOrFail($user);
        $departments = Department::orderBy('name')->get();
        $specializations = Specialization::with('department')->orderBy('name')->get();
        return view('admin.users.form', compact('user', 'departments', 'specializations'));
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
            'specialization' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => $validated['department'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
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
            'specialization' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $userModel->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => $validated['department'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
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
        $departments = Department::orderBy('name')->get();
        $specializations = Specialization::with('department')->orderBy('name')->get();
        return view('admin.courses.form', compact('instructors', 'departments', 'specializations'));
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
            'specialization' => 'nullable|string|max:255',
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
            'specialization' => $validated['specialization'] ?? null,
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
        $departments = Department::orderBy('name')->get();
        $specializations = Specialization::with('department')->orderBy('name')->get();
        return view('admin.courses.form', compact('course', 'instructors', 'departments', 'specializations'));
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
            'specialization' => 'nullable|string|max:255',
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
            'specialization' => $validated['specialization'] ?? null,
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

    // Departments & Specializations
    Route::get('/departments', function () {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $departments = Department::with('specializations')->orderBy('name')->get();
        $allDepartments = Department::orderBy('name')->get();

        return view('admin.departments.index', compact('departments', 'allDepartments'));
    })->name('admin.departments.index');

    Route::post('/departments', function (Request $request) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create(['name' => $validated['name']]);

        return redirect()->route('admin.departments.index')->with('success', 'تم إنشاء القسم بنجاح');
    })->name('admin.departments.store');

    Route::post('/specializations', function (Request $request) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
        ]);

        $exists = Specialization::where('department_id', $validated['department_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return redirect()->route('admin.departments.index')->withErrors(['name' => 'هذا التخصص موجود بالفعل ضمن هذا القسم']);
        }

        Specialization::create([
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'تم إضافة التخصص بنجاح');
    })->name('admin.specializations.store');

    Route::put('/departments/{department}', function (Request $request, $department) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $dept = Department::findOrFail($department);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $dept->id,
        ]);

        $dept->update(['name' => $validated['name']]);

        return redirect()->route('admin.departments.index')->with('success', 'تم تحديث القسم بنجاح');
    })->name('admin.departments.update');

    Route::delete('/departments/{department}', function ($department) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $dept = Department::findOrFail($department);
        $dept->delete();

        return redirect()->route('admin.departments.index')->with('success', 'تم حذف القسم بنجاح');
    })->name('admin.departments.destroy');

    Route::delete('/specializations/{specialization}', function ($specialization) {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $spec = Specialization::findOrFail($specialization);
        $spec->delete();

        return redirect()->route('admin.departments.index')->with('success', 'تم حذف التخصص بنجاح');
    })->name('admin.specializations.destroy');

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
            ->find($session);

        if (! $attendanceSession) {
            return redirect()->route('instructor.attendance.index')
                ->withErrors(['general' => 'هذه الجلسة غير موجودة أو ليست من ضمن جلساتك.']);
        }

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
        $courses = Course::where('instructor_id', session('user_id'))->with(['enrollments', 'sessions'])->get();
        return view('instructor.courses.index', compact('courses'));
    })->name('instructor.courses.index');
    
    Route::get('/courses/{course}/attendance', function ($course) {
        if (!session('user_id') || session('user_role') !== 'instructor') {
            return redirect('/login');
        }
        $course = Course::with(['enrollments.student', 'sessions'])->findOrFail($course);
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
            ->with(['course.instructor', 'course.sessions', 'course.attendanceRecords'])
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

        $query = \App\Models\Course::with(['instructor', 'enrollments']);

        // فلترة حسب القسم
        if (request('department')) {
            $query->where('department', request('department'));
        }

        // فلترة حسب التخصص
        if (request('specialization')) {
            $query->where('specialization', request('specialization'));
        }

        // فلترة حسب المستوى
        if (request('level')) {
            $query->where('level', request('level'));
        }

        // فلترة حسب الشعبة
        if (request('section')) {
            $query->where('section', 'like', '%' . request('section') . '%');
        }

        // فلترة حسب اسم المعلم
        if (request('instructor')) {
            $query->whereHas('instructor', function($q) {
                $q->where('name', 'like', '%' . request('instructor') . '%');
            });
        }

        // فلترة حسب المستوى والتخصص والقسم للطالب الحالي
        $student = \App\Models\User::find(session('user_id'));
        if ($student->department) {
            $query->where('department', $student->department);
        }
        if ($student->specialization) {
            $query->where('specialization', $student->specialization);
        }
        if ($student->level) {
            $query->where('level', $student->level);
        }
        if ($student->section) {
            $query->where('section', $student->section);
        }

        $courses = $query->paginate(10);

        return view('student.courses.index', compact('courses'));
    })->name('student.courses.index');

    // عرض المقررات المتاحة للطالب (بديل)
    Route::get('/student/courses', function () {
        return redirect()->route('student.courses.index');
    })->name('student.courses.index.redirect');

    // تسجيل في مقرر
    Route::post('/courses/{course}/enroll', function ($course) {
        if (!session('user_id') || session('user_role') !== 'student') {
            return redirect('/login');
        }

        $course = \App\Models\Course::findOrFail($course);
        $studentId = session('user_id');

        // التحقق من عدم التسجيل مسبقاً
        $existing = \App\Models\Enrollment::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->exists();

        if ($existing) {
            return back()->with('error', 'أنت مسجل بالفعل في هذا المقرر');
        }

        // التحقق من السعة
        if ($course->enrollments->count() >= $course->capacity) {
            return back()->with('error', 'المقرر مكتمل، لا توجد أماكن متاحة');
        }

        // التحقق من تطابق القسم والتخصص والمستوى والشعبة
        $student = \App\Models\User::find($studentId);
        if ($student->department && $course->department && $student->department !== $course->department) {
            return back()->with('error', 'لا يمكنك التسجيل في مقرر من قسم مختلف');
        }
        if ($student->specialization && $course->specialization && $student->specialization !== $course->specialization) {
            return back()->with('error', 'لا يمكنك التسجيل في مقرر من تخصص مختلف');
        }
        if ($student->level && $course->level && $student->level !== $course->level) {
            return back()->with('error', 'لا يمكنك التسجيل في مقرر من مستوى مختلف');
        }
        if ($student->section && $course->section && $student->section !== $course->section) {
            return back()->with('error', 'لا يمكنك التسجيل في مقرر من شعبة مختلفة');
        }

        \App\Models\Enrollment::create([
            'student_id' => $studentId,
            'course_id' => $course->id,
        ]);

        // تسجيل العملية
        \App\Models\AuditLog::create([
            'user_id' => $studentId,
            'action' => 'enroll_course',
            'description' => "تسجيل في المقرر: {$course->name}",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'تم التسجيل في المقرر بنجاح');
    })->name('student.courses.enroll');

    // إلغاء التسجيل من مقرر
    Route::delete('/courses/{course}/unenroll', function ($course) {
        if (!session('user_id') || session('user_role') !== 'student') {
            return redirect('/login');
        }

        $course = \App\Models\Course::findOrFail($course);
        $studentId = session('user_id');

        $enrollment = \App\Models\Enrollment::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'أنت غير مسجل في هذا المقرر');
        }

        $enrollment->delete();

        // تسجيل العملية
        \App\Models\AuditLog::create([
            'user_id' => $studentId,
            'action' => 'unenroll_course',
            'description' => "إلغاء التسجيل من المقرر: {$course->name}",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'تم إلغاء التسجيل من المقرر بنجاح');
    })->name('student.courses.unenroll');
    
    Route::get('/courses/{course}', function ($course) {
        if (!session('user_id') || session('user_role') !== 'student') {
            return redirect('/login');
        }
        $enrollment = \App\Models\Enrollment::where('student_id', session('user_id'))
            ->where('course_id', $course)
            ->with(['course.sessions', 'attendanceRecords'])->firstOrFail();
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

// تسجيل الحضور عبر API
Route::post('/attendance/record', function (Request $request) {
    if (!session('user_id') || session('user_role') !== 'student') {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بالوصول',
        ], 401);
    }

    $validated = $request->validate([
        'session_id' => 'required|exists:attendance_sessions,id',
        'verification_method' => 'required|in:qrcode,nfc,manual,gps',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
    ]);

    $studentId = session('user_id');
    $session = AttendanceSession::findOrFail($validated['session_id']);

    // التحقق من أن الجلسة نشطة
    if ($session->status !== 'active') {
        return response()->json([
            'success' => false,
            'message' => 'الجلسة غير نشطة',
        ], 400);
    }

    // التحقق من أن الطالب مسجل في المقرر نفسه
    $enrolled = Enrollment::where('student_id', $studentId)
        ->where('course_id', $session->course_id)
        ->exists();

    if (!$enrolled) {
        return response()->json([
            'success' => false,
            'message' => 'أنت غير مسجل في هذا المقرر',
        ], 403);
    }

    // التحقق من تطابق القسم والتخصص إن كان موجوداً
    $student = User::find($studentId);
    if ($student->department && $session->course->department && $student->department !== $session->course->department) {
        return response()->json([
            'success' => false,
            'message' => 'أنت غير من نفس القسم لمقرر هذه الجلسة',
        ], 403);
    }

    if ($student->specialization && $session->course->specialization && $student->specialization !== $session->course->specialization) {
        return response()->json([
            'success' => false,
            'message' => 'تخصصك لا يطابق تخصص المقرر',
        ], 403);
    }

    // التحقق من عدم تسجيل الحضور مسبقاً
    $existing = AttendanceRecord::where('session_id', $session->id)
        ->where('student_id', $studentId)
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
        if (!$validated['latitude'] || !$validated['longitude']) {
            if ($session->gps_required) {
                return response()->json([
                    'success' => false,
                    'message' => 'يتطلب هذا الفصل بيانات GPS لتسجيل الحضور',
                ], 400);
            }
        } else {
            // حساب المسافة (نفس المنطق في AttendanceController)
            $earthRadius = 6371000; // متر
            $latFrom = deg2rad($validated['latitude']);
            $lonFrom = deg2rad($validated['longitude']);
            $latTo = deg2rad($session->classroom_latitude);
            $lonTo = deg2rad($session->classroom_longitude);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $distance = $angle * $earthRadius;

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
        'student_id' => $studentId,
        'course_id' => $session->course_id,
        'marked_at' => now(),
        'verification_method' => $validated['verification_method'],
        'latitude' => $validated['latitude'] ?? null,
        'longitude' => $validated['longitude'] ?? null,
        'distance_from_classroom' => $distance,
    ]);

    // تسجيل العملية
    AuditLog::create([
        'user_id' => $studentId,
        'action' => 'record_attendance',
        'description' => "تسجيل حضور في جلسة {$session->course->name}",
        'ip_address' => $request->ip(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تسجيل الحضور بنجاح',
    ]);
})->name('attendance.record');

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

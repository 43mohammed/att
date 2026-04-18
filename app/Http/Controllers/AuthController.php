<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * معالجة تسجيل الدخول
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // تسجيل عملية تسجيل الدخول
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'login',
                'description' => 'تسجيل دخول المستخدم',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended('/dashboard')->with('success', 'تم تسجيل الدخول بنجاح');
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة',
        ])->onlyInput('email');
    }

    /**
     * عرض صفحة التسجيل
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * معالجة التسجيل الجديد
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string',
            'role' => 'required|in:student,instructor',
            'student_id' => 'required_if:role,student|string|unique:users,student_id',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'],
            'role' => $validated['role'],
            'student_id' => $validated['student_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        // تسجيل عملية التسجيل الجديد
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'register',
            'description' => 'تسجيل مستخدم جديد',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect('/dashboard')->with('success', 'تم إنشاء الحساب بنجاح');
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        // تسجيل عملية تسجيل الخروج
        if (Auth::check()) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'logout',
                'description' => 'تسجيل خروج المستخدم',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'تم تسجيل الخروج بنجاح');
    }

    /**
     * عرض صفحة الملف الشخصي
     */
    public function profile()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    /**
     * تحديث الملف الشخصي
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string',
        ]);

        $user->update($validated);

        // تسجيل عملية التحديث
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'update_profile',
            'description' => 'تحديث الملف الشخصي',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }

    /**
     * تغيير كلمة المرور
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // تسجيل عملية تغيير كلمة المرور
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'change_password',
            'description' => 'تغيير كلمة المرور',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }
}

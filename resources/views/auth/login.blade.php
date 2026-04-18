<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام الحضور</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #dbeafe;
            --secondary: #764ba2;
            --dark: #1f2937;
            --light: #f3f4f6;
            --border: #e5e7eb;
            --text-light: #6b7280;
            --danger: #dc2626;
            --radius-lg: 1rem;
            --radius-md: 0.5rem;
            --spacing-lg: 2rem;
            --spacing-md: 1.5rem;
            --spacing-sm: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-sm);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-box {
            background: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-lg);
        }

        .login-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .login-box h1 {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: var(--spacing-md);
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
            background-color: #fafbfc;
        }

        .form-group input::placeholder {
            color: var(--text-light);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-md);
            font-size: 0.9rem;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            font-weight: 500;
            color: var(--text-light);
            cursor: pointer;
        }

        .remember-forgot a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
            font-weight: 600;
        }

        .remember-forgot a:hover {
            color: var(--secondary);
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: var(--spacing-sm);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.875rem;
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
            border-right: 4px solid var(--danger);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: shake 0.3s ease-out;
        }

        .error-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .divider {
            display: flex;
            align-items: center;
            margin: var(--spacing-lg) 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .divider::before {
            margin-right: 0.75rem;
        }

        .divider::after {
            margin-left: 0.75rem;
        }

        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .social-btn {
            padding: 0.75rem;
            border: 2px solid var(--border);
            background: white;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .social-btn:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .register-link {
            text-align: center;
            margin-top: var(--spacing-lg);
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: var(--secondary);
        }

        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--border);
        }

        .feature {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        @media (max-width: 480px) {
            .login-box {
                padding: var(--spacing-md);
            }

            .login-box h1 {
                font-size: 1.5rem;
            }

            .login-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <!-- Header -->
            <div class="login-header">
                <div class="login-icon">📚</div>
                <h1>نظام الحضور</h1>
                <p class="login-subtitle">إدارة الحضور والغياب بسهولة</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="error-message">
                    <span class="error-icon">⚠️</span>
                    <div>{{ $errors->first('email') ?? $errors->first() }}</div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="/login">
                @csrf
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        value="{{ old('email') }}"
                        placeholder="أدخل بريدك الإلكتروني"
                    >
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="أدخل كلمة المرور"
                    >
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        تذكرني
                    </label>
                    <a href="/forgot-password">هل نسيت كلمة المرور؟</a>
                </div>

                <button type="submit" class="btn-login">تسجيل الدخول</button>
            </form>

            <!-- Register Link -->
            <div class="register-link">
                ليس لديك حساب؟ <a href="/register">إنشاء حساب جديد</a>
            </div>

            <!-- Features -->
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <div>تسجيل فوري</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">📱</div>
                    <div>تطبيق جوال</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">🔒</div>
                    <div>آمن وموثوق</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">📊</div>
                    <div>تقارير دقيقة</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

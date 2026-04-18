<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - نظام الحضور</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        .register-box {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        .register-box h1 {
            text-align: center;
            color: #2563eb;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .btn-register {
            width: 100%;
            padding: 0.75rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-register:hover {
            background: #1e40af;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #fca5a5;
            font-size: 0.875rem;
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #6b7280;
        }
        .login-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h1>📚 إنشاء حساب جديد</h1>
            
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="error-message">{{ $error }}</div>
                @endforeach
            @endif

            <form method="POST" action="/register">
                @csrf
                
                <div class="form-group">
                    <label for="name">الاسم الكامل</label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label for="department">القسم</label>
                    <select id="department" name="department" required>
                        <option value="">اختر القسم</option>
                        <option value="هندسة">هندسة</option>
                        <option value="علوم">علوم</option>
                        <option value="آداب">آداب</option>
                        <option value="تجارة">تجارة</option>
                        <option value="طب">طب</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="role">الدور</label>
                    <select id="role" name="role" required>
                        <option value="student">طالب</option>
                        <option value="instructor">محاضر</option>
                    </select>
                </div>

                <div class="form-group" id="student-id-group" style="display: none;">
                    <label for="student_id">رقم الطالب</label>
                    <input type="text" id="student_id" name="student_id" value="{{ old('student_id') }}">
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">تأكيد كلمة المرور</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn-register">إنشاء الحساب</button>
            </form>

            <div class="login-link">
                هل لديك حساب بالفعل؟ <a href="/login">تسجيل الدخول</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('role').addEventListener('change', function() {
            const studentIdGroup = document.getElementById('student-id-group');
            if (this.value === 'student') {
                studentIdGroup.style.display = 'block';
            } else {
                studentIdGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>

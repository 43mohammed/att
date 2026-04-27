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
                    <label for="email">البريد الإلكتروني الجامعي</label>
                    <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="example@stu.nbu.edu.sa أو example@nbu.edu.sa">
                    <small style="display:block; margin-top:0.5rem; color:#6b7280; font-size:0.9rem;">يمكنك استخدام بريد الطالب بصيغة <strong>@stu.nbu.edu.sa</strong> أو بريد المحاضر بصيغة <strong>@nbu.edu.sa</strong>.</small>
                </div>

                <button type="submit" class="btn-register">إرسال رمز التحقق</button>
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

        document.addEventListener('DOMContentLoaded', function () {
            const departmentSelect = document.querySelector('[data-dept-select]');
            const specializationSelect = document.querySelector('[data-spec-select]');

            function updateSpecializations() {
                const selectedDepartment = departmentSelect.value;
                Array.from(specializationSelect.options).forEach(option => {
                    if (!option.dataset.department) {
                        return;
                    }
                    option.hidden = selectedDepartment && option.dataset.department !== selectedDepartment;
                });

                const selectedOption = specializationSelect.selectedOptions[0];
                if (selectedDepartment && selectedOption && selectedOption.hidden) {
                    specializationSelect.value = '';
                }
            }

            if (departmentSelect && specializationSelect) {
                departmentSelect.addEventListener('change', updateSpecializations);
                updateSpecializations();
            }
        });
    </script>
</body>
</html>

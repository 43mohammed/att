<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إكمال التسجيل - نظام الحضور</title>
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
        .verified-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #a7f3d0;
            text-align: center;
            font-weight: 600;
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
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .back-link a:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h1>📚 إكمال التسجيل</h1>

            <div class="verified-badge">
                ✅ تم التحقق من البريد الإلكتروني بنجاح
            </div>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="error-message">{{ $error }}</div>
                @endforeach
            @endif

            <form method="POST" action="{{ route('register.complete.post') }}">
                @csrf

                <div class="form-group">
                    <label for="name">الاسم الكامل</label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label for="department">القسم</label>
                    <select id="department" name="department" required data-dept-select>
                        <option value="">اختر القسم</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->name }}" {{ old('department') == $department->name ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="specialization">التخصص</label>
                    <select id="specialization" name="specialization" data-spec-select>
                        <option value="">اختر التخصص</option>
                        @foreach($specializations as $specialization)
                            <option value="{{ $specialization->name }}" data-department="{{ $specialization->department->name }}" {{ old('specialization') == $specialization->name ? 'selected' : '' }}>
                                {{ $specialization->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="role">الدور</label>
                    <select id="role" name="role" required>
                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>طالب</option>
                        <option value="instructor" {{ old('role') == 'instructor' ? 'selected' : '' }}>محاضر</option>
                    </select>
                </div>

                <div class="form-group" id="student-id-group" style="{{ old('role') == 'student' ? '' : 'display: none;' }}">
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

            <div class="back-link">
                <a href="{{ route('register') }}">← العودة للتسجيل</a>
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
            }

            departmentSelect.addEventListener('change', updateSpecializations);
            updateSpecializations(); // Initial update
        });
    </script>
</body>
</html>
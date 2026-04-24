<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من البريد الإلكتروني - نظام الحضور</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .verify-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        .verify-box {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        .verify-box h1 {
            color: #2563eb;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }
        .verify-box p {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
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
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 0.5rem;
            font-weight: bold;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-verify {
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
        .btn-verify:hover {
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
        .resend-link {
            margin-top: 1.5rem;
            color: #6b7280;
        }
        .resend-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link {
            margin-top: 1rem;
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
    <div class="verify-container">
        <div class="verify-box">
            <h1>📧 التحقق من البريد الإلكتروني</h1>

            <p>تم إرسال رمز التحقق إلى بريدك الإلكتروني. يرجى إدخال الرمز المكون من 6 أرقام في الحقل أدناه.</p>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="error-message">{{ $error }}</div>
                @endforeach
            @endif

            @if(session('success'))
                <div style="background: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #a7f3d0;">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.verify.post') }}">
                @csrf

                <div class="form-group">
                    <label for="verification_code">رمز التحقق</label>
                    <input type="text" id="verification_code" name="verification_code" required maxlength="6" pattern="[A-Za-z0-9]{6}" placeholder="ABC123">
                </div>

                <button type="submit" class="btn-verify">التحقق والمتابعة</button>
            </form>

            <div class="resend-link">
                <form method="POST" action="{{ route('register.resend') }}" style="display: inline;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: #2563eb; text-decoration: underline; cursor: pointer; font-weight: 600;">لم تستلم الرمز؟ إعادة الإرسال</button>
                </form>
            </div>

            <div class="back-link">
                <a href="{{ route('register') }}">← العودة للتسجيل</a>
            </div>
        </div>
    </div>

    <script>
        // تحسين تجربة المستخدم لإدخال الرمز
        document.getElementById('verification_code').addEventListener('input', function(e) {
            // تحويل إلى أحرف كبيرة
            this.value = this.value.toUpperCase();

            // إزالة أي أحرف غير مسموحة
            this.value = this.value.replace(/[^A-Z0-9]/g, '');
        });
    </script>
</body>
</html>
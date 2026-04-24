<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التحقق من البريد الإلكتروني</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 30px 20px;
            text-align: center;
        }
        .verification-code {
            background-color: #f8f9fa;
            border: 2px dashed #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 3px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 نظام الحضور الجامعي</h1>
            <h2>رمز التحقق من البريد الإلكتروني</h2>
        </div>

        <div class="content">
            <p>مرحباً {{ $user->name }}،</p>

            <p>شكراً لك على التسجيل في نظام الحضور الجامعي. لإكمال عملية التسجيل، يرجى استخدام رمز التحقق التالي:</p>

            <div class="verification-code">
                {{ $verificationCode }}
            </div>

            <div class="warning">
                ⚠️ هذا الرمز صالح لمدة 10 دقائق فقط. يرجى عدم مشاركته مع أي شخص.
            </div>

            <p>إذا لم تقم بطلب هذا الرمز، يرجى تجاهل هذا البريد الإلكتروني.</p>

            <p>مع خالص التحية،<br>
            فريق نظام الحضور الجامعي</p>
        </div>

        <div class="footer">
            <p>هذا البريد الإلكتروني تم إرساله تلقائياً من نظام الحضور الجامعي</p>
            <p>جامعة الحدود الشمالية</p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رابط إعادة تعيين كلمة المرور</title>
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
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
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
        .reset-link {
            word-break: break-all;
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 نظام الحضور الجامعي</h1>
            <h2>إعادة تعيين كلمة المرور</h2>
        </div>

        <div class="content">
            <p>مرحباً {{ $user->name }},</p>
            <p>لقد تلقينا طلبًا لإعادة تعيين كلمة المرور الخاصة بك. اضغط على الزر أدناه لإعادة تعيين كلمة المرور.</p>

            <a href="{{ $resetUrl }}" class="button">إعادة تعيين كلمة المرور</a>

            <p>إذا لم يعمل الزر، يمكنك نسخ الرابط التالي ولصقه في المتصفح:</p>
            <p class="reset-link">{{ $resetUrl }}</p>

            <div class="warning">
                ⚠️ هذا الرابط صالح لمدة 60 دقيقة فقط. إذا لم تطلب استعادة كلمة المرور، يمكنك تجاهل هذا البريد الإلكتروني.
            </div>

            <p>مع خالص التحية،<br>فريق نظام الحضور الجامعي</p>
        </div>

        <div class="footer">
            <p>هذا البريد الإلكتروني تم إرساله تلقائياً من نظام الحضور الجامعي</p>
            <p>جامعة الحدود الشمالية</p>
        </div>
    </div>
</body>
</html>

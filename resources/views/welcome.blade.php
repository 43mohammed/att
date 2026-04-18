<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الحضور والغياب الذكي</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-large {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-large:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .btn-primary-large {
            background: white;
            color: #667eea;
            font-weight: 600;
        }
        .btn-secondary-large {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
        }
        .features {
            padding: 4rem 2rem;
            background: white;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .feature-card {
            background: #f9fafb;
            padding: 2rem;
            border-radius: 0.5rem;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .feature-card h3 {
            color: #2563eb;
            margin-bottom: 1rem;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .footer {
            background: #1f2937;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">📚 نظام الحضور</div>
        <ul class="navbar-menu">
            <li><a href="#features">المميزات</a></li>
            <li><a href="/login" class="btn btn-primary">تسجيل الدخول</a></li>
        </ul>
    </nav>

    <div class="hero">
        <h1>🎓 نظام الحضور والغياب الذكي</h1>
        <p>تطبيق PWA متكامل لتسجيل حضور الطلاب باستخدام QR Code و NFC</p>
        <div class="hero-buttons">
            <a href="/login" class="btn btn-large btn-primary-large">تسجيل الدخول</a>
            <a href="/register" class="btn btn-large btn-secondary-large">إنشاء حساب جديد</a>
        </div>
    </div>

    <div class="features" id="features">
        <div class="container">
            <h2 style="text-align: center; font-size: 2rem; margin-bottom: 1rem;">المميزات الرئيسية</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>تطبيق PWA</h3>
                    <p>تطبيق ويب تقدمي يعمل بدون اتصال بالإنترنت</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>أمان عالي</h3>
                    <p>تشفير كامل وحماية من الغش والتلاعب</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>تقارير متقدمة</h3>
                    <p>تقارير يومية وأسبوعية وشهرية قابلة للتصدير</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>QR Code</h3>
                    <p>رموز QR ديناميكية تتجدد لكل جلسة</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📍</div>
                    <h3>تحديد الموقع</h3>
                    <p>التحقق من موقع الطالب باستخدام GPS</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3>إشعارات فورية</h3>
                    <p>تنبيهات عند اقتراب حد الغياب المسموح</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 نظام الحضور والغياب الذكي - جامعة الحدود الشمالية</p>
        <p>تم تطويره بـ ❤️ لتحسين العملية التعليمية</p>
    </div>

    <script src="/js/app.js"></script>
</body>
</html>

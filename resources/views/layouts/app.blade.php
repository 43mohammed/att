<!DOCTYPE html>
<html lang="{{ session('settings.language', 'ar') }}" dir="{{ session('settings.language', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="نظام الحضور والغياب الذكي">
    
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/image/icon-192x192.png">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <title>@yield('title', 'نظام الحضور والغياب')</title>
</head>
<body class="has-bottom-nav {{ session('settings.dark_mode') ? 'dark-mode' : '' }} font-{{ session('settings.font_size', 'normal') }}" data-auth="{{ session('user_id') ? 'true' : 'false' }}">
    <!-- Header Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            <span>📚</span>
            <span>نظام الحضور</span>
        </div>
        <ul class="navbar-menu">
            <li><a href="/dashboard">لوحة التحكم</a></li>
            <li><a href="/profile">الملف الشخصي</a></li>
            <li>
                <form method="POST" action="/logout" style="display: inline;">
                    @csrf
                    <button type="submit">تسجيل الخروج</button>
                </form>
            </li>
        </ul>
    </nav>

    <div id="pwaInstallBanner" class="install-banner" style="display: none;">
        <div class="install-banner__text">
            <i class="fas fa-download"></i>
            <div>
                <strong>ثبّت التطبيق</strong>
                <div>احصل على التطبيق كاختصار على جهازك.</div>
            </div>
        </div>
        <div class="install-banner__actions">
            <button id="installPwaBtn" class="btn btn-primary btn-sm">تثبيت</button>
            <button type="button" class="install-banner__close" onclick="closeInstallBanner()">×</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Error Alert -->
        @if (session('error'))
            <div class="alert alert-danger">
                <span class="alert-icon">✕</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="/dashboard" class="@if(request()->is('dashboard')) active @endif">
            <span class="bottom-nav-icon">🏠</span>
            <span>الرئيسية</span>
        </a>
        
        @if(session('user_role') === 'student')
            <a href="/student/attendance" class="@if(request()->is('student/attendance')) active @endif">
                <span class="bottom-nav-icon">📋</span>
                <span>الحضور</span>
            </a>
        @elseif(session('user_role') === 'instructor')
            <a href="/instructor/attendance" class="@if(request()->is('instructor/attendance')) active @endif">
                <span class="bottom-nav-icon">📊</span>
                <span>الحضور</span>
            </a>
        @elseif(session('user_role') === 'admin')
            <a href="/admin/users" class="@if(request()->is('admin/users')) active @endif">
                <span class="bottom-nav-icon">👥</span>
                <span>المستخدمون</span>
            </a>
        @endif
        
        <a href="/profile" class="@if(request()->is('profile')) active @endif">
            <span class="bottom-nav-icon">👤</span>
            <span>الملف الشخصي</span>
        </a>
        
        <a href="/settings" class="@if(request()->is('settings')) active @endif">
            <span class="bottom-nav-icon">⚙️</span>
            <span>الإعدادات</span>
        </a>
    </nav>

    <script src="/js/app.js"></script>

    <script>
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallPrompt();
        });

        window.addEventListener('appinstalled', () => {
            closeInstallBanner();
            if (typeof showNotification === 'function') {
                showNotification('✅ تم تثبيت التطبيق بنجاح', 'success');
            }
            deferredPrompt = null;
        });

        function showInstallPrompt() {
            if (!deferredPrompt) {
                return;
            }

            const banner = document.getElementById('pwaInstallBanner');
            const installBtn = document.getElementById('installPwaBtn');
            if (!banner || !installBtn) {
                return;
            }

            banner.style.display = 'flex';

            installBtn.addEventListener('click', async () => {
                banner.style.display = 'none';
                deferredPrompt.prompt();
                const choiceResult = await deferredPrompt.userChoice;

                if (choiceResult.outcome === 'accepted') {
                    if (typeof showNotification === 'function') {
                        showNotification('✅ شكراً! التطبيق تم تثبيته.', 'success');
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification('ℹ️ تم إلغاء تثبيت التطبيق.', 'info');
                    }
                }

                deferredPrompt = null;
            }, { once: true });
        }

        function closeInstallBanner() {
            const banner = document.getElementById('pwaInstallBanner');
            if (banner) {
                banner.style.display = 'none';
            }
        }
    </script>

    @yield('scripts')
</body>
</html>

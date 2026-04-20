<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#2196F3">
    <meta name="description" content="نظام الحضور والغياب الذكي - PWA">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="نظام الحضور">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'نظام الحضور والغياب')</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/pwa.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Apple Icons -->
    <link rel="apple-touch-icon" href="{{ asset('image/icon-192x192.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('image/icon-192x192.png') }}">
    
    @yield('extra-css')
</head>
<body data-auth="{{ session('user_id') ? 'true' : 'false' }}">
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <h1>@yield('header-title', 'نظام الحضور')</h1>
            <div class="user-menu">
                <div class="user-avatar" onclick="toggleUserMenu()">
                    {{ Auth::user()->name[0] ?? 'U' }}
                </div>
                <div id="userMenu" class="user-dropdown" style="display: none;">
                    <a href="{{ route('profile.show') }}">الملف الشخصي</a>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; padding: 0;">تسجيل الخروج</button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div id="pwaInstallBanner" class="install-banner" style="display: none;">
            <div class="install-banner__text">
                <i class="fas fa-download"></i>
                <div>
                    <strong>ثبّت التطبيق</strong>
                    <div>احصل على تطبيق نظام الحضور كاختصار على جهازك.</div>
                </div>
            </div>
            <div class="install-banner__actions">
                <button id="installPwaBtn" class="btn btn-primary btn-sm">تثبيت</button>
                <button type="button" class="install-banner__close" onclick="closeInstallBanner()">×</button>
            </div>
        </div>

        <main class="app-content">
            @if(session('success'))
                <div class="alert alert-success animate-slide-down">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger animate-slide-down">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger animate-slide-down">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="container">
                @yield('content')
            </div>
        </main>

        <!-- Bottom Navigation -->
        <nav class="app-footer">
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>لوحة التحكم</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>المستخدمون</span>
                </a>
                <a href="{{ route('admin.courses.index') }}" class="nav-item {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    <span>المقررات</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-file-chart-line"></i>
                    <span>التقارير</span>
                </a>
            @elseif(Auth::user()->isInstructor())
                <a href="{{ route('instructor.dashboard') }}" class="nav-item {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>الرئيسية</span>
                </a>
                <a href="{{ route('instructor.attendance.index') }}" class="nav-item {{ request()->routeIs('instructor.attendance.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check"></i>
                    <span>الحضور</span>
                </a>
                <a href="{{ route('instructor.courses.index') }}" class="nav-item {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    <span>المقررات</span>
                </a>
                <a href="{{ route('instructor.reports.index') }}" class="nav-item {{ request()->routeIs('instructor.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>التقارير</span>
                </a>
            @else
                <a href="{{ route('student.dashboard') }}" class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>الرئيسية</span>
                </a>
                <a href="{{ route('student.attendance.index') }}" class="nav-item {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
                    <i class="fas fa-qrcode"></i>
                    <span>الحضور</span>
                </a>
                <a href="{{ route('student.courses.index') }}" class="nav-item {{ request()->routeIs('student.courses.*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    <span>المقررات</span>
                </a>
                <a href="{{ route('student.profile.show') }}" class="nav-item {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user"></i>
                    <span>الملف الشخصي</span>
                </a>
            @endif
        </nav>
    </div>

    <!-- JavaScript -->
    <script src="{{ asset('js/pwa.js') }}"></script>
    @yield('extra-js')

    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('✅ Service Worker registered'))
                .catch(err => console.log('❌ Service Worker registration failed:', err));
        }

        // Install PWA Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallPrompt();
        });

        window.addEventListener('appinstalled', () => {
            closeInstallBanner();
            showNotification('✅ تم تثبيت التطبيق بنجاح', 'success');
            deferredPrompt = null;
        });

        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

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
                    showNotification('✅ شكراً! التطبيق تم تثبيته.', 'success');
                } else {
                    showNotification('ℹ️ تم إلغاء تثبيت التطبيق.', 'info');
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
</body>
</html>

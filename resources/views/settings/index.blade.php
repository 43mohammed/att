@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')
<div class="container">
    <form method="POST" action="{{ route('settings.save') }}">
        @csrf

        <!-- Header -->
        <div style="margin-bottom: var(--spacing-2xl);">
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: var(--spacing-sm);">
                الإعدادات
            </h1>
            <p style="color: var(--text-light); font-size: 0.95rem;">
                إدارة تفضيلاتك وخيارات النظام
            </p>
        </div>

    <!-- Display Settings -->
    <div class="card">
        <div class="card-header">
            <span>🎨 إعدادات العرض</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">المظهر الليلي</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">تفعيل الوضع الليلي</p>
                    </div>
                    <input type="checkbox" name="dark_mode" {{ $settings['dark_mode'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">حجم الخط</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">اختر حجم الخط المفضل</p>
                    </div>
                    <select class="form-control" name="font_size" style="width: 120px;">
                        <option value="small" {{ $settings['font_size'] === 'small' ? 'selected' : '' }}>صغير</option>
                        <option value="normal" {{ $settings['font_size'] === 'normal' ? 'selected' : '' }}>عادي</option>
                        <option value="large" {{ $settings['font_size'] === 'large' ? 'selected' : '' }}>كبير</option>
                    </select>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">اللغة</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">اختر لغة الواجهة</p>
                    </div>
                    <select class="form-control" name="language" style="width: 120px;">
                        <option value="ar" {{ $settings['language'] === 'ar' ? 'selected' : '' }}>العربية</option>
                        <option value="en" {{ $settings['language'] === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="card">
        <div class="card-header">
            <span>🔔 إعدادات الإشعارات</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">تنبيهات الحضور</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">تلقي إشعارات عند تسجيل الحضور</p>
                    </div>
                    <input type="checkbox" name="attendance_notifications" {{ $settings['attendance_notifications'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">تنبيهات الغياب</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">تلقي تنبيهات عند تجاوز نسبة الغياب</p>
                    </div>
                    <input type="checkbox" name="absence_alerts" {{ $settings['absence_alerts'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">إشعارات النظام</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">تلقي إشعارات عن تحديثات النظام</p>
                    </div>
                    <input type="checkbox" name="system_notifications" {{ $settings['system_notifications'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">الإشعارات عبر البريد</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">تلقي رسائل بريدية</p>
                    </div>
                    <input type="checkbox" name="email_notifications" {{ $settings['email_notifications'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Settings -->
    <div class="card">
        <div class="card-header">
            <span>🔒 إعدادات الخصوصية</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">ملفي الشخصي عام</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">السماح للآخرين برؤية ملفي الشخصي</p>
                    </div>
                    <input type="checkbox" name="public_profile" {{ $settings['public_profile'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">إظهار حالة الحضور</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">السماح برؤية حالة حضوري الحالية</p>
                    </div>
                    <input type="checkbox" name="show_attendance_status" {{ $settings['show_attendance_status'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">السماح بجمع البيانات</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">السماح بجمع بيانات الاستخدام لتحسين الخدمة</p>
                    </div>
                    <input type="checkbox" name="data_collection" {{ $settings['data_collection'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
            </div>
        </div>
    </div>

    <!-- Location Settings -->
    <div class="card">
        <div class="card-header">
            <span>📍 إعدادات الموقع</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">خدمات الموقع</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">السماح باستخدام موقعك الجغرافي</p>
                    </div>
                    <input type="checkbox" name="location_services" {{ $settings['location_services'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">المنطقة الزمنية</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">اختر منطقتك الزمنية</p>
                    </div>
                    <select class="form-control" name="timezone" style="width: 120px;">
                        <option value="GMT+3" {{ $settings['timezone'] === 'GMT+3' ? 'selected' : '' }}>GMT+3</option>
                        <option value="GMT+2" {{ $settings['timezone'] === 'GMT+2' ? 'selected' : '' }}>GMT+2</option>
                        <option value="GMT+4" {{ $settings['timezone'] === 'GMT+4' ? 'selected' : '' }}>GMT+4</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Settings -->
    <div class="card">
        <div class="card-header">
            <span>📋 إعدادات الحضور</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">التحقق من GPS</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">مطلوب التحقق من الموقع الجغرافي</p>
                    </div>
                    <input type="checkbox" name="gps_verification" {{ $settings['gps_verification'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">قراءة NFC</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">تفعيل قراءة بطاقات NFC</p>
                    </div>
                    <input type="checkbox" name="nfc_enabled" {{ $settings['nfc_enabled'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <div>
                        <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">مسح QR Code</h4>
                        <p style="color: var(--text-light); font-size: 0.85rem;">تفعيل مسح رموز QR</p>
                    </div>
                    <input type="checkbox" name="qr_scanning" {{ $settings['qr_scanning'] ? 'checked' : '' }} style="width: 50px; height: 30px; cursor: pointer;">
                </div>
            </div>
        </div>
    </div>

    <!-- Data & Storage -->
    <div class="card">
        <div class="card-header">
            <span>💾 البيانات والتخزين</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <div style="padding: var(--spacing-lg); background: var(--light); border-radius: var(--radius-lg);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md);">
                        <h4 style="font-weight: 700; color: var(--dark);">حجم البيانات المخزنة</h4>
                        <span style="color: var(--primary); font-weight: 700;">{{ $settings['storage_used'] }}</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: 25%;"></div>
                    </div>
                </div>
                <button class="btn btn-secondary btn-block">
                    🗑️ حذف البيانات المخزنة
                </button>
                <button class="btn btn-secondary btn-block">
                    ⬇️ تنزيل نسخة من بياناتي
                </button>
            </div>
        </div>
    </div>

    <!-- About & Help -->
    <div class="card">
        <div class="card-header">
            <span>ℹ️ حول التطبيق والمساعدة</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <span style="color: var(--text-light); font-weight: 600;">إصدار التطبيق</span>
                    <span style="color: var(--dark); font-weight: 600;">{{ $settings['app_version'] }}</span>
                </div>
                <button class="btn btn-outline btn-block">
                    📖 سياسة الخصوصية
                </button>
                <button class="btn btn-outline btn-block">
                    📋 شروط الاستخدام
                </button>
                <button class="btn btn-outline btn-block">
                    ❓ الأسئلة الشائعة
                </button>
                <button class="btn btn-outline btn-block">
                    💬 تواصل معنا
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: var(--spacing-lg);">
            {{ session('success') }}
        </div>
    @endif

    <!-- Save Settings Button -->
    <div style="margin-top: var(--spacing-2xl);">
        <button type="submit" class="btn btn-primary btn-lg btn-block">
            💾 حفظ الإعدادات
        </button>
    </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'لوحة تحكم الإدارة')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div style="margin-bottom: var(--spacing-2xl);">
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: var(--spacing-sm);">
            لوحة تحكم الإدارة
        </h1>
        <p style="color: var(--text-light); font-size: 0.95rem;">
            إدارة شاملة للنظام والمستخدمين والمقررات
        </p>
    </div>

    <!-- Main Statistics Grid -->
    <div class="grid grid-3">
        <div class="stat-card">
            <h3>إجمالي المستخدمين</h3>
            <div class="value">{{ $stats['total_users'] ?? 0 }}</div>
            <div class="subtitle">مستخدم نشط</div>
        </div>
        <div class="stat-card info">
            <h3>الطلاب</h3>
            <div class="value">{{ $stats['total_students'] ?? 0 }}</div>
            <div class="subtitle">طالب مسجل</div>
        </div>
        <div class="stat-card success">
            <h3>المدرسون</h3>
            <div class="value">{{ $stats['total_instructors'] ?? 0 }}</div>
            <div class="subtitle">مدرس نشط</div>
        </div>
    </div>

    <!-- Secondary Statistics -->
    <div class="grid grid-3">
        <div class="stat-card warning">
            <h3>المقررات</h3>
            <div class="value">{{ $stats['total_courses'] ?? 0 }}</div>
            <div class="subtitle">مقرر</div>
        </div>
        <div class="stat-card danger">
            <h3>الجلسات</h3>
            <div class="value">{{ $stats['total_sessions'] ?? 0 }}</div>
            <div class="subtitle">جلسة</div>
        </div>
        <div class="stat-card primary">
            <h3>سجلات الحضور</h3>
            <div class="value">{{ $stats['total_records'] ?? 0 }}</div>
            <div class="subtitle">تسجيل</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <span>⚡ إجراءات سريعة</span>
        </div>
        <div class="card-body">
            <div class="grid grid-2">
                <a href="/admin/users/create" class="btn btn-primary btn-lg btn-block" style="text-decoration: none;">
                    <span>👤</span>
                    <span>إضافة مستخدم</span>
                </a>
                <a href="/admin/courses/create" class="btn btn-success btn-lg btn-block" style="text-decoration: none;">
                    <span>📚</span>
                    <span>إضافة مقرر</span>
                </a>
                <a href="/admin/reports" class="btn btn-info btn-lg btn-block" style="text-decoration: none;">
                    <span>📊</span>
                    <span>عرض التقارير</span>
                </a>
                <a href="/admin/audit-logs" class="btn btn-warning btn-lg btn-block" style="text-decoration: none;">
                    <span>📋</span>
                    <span>سجلات النظام</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Management Sections -->
    <div class="grid grid-2">
        <!-- Users Management -->
        <div class="card">
            <div class="card-header">
                <span>👥 إدارة المستخدمين</span>
                <a href="/admin/users" class="btn btn-sm btn-secondary">عرض الكل</a>
            </div>
            <div class="card-body">
                <div style="display: grid; gap: var(--spacing-md);">
                    <div style="padding: var(--spacing-lg); background: var(--light); border-radius: var(--radius-lg); text-align: center;">
                        <div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: var(--spacing-sm);">
                            {{ $stats['total_users'] ?? 0 }}
                        </div>
                        <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: var(--spacing-md);">
                            إجمالي المستخدمين النشطين
                        </p>
                        <a href="/admin/users" class="btn btn-sm btn-primary btn-block">إدارة المستخدمين</a>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                        <div style="padding: var(--spacing-lg); background: var(--primary-light); border-radius: var(--radius-lg); text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: var(--spacing-xs);">
                                {{ $stats['total_students'] ?? 0 }}
                            </div>
                            <p style="color: var(--text-light); font-size: 0.85rem;">طلاب</p>
                        </div>
                        <div style="padding: var(--spacing-lg); background: var(--success-light); border-radius: var(--radius-lg); text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--success); margin-bottom: var(--spacing-xs);">
                                {{ $stats['total_instructors'] ?? 0 }}
                            </div>
                            <p style="color: var(--text-light); font-size: 0.85rem;">مدرسون</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Management -->
        <div class="card">
            <div class="card-header">
                <span>📚 إدارة المقررات</span>
                <a href="/admin/courses" class="btn btn-sm btn-secondary">عرض الكل</a>
            </div>
            <div class="card-body">
                <div style="display: grid; gap: var(--spacing-md);">
                    <div style="padding: var(--spacing-lg); background: var(--light); border-radius: var(--radius-lg); text-align: center;">
                        <div style="font-size: 2rem; font-weight: 800; color: var(--warning); margin-bottom: var(--spacing-sm);">
                            {{ $stats['total_courses'] ?? 0 }}
                        </div>
                        <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: var(--spacing-md);">
                            إجمالي المقررات الدراسية
                        </p>
                        <a href="/admin/courses" class="btn btn-sm btn-warning btn-block">إدارة المقررات</a>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                        <div style="padding: var(--spacing-lg); background: var(--danger-light); border-radius: var(--radius-lg); text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger); margin-bottom: var(--spacing-xs);">
                                {{ $stats['total_sessions'] ?? 0 }}
                            </div>
                            <p style="color: var(--text-light); font-size: 0.85rem;">جلسات</p>
                        </div>
                        <div style="padding: var(--spacing-lg); background: var(--info-light); border-radius: var(--radius-lg); text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--info); margin-bottom: var(--spacing-xs);">
                                {{ $stats['total_records'] ?? 0 }}
                            </div>
                            <p style="color: var(--text-light); font-size: 0.85rem;">سجلات</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Management -->
    <div class="grid grid-2">
        <!-- Reports -->
        <div class="card">
            <div class="card-header">
                <span>📊 التقارير</span>
            </div>
            <div class="card-body">
                <div style="display: grid; gap: var(--spacing-md);">
                    <a href="/admin/reports" class="btn btn-outline" style="text-decoration: none;">
                        📊 عرض جميع التقارير
                    </a>
                    <div style="padding: var(--spacing-lg); background: var(--light); border-radius: var(--radius-lg);">
                        <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: var(--spacing-sm);">
                            إنشاء تقرير جديد
                        </p>
                        <select class="form-control" style="margin-bottom: var(--spacing-md);">
                            <option>اختر نوع التقرير</option>
                            <option>تقرير يومي</option>
                            <option>تقرير أسبوعي</option>
                            <option>تقرير شهري</option>
                        </select>
                        <button class="btn btn-primary btn-sm btn-block">إنشاء</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings & Logs -->
        <div class="card">
            <div class="card-header">
                <span>⚙️ الإعدادات والسجلات</span>
            </div>
            <div class="card-body">
                <div style="display: grid; gap: var(--spacing-md);">
                    <a href="/admin/settings" class="btn btn-outline" style="text-decoration: none;">
                        ⚙️ إعدادات النظام
                    </a>
                    <a href="/admin/audit-logs" class="btn btn-outline" style="text-decoration: none;">
                        📋 سجلات التدقيق
                    </a>
                    <a href="/admin/schedules" class="btn btn-outline" style="text-decoration: none;">
                        📅 الجداول الزمنية
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <span>🔔 آخر الأنشطة</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                @forelse($recentActivities as $activity)
                    <div style="padding: var(--spacing-lg); background: var(--light); border-radius: var(--radius-lg); border-right: 4px solid var(--primary);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-sm);">
                            <div>
                                <h4 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">{{ ucfirst($activity->action) }}</h4>
                                <p style="color: var(--text-light); font-size: 0.85rem;">{{ $activity->model }} #{{ $activity->model_id }} بواسطة {{ optional($activity->user)->name ?? 'مستخدم' }}</p>
                            </div>
                            <span style="font-size: 0.8rem; color: var(--text-light);">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div style="padding: var(--spacing-lg); background: var(--light); border-radius: var(--radius-lg);">
                        لا توجد أنشطة حديثة.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
.stat-card.primary {
    border-top-color: var(--primary);
}

.stat-card.primary .value {
    color: var(--primary);
}
</style>
@endsection

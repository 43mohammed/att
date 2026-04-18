@extends('layouts.app')

@section('title', 'إدارة الجداول الدراسية')

@section('content')
<div class="container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: var(--spacing-sm);">
                📅 الجداول الدراسية
            </h1>
            <p style="color: var(--text-light); font-size: 0.95rem;">
                إدارة جداول المحاضرات والجلسات
            </p>
        </div>
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary btn-lg" style="text-decoration: none;">
            ➕ إضافة جدول
        </a>
    </div>

    <!-- Filter -->
    <div class="card" style="margin-bottom: var(--spacing-2xl);">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                <select class="form-control" id="courseFilter" onchange="filterSchedules()">
                    <option value="">جميع المقررات</option>
                    @foreach($courses as $course)
                        <option value="{{ strtolower($course->code) }}">{{ $course->name }}</option>
                    @endforeach
                </select>
                <select class="form-control" id="dayFilter" onchange="filterSchedules()">
                    <option value="">جميع الأيام</option>
                    @php
                        $dayNames = ['sunday' => 'الأحد', 'monday' => 'الاثنين', 'tuesday' => 'الثلاثاء', 'wednesday' => 'الأربعاء', 'thursday' => 'الخميس'];
                    @endphp
                    @foreach($days as $day)
                        <option value="{{ $day }}">{{ $dayNames[$day] ?? ucfirst($day) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-3">
        <div class="stat-card">
            <h3>إجمالي الجداول</h3>
            <div class="value">{{ $totalSchedules }}</div>
            <div class="subtitle">جدول</div>
        </div>
        <div class="stat-card info">
            <h3>الجداول النشطة</h3>
            <div class="value">{{ $activeSchedules }}</div>
            <div class="subtitle">جدول نشط</div>
        </div>
        <div class="stat-card success">
            <h3>جلسات اليوم</h3>
            <div class="value">{{ $todaySessions }}</div>
            <div class="subtitle">جلسة</div>
        </div>
    </div>

    <!-- Schedules List -->
    <div style="display: grid; gap: var(--spacing-lg);">
        @forelse($schedules as $schedule)
            @php
                $dayKey = strtolower($schedule->session_date->format('l'));
                $dayNames = ['sunday' => 'الأحد', 'monday' => 'الاثنين', 'tuesday' => 'الثلاثاء', 'wednesday' => 'الأربعاء', 'thursday' => 'الخميس'];
            @endphp
            <div class="card schedule-card" data-course="{{ strtolower($schedule->course->code ?? '') }}" data-day="{{ $dayKey }}">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-md);">
                        <div style="flex: 1;">
                            <h3 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">
                                {{ $schedule->course->name ?? 'غير محدد' }}
                            </h3>
                            <p style="color: var(--text-light); font-size: 0.9rem;">
                                {{ $schedule->course->code ?? '---' }}
                            </p>
                        </div>
                        <span class="badge {{ $schedule->status === 'active' ? 'badge-success' : ($schedule->status === 'cancelled' ? 'badge-danger' : 'badge-secondary') }}">
                            {{ $schedule->status === 'active' ? '✓ نشط' : ($schedule->status === 'cancelled' ? '⊘ ملغى' : '✓ مغلق') }}
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md); padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg); margin-bottom: var(--spacing-md);">
                        <div>
                            <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">اليوم</p>
                            <p style="color: var(--dark); font-weight: 600;">{{ $dayNames[$dayKey] ?? $schedule->session_date->format('l') }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">الوقت</p>
                            <p style="color: var(--dark); font-weight: 600;">{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">القاعة</p>
                            <p style="color: var(--dark); font-weight: 600;">{{ $schedule->classroom_latitude ? $schedule->classroom_latitude . ', ' . $schedule->classroom_longitude : 'غير محدد' }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">المحاضر</p>
                            <p style="color: var(--dark); font-weight: 600;">{{ $schedule->instructor->name ?? 'غير محدد' }}</p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}" class="btn btn-secondary btn-block" style="text-decoration: none; text-align: center;">
                            ✏️ تعديل
                        </a>
                        <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" style="display: contents;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('هل أنت متأكد من حذف هذا الجدول؟')">
                                🗑️ حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1;">
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: var(--spacing-2xl);">
                        <div style="font-size: 3rem; margin-bottom: var(--spacing-md);">📭</div>
                        <h3 style="color: var(--dark); margin-bottom: var(--spacing-sm);">لا يوجد جداول</h3>
                        <p style="color: var(--text-light); margin-bottom: var(--spacing-lg);">
                            لا توجد جلسات مجدولة في النظام.
                        </p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

</div>

<script>
function filterSchedules() {
    const courseFilter = document.getElementById('courseFilter').value.toLowerCase();
    const dayFilter = document.getElementById('dayFilter').value.toLowerCase();
    
    const scheduleCards = document.querySelectorAll('.schedule-card');
    
    scheduleCards.forEach(card => {
        let show = true;
        
        if (courseFilter) {
            const course = card.getAttribute('data-course').toLowerCase();
            show = course.includes(courseFilter);
        }
        
        if (dayFilter && show) {
            const day = card.getAttribute('data-day').toLowerCase();
            show = day.includes(dayFilter);
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}
</script>

<style>
.badge-success {
    background: var(--success-light);
    color: var(--success);
}

.badge-secondary {
    background: #e5e7eb;
    color: #6b7280;
}

.badge-danger {
    background: #fde2e2;
    color: #b91c1c;
}

.schedule-card {
    transition: all 0.3s ease;
}

.schedule-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}
</style>
@endsection

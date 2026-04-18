@extends('layouts.app')

@section('title', 'إدارة المقررات')

@section('content')
<div class="container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: var(--spacing-sm);">
                📚 إدارة المقررات
            </h1>
            <p style="color: var(--text-light); font-size: 0.95rem;">
                إدارة جميع المقررات الدراسية
            </p>
        </div>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-success btn-lg" style="text-decoration: none;">
            ➕ إضافة مقرر
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="card" style="margin-bottom: var(--spacing-2xl);">
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <input 
                    type="text" 
                    class="form-control" 
                    placeholder="🔍 ابحث عن مقرر..."
                    id="searchInput"
                    onkeyup="filterCourses()"
                >
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <select class="form-control" id="departmentFilter" onchange="filterCourses()">
                        <option value="">جميع الأقسام</option>
                        <option value="cs">علوم الحاسب</option>
                        <option value="eng">الهندسة</option>
                        <option value="bus">إدارة الأعمال</option>
                    </select>
                    <select class="form-control" id="statusFilter" onchange="filterCourses()">
                        <option value="">جميع الحالات</option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-3">
        <div class="stat-card">
            <h3>إجمالي المقررات</h3>
            <div class="value">{{ $courses->count() }}</div>
            <div class="subtitle">مقرر</div>
        </div>
        <div class="stat-card success">
            <h3>المقررات النشطة</h3>
            <div class="value">{{ $courses->filter(fn($c) => $c->status === 'active')->count() }}</div>
            <div class="subtitle">مقرر نشط</div>
        </div>
        <div class="stat-card warning">
            <h3>إجمالي الطلاب</h3>
            <div class="value">{{ $courses->sum(fn($c) => $c->students_count ?? 0) }}</div>
            <div class="subtitle">طالب</div>
        </div>
    </div>

    <!-- Courses Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-lg);">
        @forelse($courses ?? [] as $course)
            <div class="card course-card">
                <div class="card-body">
                    <!-- Course Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-md);">
                        <div>
                            <h3 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">
                                {{ $course->name }}
                            </h3>
                            <p style="color: var(--text-light); font-size: 0.9rem;">
                                {{ $course->code }}
                            </p>
                        </div>
                        <span class="badge {{ $course->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                            {{ $course->status === 'active' ? '✓ نشط' : '⊘ غير نشط' }}
                        </span>
                    </div>

                    <!-- Course Info -->
                    <div style="padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg); margin-bottom: var(--spacing-md);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
                            <div>
                                <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">المحاضر</p>
                                <p style="color: var(--dark); font-weight: 600; font-size: 0.9rem;">{{ $course->instructor->name ?? 'غير محدد' }}</p>
                            </div>
                            <div>
                                <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">الطلاب</p>
                                <p style="color: var(--dark); font-weight: 600; font-size: 0.9rem;">{{ $course->students_count ?? 0 }} طالب</p>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                            <div>
                                <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">الجلسات</p>
                                <p style="color: var(--dark); font-weight: 600; font-size: 0.9rem;">{{ $course->sessions_count ?? 0 }} جلسة</p>
                            </div>
                            <div>
                                <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">القسم</p>
                                <p style="color: var(--dark); font-weight: 600; font-size: 0.9rem;">{{ $course->department ?? 'غير محدد' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Course Description -->
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: var(--spacing-md); line-height: 1.5;">
                        {{ substr($course->description ?? 'لا توجد وصف', 0, 100) }}...
                    </p>

                    <!-- Actions -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                        <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-secondary btn-block" style="text-decoration: none; text-align: center;">
                            ✏️ تعديل
                        </a>
                        <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" style="display: contents;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('هل أنت متأكد من حذف هذا المقرر؟')">
                                🗑️ حذف
                            </button>
                        </form>
                    </div>

                    <!-- View Details -->
                    <a href="{{ route('admin.courses.show', $course->id) }}" class="btn btn-outline btn-block" style="text-decoration: none; text-align: center; margin-top: var(--spacing-md);">
                        👁️ عرض التفاصيل
                    </a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1;">
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: var(--spacing-2xl);">
                        <div style="font-size: 3rem; margin-bottom: var(--spacing-md);">📭</div>
                        <h3 style="color: var(--dark); margin-bottom: var(--spacing-sm);">لا يوجد مقررات</h3>
                        <p style="color: var(--text-light); margin-bottom: var(--spacing-lg);">
                            لم يتم العثور على أي مقررات في النظام
                        </p>
                        <a href="{{ route('admin.courses.create') }}" class="btn btn-success" style="text-decoration: none;">
                            ➕ إضافة مقرر جديد
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
function filterCourses() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const departmentFilter = document.getElementById('departmentFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const courseCards = document.querySelectorAll('.course-card');
    
    courseCards.forEach(card => {
        let show = true;
        
        // Search filter
        if (searchInput) {
            const text = card.textContent.toLowerCase();
            show = text.includes(searchInput);
        }
        
        // Department filter
        if (departmentFilter && show) {
            const text = card.textContent.toLowerCase();
            show = text.includes(departmentFilter);
        }
        
        // Status filter
        if (statusFilter && show) {
            const badge = card.querySelector('.badge');
            show = badge.textContent.toLowerCase().includes(statusFilter);
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

.course-card {
    transition: all 0.3s ease;
}

.course-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
}
</style>
@endsection

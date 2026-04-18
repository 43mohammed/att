@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@section('content')
<div class="container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: var(--spacing-sm);">
                👥 إدارة المستخدمين
            </h1>
            <p style="color: var(--text-light); font-size: 0.95rem;">
                إدارة جميع مستخدمي النظام
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-lg" style="text-decoration: none;">
            ➕ إضافة مستخدم
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="card" style="margin-bottom: var(--spacing-2xl);">
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <input 
                    type="text" 
                    class="form-control" 
                    placeholder="🔍 ابحث عن مستخدم بالاسم أو البريد الإلكتروني..."
                    id="searchInput"
                    onkeyup="filterUsers()"
                >
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <select class="form-control" id="roleFilter" onchange="filterUsers()">
                        <option value="">جميع الأدوار</option>
                        <option value="admin">مدير</option>
                        <option value="instructor">محاضر</option>
                        <option value="student">طالب</option>
                    </select>
                    <select class="form-control" id="statusFilter" onchange="filterUsers()">
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
            <h3>إجمالي المستخدمين</h3>
            <div class="value">{{ count($users->items ?? []) }}</div>
            <div class="subtitle">مستخدم</div>
        </div>
        <div class="stat-card info">
            <h3>الطلاب</h3>
            <div class="value">{{ count(array_filter($users->items ?? [], function($u) { return $u->role === 'student'; })) }}</div>
            <div class="subtitle">طالب</div>
        </div>
        <div class="stat-card success">
            <h3>المحاضرون</h3>
            <div class="value">{{ count(array_filter($users->items ?? [], function($u) { return $u->role === 'instructor'; })) }}</div>
            <div class="subtitle">محاضر</div>
        </div>
    </div>

    <!-- Users List -->
    <div style="display: grid; gap: var(--spacing-lg);">
        @forelse($users ?? [] as $user)
            <div class="card user-card" style="border-left: 4px solid {{ $user->role === 'admin' ? 'var(--danger)' : ($user->role === 'instructor' ? 'var(--warning)' : 'var(--primary)') }};">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-md);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-sm);">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    👤
                                </div>
                                <div>
                                    <h3 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">
                                        {{ $user->name }}
                                    </h3>
                                    <p style="color: var(--text-light); font-size: 0.9rem;">
                                        {{ $user->email }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge {{ $user->role === 'admin' ? 'badge-danger' : ($user->role === 'instructor' ? 'badge-warning' : 'badge-primary') }}">
                                {{ $user->role === 'admin' ? '👨‍💼 مدير' : ($user->role === 'instructor' ? '👨‍🏫 محاضر' : '👨‍🎓 طالب') }}
                            </span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md); padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg); margin-bottom: var(--spacing-md);">
                        <div>
                            <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">القسم</p>
                            <p style="color: var(--dark); font-weight: 600;">{{ $user->department ?? 'غير محدد' }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 0.85rem; margin-bottom: var(--spacing-xs);">تاريخ الانضمام</p>
                            <p style="color: var(--dark); font-weight: 600;">{{ $user->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-secondary btn-block" style="text-decoration: none;">
                            ✏️ تعديل
                        </a>
                        <form action="#" method="POST" style="display: contents;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                🗑️ حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body" style="text-align: center; padding: var(--spacing-2xl);">
                    <div style="font-size: 3rem; margin-bottom: var(--spacing-md);">📭</div>
                    <h3 style="color: var(--dark); margin-bottom: var(--spacing-sm);">لا يوجد مستخدمين</h3>
                    <p style="color: var(--text-light); margin-bottom: var(--spacing-lg);">
                        لم يتم العثور على أي مستخدمين في النظام
                    </p>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="text-decoration: none;">
                        ➕ إضافة مستخدم جديد
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
function filterUsers() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const userCards = document.querySelectorAll('.user-card');
    
    userCards.forEach(card => {
        let show = true;
        
        // Search filter
        if (searchInput) {
            const text = card.textContent.toLowerCase();
            show = text.includes(searchInput);
        }
        
        // Role filter
        if (roleFilter && show) {
            const badge = card.querySelector('.badge');
            show = badge.textContent.toLowerCase().includes(roleFilter);
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}
</script>

<style>
.badge {
    display: inline-block;
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-full);
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-primary {
    background: var(--primary-light);
    color: var(--primary);
}

.badge-warning {
    background: #fef3c7;
    color: #f59e0b;
}

.badge-danger {
    background: #fee2e2;
    color: var(--danger);
}

.user-card {
    transition: all 0.3s ease;
}

.user-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}
</style>
@endsection

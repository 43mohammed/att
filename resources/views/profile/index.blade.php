@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
<div class="container">
    <!-- Profile Header -->
    <div class="card" style="text-align: center; margin-bottom: var(--spacing-2xl);">
        <div class="card-body">
            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: var(--radius-full); margin: 0 auto var(--spacing-lg); display: flex; align-items: center; justify-content: center; color: var(--white); font-size: 2.5rem;">
                👤
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--dark); margin-bottom: var(--spacing-sm);">
                {{ $user->name }}
            </h1>
            <p style="color: var(--text-light); font-size: 0.95rem; margin-bottom: var(--spacing-md);">
                {{ $user->role === 'student' ? 'طالب' : ucfirst($user->role) }} - {{ $user->department ?? 'غير محدد' }}
            </p>
            <div style="display: inline-block; padding: var(--spacing-sm) var(--spacing-lg); background: var(--primary-light); border-radius: var(--radius-full); color: var(--primary); font-weight: 600; font-size: 0.9rem;">
                رقم الطالب: {{ $user->student_id ?? $user->id }}
            </div>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="card">
        <div class="card-header">
            <span>📋 المعلومات الشخصية</span>
            <button class="btn btn-sm btn-primary" onclick="showEditModal()">تعديل</button>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <span style="color: var(--text-light); font-weight: 600;">البريد الإلكتروني</span>
                    <span style="color: var(--dark); font-weight: 600;">{{ $user->email }}</span>
                </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <span style="color: var(--text-light); font-weight: 600;">رقم الهاتف</span>
                    <span style="color: var(--dark); font-weight: 600;">{{ $user->phone ?? '-' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <span style="color: var(--text-light); font-weight: 600;">تاريخ الميلاد</span>
                    <span style="color: var(--dark); font-weight: 600;">{{ $user->date_of_birth ?? '-' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--light); border-radius: var(--radius-lg);">
                    <span style="color: var(--text-light); font-weight: 600;">الجنسية</span>
                    <span style="color: var(--dark); font-weight: 600;">{{ $user->nationality ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Statistics (for Students) -->
    <div class="card">
        <div class="card-header">
            <span>📊 إحصائيات الحضور</span>
        </div>
        <div class="card-body">
            <div class="grid grid-2">
                <div style="padding: var(--spacing-lg); background: var(--success-light); border-radius: var(--radius-lg); text-align: center;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--success); margin-bottom: var(--spacing-xs);">
                        {{ $attendanceCount }}
                    </div>
                    <p style="color: var(--text-light); font-size: 0.9rem;">جلسات حاضر</p>
                </div>
                <div style="padding: var(--spacing-lg); background: var(--danger-light); border-radius: var(--radius-lg); text-align: center;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--danger); margin-bottom: var(--spacing-xs);">
                        {{ $absenceCount }}
                    </div>
                    <p style="color: var(--text-light); font-size: 0.9rem;">جلسات غائب</p>
                </div>
            </div>
            <div style="margin-top: var(--spacing-lg);">
                <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: var(--spacing-md);">معدل الحضور العام</p>
                <div class="progress">
                    <div class="progress-bar success" style="width: {{ $attendancePercentage }}%;"></div>
                </div>
                <p style="text-align: center; color: var(--success); font-weight: 700; margin-top: var(--spacing-sm);">{{ $attendancePercentage }}%</p>
            </div>
        </div>
    </div>

    <!-- Security & Preferences -->
    <div class="card">
        <div class="card-header">
            <span>🔒 الأمان والتفضيلات</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: var(--spacing-md);">
                <button class="btn btn-outline btn-block" onclick="showChangePasswordModal()">
                    🔑 تغيير كلمة المرور
                </button>
                <button class="btn btn-outline btn-block">
                    🔔 إدارة الإشعارات
                </button>
                <button class="btn btn-outline btn-block">
                    🌙 المظهر الليلي
                </button>
                <button class="btn btn-outline btn-block">
                    🌍 اللغة والمنطقة الزمنية
                </button>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card" style="border-top: 3px solid var(--danger);">
        <div class="card-header">
            <span>⚠️ منطقة الخطر</span>
        </div>
        <div class="card-body">
            <p style="color: var(--text-light); font-size: 0.95rem; margin-bottom: var(--spacing-lg);">
                هذه الإجراءات لا يمكن التراجع عنها. يرجى التأكد قبل المتابعة.
            </p>
            <button class="btn btn-danger btn-block" onclick="showDeleteAccountModal()">
                🗑️ حذف الحساب بشكل دائم
            </button>
        </div>
    </div>

    <!-- Last Updated -->
    <div style="text-align: center; padding: var(--spacing-lg); color: var(--text-light); font-size: 0.85rem;">
        آخر تحديث: {{ $lastUpdated }}
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>تعديل الملف الشخصي</h2>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <div class="modal-body">
            <form style="display: grid; gap: var(--spacing-md);">
                <div class="form-group">
                    <label>الاسم الكامل</label>
                    <input type="text" class="form-control" value="{{ $user->name }}">
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" class="form-control" value="{{ $user->email }}">
                </div>
                <div class="form-group">
                    <label>رقم الهاتف</label>
                    <input type="tel" class="form-control" value="{{ $user->phone ?? '' }}">
                </div>
                <div class="form-group">
                    <label>تاريخ الميلاد</label>
                    <input type="date" class="form-control" value="{{ $user->date_of_birth ?? '' }}">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editModal')">إلغاء</button>
            <button class="btn btn-primary" onclick="saveChanges()">حفظ التغييرات</button>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal" id="passwordModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>تغيير كلمة المرور</h2>
            <button class="modal-close" onclick="closeModal('passwordModal')">✕</button>
        </div>
        <div class="modal-body">
            <form style="display: grid; gap: var(--spacing-md);">
                <div class="form-group">
                    <label>كلمة المرور الحالية</label>
                    <input type="password" class="form-control" placeholder="أدخل كلمة المرور الحالية">
                </div>
                <div class="form-group">
                    <label>كلمة المرور الجديدة</label>
                    <input type="password" class="form-control" placeholder="أدخل كلمة المرور الجديدة">
                </div>
                <div class="form-group">
                    <label>تأكيد كلمة المرور الجديدة</label>
                    <input type="password" class="form-control" placeholder="أعد إدخال كلمة المرور الجديدة">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('passwordModal')">إلغاء</button>
            <button class="btn btn-primary" onclick="changePassword()">تحديث كلمة المرور</button>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>حذف الحساب</h2>
            <button class="modal-close" onclick="closeModal('deleteModal')">✕</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger">
                <span class="alert-icon">⚠️</span>
                <div>
                    <strong>تحذير:</strong> حذف حسابك سيؤدي إلى فقدان جميع البيانات بشكل دائم ولا يمكن التراجع عن هذا الإجراء.
                </div>
            </div>
            <p style="color: var(--text-light); margin-bottom: var(--spacing-md);">
                أدخل كلمة المرور الخاصة بك للتأكيد:
            </p>
            <input type="password" class="form-control" placeholder="أدخل كلمة المرور">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('deleteModal')">إلغاء</button>
            <button class="btn btn-danger" onclick="deleteAccount()">حذف الحساب بشكل دائم</button>
        </div>
    </div>
</div>

<script>
function showEditModal() {
    document.getElementById('editModal').classList.add('active');
}

function showChangePasswordModal() {
    document.getElementById('passwordModal').classList.add('active');
}

function showDeleteAccountModal() {
    document.getElementById('deleteModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function saveChanges() {
    alert('تم حفظ التغييرات بنجاح');
    closeModal('editModal');
}

function changePassword() {
    alert('تم تحديث كلمة المرور بنجاح');
    closeModal('passwordModal');
}

function deleteAccount() {
    if (confirm('هل أنت متأكد من رغبتك في حذف حسابك؟ هذا الإجراء لا يمكن التراجع عنه.')) {
        alert('تم حذف الحساب بنجاح');
        window.location.href = '/login';
    }
}

// Close modals when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>
@endsection

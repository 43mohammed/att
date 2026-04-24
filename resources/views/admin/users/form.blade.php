@extends('layouts.app')

@section('title', isset($user) ? 'تعديل مستخدم' : 'إضافة مستخدم جديد')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>{{ isset($user) ? 'تعديل مستخدم' : 'إضافة مستخدم جديد' }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="name">الاسم الكامل *</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني *</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
                </div>

                <div class="form-group">
                    <label for="role">الدور *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">-- اختر دور --</option>
                        <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>مسؤول</option>
                        <option value="instructor" {{ old('role', $user->role ?? '') === 'instructor' ? 'selected' : '' }}>محاضر</option>
                        <option value="student" {{ old('role', $user->role ?? '') === 'student' ? 'selected' : '' }}>طالب</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور {{ !isset($user) ? '*' : '(اتركها فارغة لعدم التغيير)' }}</label>
                    <input type="password" id="password" name="password" class="form-control" {{ !isset($user) ? 'required' : '' }}>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">تأكيد كلمة المرور</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                </div>

                <div class="form-group">
                    <label for="department">القسم</label>
                    <select id="department" name="department" class="form-control" data-dept-select>
                        <option value="">-- اختر القسم --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}" {{ old('department', $user->department ?? '') === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="specialization">التخصص</label>
                    <select id="specialization" name="specialization" class="form-control" data-spec-select>
                        <option value="">-- اختر التخصص --</option>
                        @foreach($specializations as $specialization)
                            <option value="{{ $specialization->name }}" data-department="{{ $specialization->department->name }}" {{ old('specialization', $user->specialization ?? '') === $specialization->name ? 'selected' : '' }}>
                                {{ $specialization->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                        تفعيل الحساب
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">{{ isset($user) ? 'تحديث' : 'إضافة' }}</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 0.25rem;
        font-size: 1rem;
    }

    .form-actions {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary { background-color: #007bff; color: white; }
    .btn-secondary { background-color: #6c757d; color: white; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const departmentSelect = document.querySelector('[data-dept-select]');
        const specializationSelect = document.querySelector('[data-spec-select]');

        if (!departmentSelect || !specializationSelect) {
            return;
        }

        function updateSpecializations() {
            const selectedDepartment = departmentSelect.value;
            Array.from(specializationSelect.options).forEach(option => {
                if (!option.dataset.department) {
                    return;
                }
                option.hidden = selectedDepartment && option.dataset.department !== selectedDepartment;
            });

            const selectedOption = specializationSelect.selectedOptions[0];
            if (selectedDepartment && selectedOption && selectedOption.hidden) {
                specializationSelect.value = '';
            }
        }

        departmentSelect.addEventListener('change', updateSpecializations);
        updateSpecializations();
    });
</script>
@endsection

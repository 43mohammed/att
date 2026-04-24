@extends('layouts.app')

@section('title', isset($course) ? 'تعديل مقرر' : 'إضافة مقرر جديد')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>{{ isset($course) ? 'تعديل مقرر' : 'إضافة مقرر جديد' }}</h1>
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
            <form action="{{ isset($course) ? route('admin.courses.update', $course->id) : route('admin.courses.store') }}" method="POST">
                @csrf
                @if(isset($course))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="name">اسم المقرر *</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $course->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="code">كود المقرر *</label>
                    <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $course->code ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="description">الوصف</label>
                    <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $course->description ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="department">القسم *</label>
                    <select id="department" name="department" class="form-control" required data-dept-select>
                        <option value="">-- اختر القسم --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}" {{ old('department', $course->department ?? '') === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="specialization">التخصص</label>
                    <select id="specialization" name="specialization" class="form-control" data-spec-select>
                        <option value="">-- اختر التخصص --</option>
                        @foreach($specializations as $specialization)
                            <option value="{{ $specialization->name }}" data-department="{{ $specialization->department->name }}" {{ old('specialization', $course->specialization ?? '') === $specialization->name ? 'selected' : '' }}>
                                {{ $specialization->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="instructor_id">المحاضر *</label>
                    <select id="instructor_id" name="instructor_id" class="form-control" required>
                        <option value="">-- اختر محاضر --</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ old('instructor_id', $course->instructor_id ?? '') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">حالة المقرر *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" {{ old('status', $course->status ?? 'active') === 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ old('status', $course->status ?? 'active') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="credit_hours">عدد الساعات المعتمدة</label>
                    <input type="number" id="credit_hours" name="credit_hours" class="form-control" value="{{ old('credit_hours', $course->credit_hours ?? 3) }}" min="1">
                </div>

                <div class="form-group">
                    <label for="max_students">الحد الأقصى للطلاب</label>
                    <input type="number" id="max_students" name="max_students" class="form-control" value="{{ old('max_students', $course->max_students ?? 50) }}" min="1">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">{{ isset($course) ? 'تحديث' : 'إضافة' }}</button>
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">إلغاء</a>
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

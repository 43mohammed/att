@extends('layouts.app')

@section('title', 'إدارة الأقسام والتخصصات')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>إدارة الأقسام والتخصصات</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-2" style="gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card">
            <div class="card-header">
                <span>إضافة قسم جديد</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">اسم القسم *</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">حفظ القسم</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span>إضافة تخصص جديد</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.specializations.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="department_id">القسم *</label>
                        <select id="department_id" name="department_id" class="form-control" required>
                            <option value="">اختر القسم</option>
                            @foreach($allDepartments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">اسم التخصص *</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">حفظ التخصص</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>القسم والتخصصات المسجلة</span>
        </div>
        <div class="card-body">
            @if($departments->isEmpty())
                <p>لا يوجد أقسام مسجلة بعد.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>القسم</th>
                            <th>التخصصات</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $department)
                            <tr>
                                <td>{{ $department->name }}</td>
                                <td>
                                    @if($department->specializations->isEmpty())
                                        <span>لا يوجد تخصصات لهذا القسم</span>
                                    @else
                                        <ul class="spec-list">
                                            @foreach($department->specializations as $specialization)
                                                <li>
                                                    {{ $specialization->name }}
                                                    <form action="{{ route('admin.specializations.destroy', $specialization->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا التخصص؟')">حذف</button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editDepartment({{ $department->id }}, '{{ $department->name }}')">تعديل</button>
                                    <form action="{{ route('admin.departments.destroy', $department->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟ سيتم حذف جميع التخصصات التابعة له.')">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>تعديل القسم</h2>
            <form id="editForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_name">اسم القسم *</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">تحديث القسم</button>
            </form>
        </div>
    </div>
</div>

<style>
    .form-group { margin-bottom: 1.25rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
    .form-control { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.35rem; }
    .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 0.35rem; color: white; background: #007bff; cursor: pointer; }
    .btn:hover { opacity: 0.92; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .btn-warning { background: #ffc107; color: #212529; }
    .btn-danger { background: #dc3545; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { border: 1px solid #e2e8f0; padding: 0.75rem; text-align: left; }
    .spec-list { margin: 0; padding-left: 1.25rem; }
    .spec-list li { display: flex; justify-content: space-between; align-items: center; }
    .modal { position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
    .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
</style>

<script>
    function editDepartment(id, name) {
        document.getElementById('edit_name').value = name;
        document.getElementById('editForm').action = '/admin/departments/' + id;
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('editModal')) {
            closeModal();
        }
    }
</script>
@endsection

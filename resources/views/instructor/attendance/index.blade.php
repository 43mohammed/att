@extends('layouts.app')

@section('title', 'إدارة الحضور')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>إدارة الحضور</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>الجلسات النشطة</h3>
                </div>
                <div class="card-body">
                    @forelse($activeSessions as $session)
                        <div class="session-item">
                            <div class="session-info">
                                <h4>{{ $session->course->name }}</h4>
                                <p>تاريخ: {{ $session->session_date }} | الوقت: {{ $session->start_time }}</p>
                                <p>عدد الحاضرين: <strong>{{ $session->attendanceRecords->count() }}</strong></p>
                            </div>
                            <div class="session-actions">
                                <a href="{{ route('instructor.attendance.view', $session->id) }}" class="btn btn-info">عرض التفاصيل</a>
                                <form action="{{ route('instructor.attendance.close', $session->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">إغلاق الجلسة</button>
                                </form>
                            </div>
                            <div class="session-info">
                                <p>القاعة: <strong>{{ $session->classroom_name ?? 'غير محددة' }}</strong></p>
                            </div>
                        </div>
                    @empty
                        <p>لا توجد جلسات نشطة حالياً</p>
                    @endforelse
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>الجلسات السابقة</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المقرر</th>
                                <th>التاريخ</th>
                                <th>الوقت</th>
                                <th>عدد الحاضرين</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pastSessions as $session)
                                <tr>
                                    <td>{{ $session->course->name }}</td>
                                    <td>{{ $session->session_date }}</td>
                                    <td>{{ $session->start_time }} - {{ $session->end_time }}</td>
                                    <td>{{ $session->attendanceRecords->count() }}</td>
                                    <td>
                                        <a href="{{ route('instructor.attendance.view', $session->id) }}" class="btn btn-sm btn-info">عرض</a>
                                        <a href="{{ route('instructor.attendance.edit', $session->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">لا توجد جلسات سابقة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>إنشاء جلسة حضور جديدة</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('instructor.attendance.create') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="course_id">المقرر *</label>
                            <select id="course_id" name="course_id" class="form-control" required>
                                <option value="">-- اختر مقرر --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="session_date">التاريخ *</label>
                            <input type="date" id="session_date" name="session_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="start_time">وقت البدء *</label>
                            <input type="time" id="start_time" name="start_time" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="classroom_name">اختر القاعة / المكان</label>
                            <select id="classroom_name" name="classroom_name" class="form-control">
                                <option value="">-- اختر قاعة معروفة --</option>
                                @foreach(config('classrooms') as $name => $coordinates)
                                    <option value="{{ $name }}" {{ old('classroom_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">يمكنك اختيار قاعة جاهزة لتعبئة الموقع تلقائياً.</small>
                        </div>

                        <div class="form-group">
                            <label for="custom_classroom_name">اسم القاعة المخصص</label>
                            <input type="text" id="custom_classroom_name" name="custom_classroom_name" class="form-control" value="{{ old('custom_classroom_name') }}" placeholder="مثال: معمل الحاسب 3">
                            <small class="form-text text-muted">اكتب اسم القاعة إذا كان المكان غير موجود في القائمة.</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="gps_required" value="1" {{ old('gps_required') ? 'checked' : '' }}>
                                تفعيل التحقق من GPS
                            </label>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="nfc_active" value="1" {{ old('nfc_active') ? 'checked' : '' }}>
                                تفعيل NFC
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">إنشاء جلسة</button>
                    </form>
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>الإحصائيات</h3>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <span>إجمالي الجلسات:</span>
                        <strong>{{ $totalSessions }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>الجلسات النشطة:</span>
                        <strong>{{ $activeSessions->count() }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>إجمالي السجلات:</span>
                        <strong>{{ $totalRecords }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        margin-bottom: 2rem;
    }

    .row {
        display: flex;
        gap: 2rem;
    }

    .col-md-8 { flex: 2; }
    .col-md-4 { flex: 1; }

    .session-item {
        padding: 1rem;
        border: 1px solid #ddd;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .session-info h4 {
        margin: 0 0 0.5rem 0;
    }

    .session-info p {
        margin: 0.25rem 0;
        font-size: 0.875rem;
        color: #666;
    }

    .session-actions {
        display: flex;
        gap: 0.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.25rem;
        font-weight: bold;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 0.25rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary { background-color: #007bff; color: white; }
    .btn-info { background-color: #17a2b8; color: white; }
    .btn-warning { background-color: #ffc107; color: black; }
    .btn-danger { background-color: #dc3545; color: white; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .btn-block { width: 100%; }

    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 0.75rem;
        text-align: right;
        border-bottom: 1px solid #ddd;
    }

    .table th {
        background-color: #f5f5f5;
        font-weight: bold;
    }
</style>
@endsection

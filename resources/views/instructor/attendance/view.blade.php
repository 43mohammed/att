@extends('layouts.app')

@section('title', 'تفاصيل الحضور')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>تفاصيل الحضور - {{ $session->course->name }}</h1>
        <a href="{{ route('instructor.attendance.index') }}" class="btn btn-secondary">← رجوع</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>معلومات الجلسة</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span>المقرر:</span>
                            <strong>{{ $session->course->name }}</strong>
                        </div>
                        <div class="info-item">
                            <span>التاريخ:</span>
                            <strong>{{ $session->session_date }}</strong>
                        </div>
                        <div class="info-item">
                            <span>الوقت:</span>
                            <strong>{{ $session->start_time }} - {{ $session->end_time ?? 'جارية' }}</strong>
                        </div>
                        <div class="info-item">
                            <span>الحالة:</span>
                            <strong class="badge badge-{{ $session->status === 'active' ? 'success' : 'danger' }}">
                                {{ $session->status === 'active' ? 'نشطة' : 'مغلقة' }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>قائمة الحضور ({{ $records->count() }} حاضر)</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الطالب</th>
                                <th>رقم الطالب</th>
                                <th>وقت التسجيل</th>
                                <th>طريقة التحقق</th>
                                <th>الموقع</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $record->student->name }}</td>
                                    <td>{{ $record->student->id_number ?? '-' }}</td>
                                    <td>{{ $record->marked_at->format('H:i:s') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $record->verification_method === 'qrcode' ? 'info' : ($record->verification_method === 'nfc' ? 'warning' : 'success') }}">
                                            {{ $record->verification_method }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($record->latitude && $record->longitude)
                                            <a href="https://maps.google.com/?q={{ $record->latitude }},{{ $record->longitude }}" target="_blank" class="btn btn-sm btn-info">
                                                عرض على الخريطة
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('instructor.attendance.delete', $record->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">لا يوجد حاضرون</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>الطلاب الغائبون</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الطالب</th>
                                <th>رقم الطالب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absentStudents as $student)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->id_number ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('instructor.attendance.add-manual', $session->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                            <button type="submit" class="btn btn-sm btn-success">تسجيل حضور</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">جميع الطلاب حاضرون</td>
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
                    <h3>الإحصائيات</h3>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <span>إجمالي الطلاب:</span>
                        <strong>{{ $totalStudents }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>الحاضرون:</span>
                        <strong class="text-success">{{ $records->count() }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>الغائبون:</span>
                        <strong class="text-danger">{{ $absentStudents->count() }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>نسبة الحضور:</span>
                        <strong>{{ $totalStudents > 0 ? round(($records->count() / $totalStudents) * 100, 2) : 0 }}%</strong>
                    </div>
                </div>
            </div>

            @if($session->status === 'active')
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>QR Code</h3>
                    </div>
                    <div class="card-body">
                        <div id="qrcode" style="text-align: center;"></div>
                        <p style="margin-top: 1rem; text-align: center; font-size: 0.875rem; color: #666;">
                            اطلب من الطلاب مسح هذا الرمز
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .row {
        display: flex;
        gap: 2rem;
    }

    .col-md-8 { flex: 2; }
    .col-md-4 { flex: 1; }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        border-bottom: 1px solid #eee;
    }

    .info-item span {
        color: #666;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .badge-success { background-color: #28a745; color: white; }
    .badge-danger { background-color: #dc3545; color: white; }
    .badge-info { background-color: #17a2b8; color: white; }
    .badge-warning { background-color: #ffc107; color: black; }

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

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary { background-color: #6c757d; color: white; }
    .btn-success { background-color: #28a745; color: white; }
    .btn-danger { background-color: #dc3545; color: white; }
    .btn-info { background-color: #17a2b8; color: white; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }

    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .text-success { color: #28a745; }
    .text-danger { color: #dc3545; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const qrcode = new QRCode(document.getElementById('qrcode'), {
        text: "{{ route('attendance.scan', ['session' => $session->id, 'token' => $session->qr_code_token]) }}",
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
</script>
@endsection

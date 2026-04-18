@extends('layouts.app')

@section('title', 'تفاصيل الحضور - ' . $course->name)

@section('content')
<div class="container">
    <div class="page-header">
        <h1>تفاصيل الحضور - {{ $course->name }}</h1>
        <a href="{{ route('student.attendance.index') }}" class="btn btn-secondary">← رجوع</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>معلومات المقرر</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span>اسم المقرر:</span>
                            <strong>{{ $course->name }}</strong>
                        </div>
                        <div class="info-item">
                            <span>الكود:</span>
                            <strong>{{ $course->code }}</strong>
                        </div>
                        <div class="info-item">
                            <span>المحاضر:</span>
                            <strong>{{ $course->instructor->name }}</strong>
                        </div>
                        <div class="info-item">
                            <span>القسم:</span>
                            <strong>{{ $course->department->name }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>سجل الحضور ({{ $records->count() }} جلسة)</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>الوقت</th>
                                <th>الحالة</th>
                                <th>طريقة التحقق</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $record->attendanceSession->session_date }}</td>
                                    <td>{{ $record->marked_at->format('H:i:s') }}</td>
                                    <td>
                                        <span class="badge badge-success">حاضر</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $record->verification_method === 'qrcode' ? 'info' : ($record->verification_method === 'nfc' ? 'warning' : 'success') }}">
                                            {{ $record->verification_method }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">لا توجد سجلات حضور</td>
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
                        <span>إجمالي الجلسات:</span>
                        <strong>{{ $totalSessions }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>الحضور:</span>
                        <strong class="text-success">{{ $records->count() }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>الغياب:</span>
                        <strong class="text-danger">{{ $totalSessions - $records->count() }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>نسبة الحضور:</span>
                        <strong>{{ $totalSessions > 0 ? round(($records->count() / $totalSessions) * 100, 2) : 0 }}%</strong>
                    </div>
                </div>
            </div>

            @if($warningLevel)
                <div class="alert alert-warning" style="margin-top: 2rem;">
                    ⚠️ <strong>تحذير:</strong> نسبة الحضور منخفضة!
                </div>
            @endif

            @if($criticalLevel)
                <div class="alert alert-danger" style="margin-top: 2rem;">
                    🚨 <strong>تنبيه حرج:</strong> تجاوزت حد الغياب المسموح!
                </div>
            @endif

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>الإشعارات</h3>
                </div>
                <div class="card-body">
                    @forelse($notifications as $notification)
                        <div class="notification-item">
                            <p>{{ $notification->message }}</p>
                            <small>{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    @empty
                        <p>لا توجد إشعارات</p>
                    @endforelse
                </div>
            </div>
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

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .badge-success { background-color: #28a745; color: white; }
    .badge-info { background-color: #17a2b8; color: white; }
    .badge-warning { background-color: #ffc107; color: black; }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary { background-color: #6c757d; color: white; }

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

    .alert {
        padding: 1rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }

    .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    .notification-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .notification-item p {
        margin: 0 0 0.25rem 0;
    }

    .notification-item small {
        color: #999;
    }
</style>
@endsection

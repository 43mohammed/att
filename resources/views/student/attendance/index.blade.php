@extends('layouts.app')

@section('title', 'سجل الحضور')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>سجل الحضور الشخصي</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>المقررات المسجلة</h3>
                </div>
                <div class="card-body">
                    @forelse($enrollments as $enrollment)
                        <div class="course-item">
                            <div class="course-info">
                                <h4>{{ $enrollment->course->name }}</h4>
                                <p>المحاضر: {{ $enrollment->course->instructor->name }}</p>
                                <p>الكود: {{ $enrollment->course->code }}</p>
                            </div>
                            <div class="course-stats">
                                <div class="stat">
                                    <span>الحضور:</span>
                                    <strong class="text-success">{{ $enrollment->attendance_count }}</strong>
                                </div>
                                <div class="stat">
                                    <span>الغياب:</span>
                                    <strong class="text-danger">{{ $enrollment->absence_count }}</strong>
                                </div>
                                <div class="stat">
                                    <span>النسبة:</span>
                                    <strong>{{ $enrollment->attendance_percentage }}%</strong>
                                </div>
                            </div>
                            <a href="{{ route('student.courses.show', $enrollment->course->id) }}" class="btn btn-info">عرض التفاصيل</a>
                        </div>
                    @empty
                        <p>لم تسجل في أي مقرر</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>مسح QR Code</h3>
                </div>
                <div class="card-body">
                    <button id="startQR" class="btn btn-primary btn-block">🎥 ابدأ مسح QR Code</button>
                    <video id="video" autoplay playsinline muted style="width: 100%; margin-top: 1rem; height: auto; display: none; object-fit: cover; background: #000;"></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>مسح NFC</h3>
                </div>
                <div class="card-body">
                    <button id="startNFC" class="btn btn-warning btn-block">📱 ابدأ مسح NFC</button>
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>الإحصائيات العامة</h3>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <span>إجمالي الجلسات:</span>
                        <strong>{{ $totalSessions }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>الحضور:</span>
                        <strong class="text-success">{{ $totalAttended }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>الغياب:</span>
                        <strong class="text-danger">{{ $totalAbsent }}</strong>
                    </div>
                    <div class="stat-item">
                        <span>نسبة الحضور:</span>
                        <strong>{{ $overallPercentage }}%</strong>
                    </div>
                </div>
            </div>

            @if($hasWarning)
                <div class="alert alert-warning" style="margin-top: 2rem;">
                    ⚠️ <strong>تحذير:</strong> أنت اقتربت من حد الغياب المسموح!
                </div>
            @endif

            @if($isCritical)
                <div class="alert alert-danger" style="margin-top: 2rem;">
                    🚨 <strong>تنبيه حرج:</strong> أنت تجاوزت حد الغياب المسموح!
                </div>
            @endif
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

    .course-item {
        padding: 1.5rem;
        border: 1px solid #ddd;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }

    .course-item h4 {
        margin: 0 0 0.5rem 0;
    }

    .course-item p {
        margin: 0.25rem 0;
        font-size: 0.875rem;
        color: #666;
    }

    .course-stats {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
        margin: 1rem 0;
    }

    .stat {
        text-align: center;
    }

    .stat span {
        display: block;
        font-size: 0.875rem;
        color: #666;
    }

    .stat strong {
        display: block;
        font-size: 1.25rem;
        margin-top: 0.25rem;
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
    .btn-warning { background-color: #ffc107; color: black; }
    .btn-info { background-color: #17a2b8; color: white; }
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

    .text-success { color: #28a745; }
    .text-danger { color: #dc3545; }

    .alert {
        padding: 1rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }

    .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<script>
    document.getElementById('startQR').addEventListener('click', function() {
        // تشغيل مسح QR Code
        if (window.startQRScanner) {
            window.startQRScanner();
        } else {
            alert('يرجى تحميل مكتبة QR Scanner');
        }
    });

    document.getElementById('startNFC').addEventListener('click', function() {
        // تشغيل مسح NFC
        if (window.startNFCScanning) {
            window.startNFCScanning();
        } else {
            alert('NFC غير مدعوم في هذا الجهاز');
        }
    });
</script>
@endsection

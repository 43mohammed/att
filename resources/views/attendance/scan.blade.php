@extends('layouts.app')

@section('title', 'مسح QR Code')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>تم مسح رمز QR</h1>
        <a href="{{ route('instructor.attendance.index') }}" class="btn btn-secondary">العودة</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>تفاصيل الجلسة</h3>
        </div>
        <div class="card-body">
            <p><strong>المقرر:</strong> {{ $attendanceSession->course->name }}</p>
            <p><strong>التاريخ:</strong> {{ $attendanceSession->session_date }}</p>
            <p><strong>الوقت:</strong> {{ $attendanceSession->start_time }}</p>
            <p><strong>الحالة:</strong> {{ $attendanceSession->status }}</p>
            @if($attendanceSession->qr_code_token)
                <p><strong>رمز الجلسة:</strong> {{ $attendanceSession->qr_code_token }}</p>
            @else
                <p><strong>رمز الجلسة:</strong> هذا رمز تم إنشاؤه مؤقتاً بدون جلسة مسجلة بعد</p>
            @endif
            <p>يمكن للطالب أو النظام استخدام هذه الصفحة للتأكيد أو تسجيل الحضور.</p>
        </div>
    </div>
</div>
@endsection

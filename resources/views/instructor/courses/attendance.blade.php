@extends('layouts.app')

@section('title', 'حضور المقرر - ' . $course->name)

@section('content')
<div class="container">
    <div class="page-header">
        <h1>حضور المقرر: {{ $course->name }}</h1>
        <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary">العودة إلى المقررات</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>جلسات المقرر</h3>
                </div>
                <div class="card-body">
                    @if($course->sessions->isEmpty())
                        <p>لا توجد جلسات لهذا المقرر بعد.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>الوقت</th>
                                    <th>القاعة</th>
                                    <th>الحضور</th>
                                    <th>إدارة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($course->sessions as $session)
                                    <tr>
                                        <td>{{ $session->session_date ?? $session->date ?? 'غير محدد' }}</td>
                                        <td>{{ $session->start_time ?? 'غير محدد' }} - {{ $session->end_time ?? 'غير محدد' }}</td>
                                        <td>{{ $session->classroom_name ?? 'غير محدد' }}</td>
                                        <td>{{ $session->attendanceRecords->count() }}</td>
                                        <td>
                                            <a href="{{ route('instructor.attendance.view', $session->id) }}" class="btn btn-sm btn-info">عرض التفاصيل</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>تفاصيل المقرر</h3>
                </div>
                <div class="card-body">
                    <p><strong>اسم المقرر:</strong> {{ $course->name }}</p>
                    <p><strong>عدد الطلاب المسجلين:</strong> {{ $course->enrollments->count() }}</p>
                    <p><strong>عدد الجلسات:</strong> {{ $course->sessions->count() }}</p>
                    <p><strong>المعلم:</strong> {{ $course->instructor->name ?? 'غير محدد' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
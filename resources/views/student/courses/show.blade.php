@extends('layouts.app')

@section('title', 'تفاصيل المقرر - ' . $enrollment->course->name)

@section('content')
<div class="container">
    <div class="page-header">
        <h1>{{ $enrollment->course->name }}</h1>
        <a href="{{ route('student.courses.index') }}" class="btn btn-secondary">العودة للمقررات</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>معلومات المقرر</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>اسم المقرر:</strong> {{ $enrollment->course->name }}</p>
                    <p><strong>المعلم:</strong> {{ $enrollment->course->instructor_name }}</p>
                    <p><strong>المستوى:</strong> {{ $enrollment->course->level }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>القسم:</strong> {{ $enrollment->course->department }}</p>
                    <p><strong>التخصص:</strong> {{ $enrollment->course->specialization }}</p>
                    <p><strong>الشعبة:</strong> {{ $enrollment->course->section }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>جلسات الحضور</h5>
        </div>
        <div class="card-body">
            @if($enrollment->course->sessions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>الوقت</th>
                                <th>القاعة</th>
                                <th>حالة الحضور</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollment->course->sessions as $session)
                                <tr>
                                    <td>{{ $session->date }}</td>
                                    <td>{{ $session->start_time }} - {{ $session->end_time }}</td>
                                    <td>{{ $session->classroom_name }}</td>
                                    <td>
                                        @php
                                            $record = $enrollment->attendanceRecords->where('session_id', $session->id)->first();
                                        @endphp
                                        @if($record)
                                            @if($record->status == 'present')
                                                <span class="badge bg-success">حاضر</span>
                                            @elseif($record->status == 'absent')
                                                <span class="badge bg-danger">غائب</span>
                                            @else
                                                <span class="badge bg-warning">{{ $record->status }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">لم يتم التسجيل</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">لا توجد جلسات حضور لهذا المقرر</p>
            @endif
        </div>
    </div>
</div>
@endsection
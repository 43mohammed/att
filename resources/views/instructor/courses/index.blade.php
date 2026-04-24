@extends('layouts.app')

@section('title', 'قائمة المقررات')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>المقررات الخاصة بك</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($courses->isEmpty())
                <p>لم يتم تعيين أي مقررات بعد.</p>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>اسم المقرر</th>
                            <th>عدد الطلاب</th>
                            <th>عدد الجلسات</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                            <tr>
                                <td>{{ $course->name }}</td>
                                <td>{{ $course->enrollments->count() }}</td>
                                <td>{{ $course->sessions->count() }}</td>
                                <td>
                                    <a href="{{ route('instructor.courses.attendance', $course->id) }}" class="btn btn-sm btn-primary">عرض الحضور</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
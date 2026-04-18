@extends('layouts.app')

@section('title', 'عرض تفاصيل المقرر')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>تفاصيل المقرر</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">اسم المقرر</dt>
                <dd class="col-sm-9">{{ $course->name }}</dd>

                <dt class="col-sm-3">كود المقرر</dt>
                <dd class="col-sm-9">{{ $course->code }}</dd>

                <dt class="col-sm-3">القسم</dt>
                <dd class="col-sm-9">{{ $course->department }}</dd>

                <dt class="col-sm-3">المحاضر</dt>
                <dd class="col-sm-9">{{ $course->instructor->name ?? 'غير محدد' }}</dd>

                <dt class="col-sm-3">عدد الساعات المعتمدة</dt>
                <dd class="col-sm-9">{{ $course->credit_hours }}</dd>

                <dt class="col-sm-3">الحد الأقصى للطلاب</dt>
                <dd class="col-sm-9">{{ $course->capacity }}</dd>

                <dt class="col-sm-3">الوصف</dt>
                <dd class="col-sm-9">{{ $course->description ?? 'لا يوجد وصف.' }}</dd>
            </dl>

            <div class="form-actions" style="margin-top: 1.5rem;">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">العودة</a>
                <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-primary">تعديل</a>
            </div>
        </div>
    </div>
</div>

<style>
    .form-actions {
        display: flex;
        gap: 1rem;
    }
</style>
@endsection
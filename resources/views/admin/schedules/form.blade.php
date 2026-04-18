@extends('layouts.app')

@section('title', isset($schedule) ? 'تعديل جدول' : 'إضافة جدول جديد')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>{{ isset($schedule) ? 'تعديل جدول' : 'إضافة جدول جديد' }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $scheduleData = $schedule ?? null;
    @endphp

    <div class="card">
        <div class="card-body">
            <form action="{{ isset($scheduleData) ? route('admin.schedules.update', $scheduleData->id) : route('admin.schedules.store') }}" method="POST">
                @csrf
                @if(isset($scheduleData))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="course_id">المقرر *</label>
                    <select id="course_id" name="course_id" class="form-control" required>
                        <option value="">-- اختر مقرر --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id', optional($scheduleData)->course_id ?? '') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }} ({{ $course->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="instructor_id">المحاضر *</label>
                    <select id="instructor_id" name="instructor_id" class="form-control" required>
                        <option value="">-- اختر محاضر --</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ old('instructor_id', optional($scheduleData)->instructor_id ?? '') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="session_date">التاريخ *</label>
                    <input type="date" id="session_date" name="session_date" class="form-control" value="{{ old('session_date', optional($scheduleData)->session_date?->format('Y-m-d') ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="start_time">وقت البداية *</label>
                    <input type="time" id="start_time" name="start_time" class="form-control" value="{{ old('start_time', optional($scheduleData)->start_time?->format('H:i') ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="end_time">وقت الانتهاء *</label>
                    <input type="time" id="end_time" name="end_time" class="form-control" value="{{ old('end_time', optional($scheduleData)->end_time?->format('H:i') ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="classroom_latitude">خط العرض</label>
                    <input type="text" id="classroom_latitude" name="classroom_latitude" class="form-control" value="{{ old('classroom_latitude', optional($scheduleData)->classroom_latitude ?? '') }}" placeholder="مثال: 24.7136">
                </div>

                <div class="form-group">
                    <label for="classroom_longitude">خط الطول</label>
                    <input type="text" id="classroom_longitude" name="classroom_longitude" class="form-control" value="{{ old('classroom_longitude', optional($scheduleData)->classroom_longitude ?? '') }}" placeholder="مثال: 46.6753">
                </div>

                <div class="form-group">
                    <label for="status">الحالة *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" {{ old('status', optional($scheduleData)->status ?? '') === 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="closed" {{ old('status', optional($scheduleData)->status ?? '') === 'closed' ? 'selected' : '' }}>مغلق</option>
                        <option value="cancelled" {{ old('status', optional($scheduleData)->status ?? '') === 'cancelled' ? 'selected' : '' }}>ملغى</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">{{ isset($schedule) ? 'تحديث' : 'إضافة' }}</button>
                    <a href="{{ route('admin.schedules') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 0.25rem;
        font-size: 1rem;
    }

    .form-actions {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
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
    .btn-secondary { background-color: #6c757d; color: white; }
</style>
@endsection
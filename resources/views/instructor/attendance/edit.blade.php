@extends('layouts.app')

@section('title', 'تعديل جلسة الحضور')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>تعديل جلسة | {{ $attendanceSession->course->name }}</h1>
        <a href="{{ route('instructor.attendance.view', $attendanceSession->id) }}" class="btn btn-secondary">رجوع</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>تحديث بيانات الجلسة</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('instructor.attendance.update', $attendanceSession->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="course_id">المقرر *</label>
                    <select id="course_id" name="course_id" class="form-control" required>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ $attendanceSession->course_id == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="session_date">التاريخ *</label>
                    <input type="date" id="session_date" name="session_date" value="{{ $attendanceSession->session_date->format('Y-m-d') }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="start_time">وقت البدء *</label>
                    <input type="time" id="start_time" name="start_time" value="{{ $attendanceSession->start_time->format('H:i') }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="end_time">وقت الانتهاء</label>
                    <input type="time" id="end_time" name="end_time" value="{{ optional($attendanceSession->end_time)->format('H:i') }}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="status">الحالة *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" {{ $attendanceSession->status === 'active' ? 'selected' : '' }}>نشطة</option>
                        <option value="closed" {{ $attendanceSession->status === 'closed' ? 'selected' : '' }}>مغلقة</option>
                        <option value="cancelled" {{ $attendanceSession->status === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="gps_required" value="1" {{ $attendanceSession->gps_required ? 'checked' : '' }}>
                        تفعيل التحقق من GPS
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="nfc_active" value="1" {{ $attendanceSession->nfc_active ? 'checked' : '' }}>
                        تفعيل NFC
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            </form>
        </div>
    </div>
</div>
@endsection

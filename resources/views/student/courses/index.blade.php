@extends('layouts.app')

@section('title', 'المقررات المتاحة')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>المقررات المتاحة للتسجيل</h1>
    </div>

    <!-- رسائل النجاح والخطأ -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- فلاتر البحث -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>فلاتر البحث</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('student.courses.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="department">القسم</label>
                        <select name="department" id="department" class="form-control">
                            <option value="">جميع الأقسام</option>
                            @foreach(\App\Models\Department::all() as $dept)
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="specialization">التخصص</label>
                        <select name="specialization" id="specialization" class="form-control">
                            <option value="">جميع التخصصات</option>
                            @foreach(\App\Models\Specialization::all() as $spec)
                                <option value="{{ $spec->id }}" {{ request('specialization') == $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="level">المستوى</label>
                        <select name="level" id="level" class="form-control">
                            <option value="">جميع المستويات</option>
                            @for($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ request('level') == $i ? 'selected' : '' }}>
                                    المستوى {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="section">الشعبة</label>
                        <input type="text" name="section" id="section" class="form-control" value="{{ request('section') }}" placeholder="الشعبة">
                    </div>
                    <div class="col-md-2">
                        <label for="instructor">المعلم</label>
                        <input type="text" name="instructor" id="instructor" class="form-control" value="{{ request('instructor') }}" placeholder="اسم المعلم">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">بحث</button>
                        <a href="{{ route('student.courses.index') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- قائمة المقررات -->
    <div class="card">
        <div class="card-header">
            <h3>المقررات المتاحة ({{ $courses->count() }})</h3>
        </div>
        <div class="card-body">
            @forelse($courses as $course)
                <div class="course-item mb-3 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-8">
                            <h4>{{ $course->name }}</h4>
                            <p><strong>الكود:</strong> {{ $course->code }}</p>
                            <p><strong>المعلم:</strong> {{ $course->instructor->name }}</p>
                            <p><strong>القسم:</strong> {{ $course->department }}</p>
                            @if($course->specialization)
                                <p><strong>التخصص:</strong> {{ $course->specialization }}</p>
                            @endif
                            @if($course->level)
                                <p><strong>المستوى:</strong> {{ $course->level }}</p>
                            @endif
                            @if($course->section)
                                <p><strong>الشعبة:</strong> {{ $course->section }}</p>
                            @endif
                            <p><strong>الساعات المعتمدة:</strong> {{ $course->credit_hours }}</p>
                            <p><strong>السعة:</strong> {{ $course->enrollments->count() }}/{{ $course->capacity }}</p>
                            @if($course->description)
                                <p><strong>الوصف:</strong> {{ $course->description }}</p>
                            @endif
                        </div>
                        <div class="col-md-4 text-center">
                            @php
                                $isEnrolled = $course->enrollments->where('student_id', session('user_id'))->isNotEmpty();
                            @endphp
                            @if($isEnrolled)
                                <span class="badge badge-success">مسجل</span>
                                <br>
                                <form method="POST" action="{{ route('student.courses.unenroll', $course->id) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm mt-2" onclick="return confirm('هل أنت متأكد من إلغاء التسجيل؟')">إلغاء التسجيل</button>
                                </form>
                            @else
                                @if($course->enrollments->count() < $course->capacity)
                                    <form method="POST" action="{{ route('student.courses.enroll', $course->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success">تسجيل</button>
                                    </form>
                                @else
                                    <span class="badge badge-warning">مكتمل</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center">لا توجد مقررات متاحة تطابق معايير البحث</p>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($courses->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $courses->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<script>
document.getElementById('department').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>
@endsection
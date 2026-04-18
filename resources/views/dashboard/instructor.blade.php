@extends('layouts.app')

@section('title', 'لوحة تحكم المدرس')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div style="margin-bottom: var(--spacing-2xl);">
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: var(--spacing-sm);">
            لوحة تحكم المدرس
        </h1>
        <p style="color: var(--text-light); font-size: 0.95rem;">
            إدارة الحضور والجلسات الدراسية
        </p>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-3">
        <div class="stat-card">
            <h3>إجمالي الجلسات</h3>
            <div class="value">{{ $stats['total_sessions'] ?? 0 }}</div>
            <div class="subtitle">جلسة نشطة</div>
        </div>
        <div class="stat-card info">
            <h3>سجلات الحضور</h3>
            <div class="value">{{ $stats['total_records'] ?? 0 }}</div>
            <div class="subtitle">تسجيل</div>
        </div>
        <div class="stat-card success">
            <h3>المقررات</h3>
            <div class="value">{{ $stats['total_courses'] ?? 0 }}</div>
            <div class="subtitle">مقرر</div>
        </div>
    </div>

    <!-- Courses Section -->
    <div class="card">
        <div class="card-header">
            <span>📚 مقرراتي</span>
            <button class="btn btn-sm btn-primary" onclick="showModal('addCourseModal')">+ إضافة جلسة</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>اسم المقرر</th>
                            <th>الكود</th>
                            <th>الطلاب</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                <td data-label="اسم المقرر">{{ $course->name }}</td>
                                <td data-label="الكود">{{ $course->code }}</td>
                                <td data-label="الطلاب">{{ $course->enrollments_count }}</td>
                                <td data-label="الحالة">
                                    <span class="badge badge-{{ $course->enrollments_count > 0 ? 'success' : 'warning' }}">
                                        {{ $course->enrollments_count > 0 ? 'نشط' : 'بلا طلاب' }}
                                    </span>
                                </td>
                                <td data-label="الإجراءات">
                                    <a href="/instructor/courses/{{ $course->id }}/attendance" class="btn btn-sm btn-secondary">تفاصيل</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center">لا توجد مقررات حالياً</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-2">
        <!-- Create Session -->
        <div class="card">
            <div class="card-header">
                <span>➕ إنشاء جلسة حضور</span>
            </div>
            <div class="card-body">
                <form action="{{ route('instructor.attendance.create') }}" method="POST" style="display: grid; gap: var(--spacing-md);" id="create-session-form">
                    @csrf
                    <div class="form-group">
                        <label>اختر المقرر</label>
                        <select name="course_id" id="course_id" class="form-control" required>
                            <option value="">-- اختر مقرر --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>التاريخ</label>
                        <input type="date" name="session_date" id="session_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>وقت البدء</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        إنشاء جلسة
                    </button>
                </form>
            </div>
        </div>

        <!-- Generate QR Code -->
        <div class="card">
            <div class="card-header">
                <span>🔗 إنشاء رمز QR</span>
            </div>
            <div class="card-body" style="text-align: center;">
                <p style="color: var(--text-light); margin-bottom: var(--spacing-lg); font-size: 0.9rem;">
                    أنشئ رمز QR للطلاب لتسجيل الحضور
                </p>
                <button type="button" class="btn btn-success btn-lg btn-block" onclick="generateQRCode()">
                    🔗 إنشاء رمز QR
                </button>
                <div id="qr-code-display" style="margin-top: var(--spacing-lg); display: none;">
                    <div id="qr-code" style="background: var(--white); padding: var(--spacing-lg); border-radius: var(--radius-lg); border: 2px solid var(--border); display: inline-block;"></div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="printQRCode()" style="margin-top: var(--spacing-md);">
                        🖨️ طباعة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions -->
    <div class="card">
        <div class="card-header">
            <span>⏱️ الجلسات النشطة</span>
        </div>
        <div class="card-body" style="display: grid; gap: var(--spacing-md);">
            @forelse($activeSessions as $session)
                <div style="background: var(--light); padding: var(--spacing-lg); border-radius: var(--radius-lg); border-right: 4px solid var(--primary);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md);">
                        <div>
                            <h3 style="font-weight: 700; color: var(--dark); margin-bottom: var(--spacing-xs);">{{ $session->course->name }}</h3>
                            <p style="color: var(--text-light); font-size: 0.9rem;">{{ $session->session_date }} {{ $session->start_time }}</p>
                        </div>
                        <span class="badge badge-success">نشطة</span>
                    </div>
                    <div style="display: flex; gap: var(--spacing-md);">
                        <a href="/instructor/attendance/{{ $session->id }}" class="btn btn-sm btn-primary">عرض</a>
                        <button type="button" class="btn btn-sm btn-danger" onclick="closeSession()">إغلاق</button>
                    </div>
                </div>
            @empty
                <p>لا توجد جلسات نشطة حالياً</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Records -->
    <div class="card">
        <div class="card-header">
            <span>📊 آخر سجلات الحضور</span>
            <a href="/instructor/attendance" class="btn btn-sm btn-secondary">عرض الكل</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>المقرر</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRecords as $record)
                            <tr>
                                <td data-label="الطالب">{{ $record->student->name }}</td>
                                <td data-label="المقرر">{{ $record->course->name }}</td>
                                <td data-label="التاريخ">{{ optional($record->marked_at)->format('Y-m-d H:i') }}</td>
                                <td data-label="الحالة"><span class="badge badge-{{ $record->status === 'present' ? 'success' : 'danger' }}">{{ $record->status === 'present' ? 'حاضر' : 'غائب' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center">لا توجد سجلات حضور حديثة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
function generateQRCode() {
    const courseId = document.getElementById('course_id').value;
    const courseText = document.getElementById('course_id').selectedOptions[0]?.text || '';
    const sessionDate = document.getElementById('session_date').value;
    const startTime = document.getElementById('start_time').value;

    if (!courseId || !sessionDate || !startTime) {
        alert('يرجى اختيار المقرر وتحديد التاريخ ووقت البدء لإنشاء رمز QR');
        return;
    }

    const qrText = `${window.location.origin}/attendance/scan?course_id=${courseId}&date=${sessionDate}&time=${startTime}`;
    const qrContainer = document.getElementById('qr-code');
    qrContainer.innerHTML = '';
    new QRCode(qrContainer, {
        text: qrText,
        width: 220,
        height: 220,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    document.getElementById('qr-code-display').style.display = 'block';
}

function printQRCode() {
    const originalTitle = document.title;
    const qrDisplay = document.getElementById('qr-code-display').innerHTML;
    const printWindow = window.open('', '', 'width=600,height=600');
    printWindow.document.write('<html><head><title>Print QR Code</title></head><body>' + qrDisplay + '</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

function closeSession() {
    if (confirm('هل تريد إغلاق هذه الجلسة؟')) {
        alert('تم إغلاق الجلسة بنجاح');
    }
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}
</script>
@endsection

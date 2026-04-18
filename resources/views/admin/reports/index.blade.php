@extends('layouts.app')

@section('title', 'التقارير والإحصائيات')

@section('content')
<div class="container">
    <div class="grid">
        <div class="stat-card">
            <h3>إجمالي التقارير</h3>
            <div class="value">{{ $totalReports }}</div>
        </div>
        <div class="stat-card" style="border-top-color: var(--info);">
            <h3>تقارير يومية</h3>
            <div class="value">{{ $dailyReports }}</div>
        </div>
        <div class="stat-card" style="border-top-color: var(--warning);">
            <h3>تقارير أسبوعية</h3>
            <div class="value">{{ $weeklyReports }}</div>
        </div>
        <div class="stat-card" style="border-top-color: var(--success);">
            <h3>تقارير شهرية</h3>
            <div class="value">{{ $monthlyReports }}</div>
        </div>
    </div>

    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 300px;">
            <div class="card">
                <div class="card-header">
                    <span>📋 قائمة التقارير المنشأة</span>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>النوع</th>
                                <th>المقرر</th>
                                <th>الفترة</th>
                                <th>الصيغة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>
                                        <span class="badge {{ $report->report_type === 'daily' ? 'badge-primary' : ($report->report_type === 'weekly' ? 'badge-warning' : 'badge-success') }}">
                                            {{ $report->report_type === 'daily' ? 'يومي' : ($report->report_type === 'weekly' ? 'أسبوعي' : 'شهري') }}
                                        </span>
                                    </td>
                                    <td>{{ $report->course->name ?? 'عام' }}</td>
                                    <td>{{ $report->start_date }}</td>
                                    <td>{{ strtoupper($report->file_format) }}</td>
                                    <td>
                                        <a href="{{ route('admin.reports.view', $report) }}" class="btn btn-secondary btn-sm">عرض</a>
                                        <a href="{{ route('admin.reports.download', $report) }}" class="btn btn-success btn-sm">تحميل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center;">لا توجد تقارير منشأة حالياً</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div style="margin-top: 1rem;">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 1; min-width: 300px;">
            <div class="card">
                <div class="card-header">
                    <span>⚙️ إنشاء تقرير جديد</span>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger" style="margin-bottom: 1rem;">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('admin.reports.generate') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>نوع التقرير</label>
                            <select name="report_type" class="form-control" required>
                                <option value="daily">تقرير يومي</option>
                                <option value="weekly">تقرير أسبوعي</option>
                                <option value="monthly">تقرير شهري</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>المقرر الدراسي</label>
                            <select name="course_id" class="form-control" required>
                                <option value="">-- اختر مقرر --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>تاريخ البدء</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>تاريخ الانتهاء</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>صيغة الملف</label>
                            <select name="format" class="form-control">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel (XLSX)</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">إنشاء التقرير الآن</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

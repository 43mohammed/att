@extends('layouts.app')

@section('title', 'عرض التقرير')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <span>📄 تفاصيل التقرير</span>
        </div>
        <div class="card-body">
            <table class="table">
                <tbody>
                    <tr>
                        <th>رقم التقرير</th>
                        <td>{{ $report->id }}</td>
                    </tr>
                    <tr>
                        <th>نوع التقرير</th>
                        <td>{{ $report->report_type === 'daily' ? 'يومي' : ($report->report_type === 'weekly' ? 'أسبوعي' : 'شهري') }}</td>
                    </tr>
                    <tr>
                        <th>المقرر</th>
                        <td>{{ $report->course->name ?? 'عام' }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ البداية</th>
                        <td>{{ $report->start_date?->format('Y-m-d') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ النهاية</th>
                        <td>{{ $report->end_date?->format('Y-m-d') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>صيغة الملف</th>
                        <td>{{ strtoupper($report->file_format ?? 'غير محدد') }}</td>
                    </tr>
                    <tr>
                        <th>مُنشئ التقرير</th>
                        <td>{{ $report->createdBy->name ?? 'نظام' }}</td>
                    </tr>
                    <tr>
                        <th>عدد الطلاب</th>
                        <td>{{ $report->total_students ?? '0' }}</td>
                    </tr>
                    <tr>
                        <th>عدد الجلسات</th>
                        <td>{{ $report->total_sessions ?? '0' }}</td>
                    </tr>
                    <tr>
                        <th>بيانات إضافية</th>
                        <td><pre>{{ $report->data }}</pre></td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-3" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('admin.reports.download', $report) }}" class="btn btn-success">تحميل التقرير</a>
                <a href="{{ route('admin.reports') }}" class="btn btn-secondary">العودة إلى التقارير</a>
            </div>
        </div>
    </div>
</div>
@endsection

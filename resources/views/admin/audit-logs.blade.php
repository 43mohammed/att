@extends('layouts.app')

@section('title', 'سجلات التدقيق')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <span>📋 سجلات التدقيق والعمليات</span>
            <button class="btn btn-secondary btn-sm" onclick="exportLogs()">تصدير السجلات</button>
        </div>
        
        <div style="margin-bottom: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <input type="text" placeholder="ابحث عن عملية..." class="form-control" style="flex: 1; min-width: 200px;">
            <select class="form-control" style="flex: 1; min-width: 150px;">
                <option value="">جميع الأنواع</option>
                <option value="create">إنشاء</option>
                <option value="update">تعديل</option>
                <option value="delete">حذف</option>
                <option value="login">تسجيل دخول</option>
            </select>
            <input type="date" class="form-control" style="flex: 1; min-width: 150px;">
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المستخدم</th>
                    <th>العملية</th>
                    <th>النموذج</th>
                    <th>التفاصيل</th>
                    <th>التاريخ والوقت</th>
                    <th>عنوان IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                    @php
                        $details = '-';
                        if (!empty($log->new_values)) {
                            $details = collect($log->new_values)->map(function ($value, $key) {
                                return "{$key}: {$value}";
                            })->implode(', ');
                        } elseif (!empty($log->old_values)) {
                            $details = collect($log->old_values)->map(function ($value, $key) {
                                return "{$key}: {$value}";
                            })->implode(', ');
                        }
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration + ($auditLogs->currentPage() - 1) * $auditLogs->perPage() }}</td>
                        <td>{{ $log->user->name ?? 'غير معروف' }}</td>
                        <td><span class="badge badge-primary">{{ $log->action }}</span></td>
                        <td>{{ $log->model ?? '-' }}</td>
                        <td>{{ $details }}</td>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 1rem;">لا توجد سجلات تدقيق.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem; display: flex; justify-content: center;">
            {{ $auditLogs->links() }}
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>📊 إحصائيات التدقيق</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; padding: 0.5rem 0;">
            <div style="background: var(--light); padding: 1rem; border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 0.9rem; color: var(--text);">إجمالي العمليات</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary);">{{ number_format($totalLogs) }}</div>
            </div>
            <div style="background: var(--light); padding: 1rem; border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 0.9rem; color: var(--text);">عمليات اليوم</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--info);">{{ number_format($todayLogs) }}</div>
            </div>
            <div style="background: var(--light); padding: 1rem; border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 0.9rem; color: var(--text);">المستخدمون النشطون</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--success);">{{ number_format($activeUsers) }}</div>
            </div>
            <div style="background: var(--light); padding: 1rem; border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 0.9rem; color: var(--text);">محاولات فاشلة</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--danger);">{{ number_format($failedAttempts) }}</div>
            </div>
        </div>
    </div>
</div>

<script>
function exportLogs() {
    alert('جاري تصدير السجلات إلى ملف Excel...');
    // هنا يتم تنفيذ عملية التصدير الفعلية
}
</script>
@endsection

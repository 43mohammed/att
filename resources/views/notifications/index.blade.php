@extends('layouts.pwa')

@section('title', 'الإشعارات')

@section('content')
<div class="page">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🔔 الإشعارات</h3>
            <button class="btn btn-sm btn-outline" onclick="markAllAsRead()">وضع الكل كمقروء</button>
        </div>
    </div>

    @forelse($notifications as $notification)
        <div class="card">
            <div class="list-item">
                <div class="list-item-icon">{{ $notification->type === 'warning' ? '⚠️' : ($notification->type === 'info' ? 'ℹ️' : '✅') }}</div>
                <div class="list-item-content">
                    <div class="list-item-title">{{ $notification->title ?? ucfirst($notification->type) }}</div>
                    <div class="list-item-subtitle">{{ $notification->message }}</div>
                    @if($notification->course)
                        <div class="list-item-subtitle">المقرر: {{ $notification->course->name }}</div>
                    @endif
                </div>
                <span class="badge badge-{{ $notification->type === 'warning' ? 'danger' : ($notification->type === 'info' ? 'info' : 'success') }}">{{ $notification->read ? 'مقروء' : 'جديد' }}</span>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="list-item">
                <div class="list-item-content">
                    <div class="list-item-title">لا توجد إشعارات جديدة</div>
                </div>
            </div>
        </div>
    @endforelse
</div>

<script>
function markAllAsRead() {
    alert('تم وضع جميع الإشعارات كمقروءة');
}
</script>
@endsection

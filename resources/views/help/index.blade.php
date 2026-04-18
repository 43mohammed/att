@extends('layouts.pwa')

@section('title', 'Help & Support')

@section('content')
<div class="page">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">❓ FAQ</h3>
        </div>
    </div>

    <div class="card">
        <div class="list-item" onclick="toggleFAQ(0)">
            <div class="list-item-content">
                <div class="list-item-title">How do I scan QR codes?</div>
            </div>
            <span class="list-item-action">▼</span>
        </div>
        <div id="faq-0" class="hidden" style="padding: 0 1rem 1rem;">
            <p>Open attendance page and tap QR scanner. Allow camera access and point at QR code.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📧 Contact Support</h3>
        </div>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" class="form-input" placeholder="Enter subject">
        </div>
        <div class="form-group">
            <label class="form-label">Message</label>
            <textarea class="form-textarea" placeholder="Describe your issue..."></textarea>
        </div>
        <button class="btn btn-primary btn-block">Send</button>
    </div>
</div>

<script>
function toggleFAQ(index) {
    document.getElementById('faq-' + index).classList.toggle('hidden');
}
</script>
@endsection

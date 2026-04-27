@extends('layouts.app')

@section('title', 'لوحة تحكم الطالب')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div style="margin-bottom: var(--spacing-2xl);">
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin-bottom: var(--spacing-sm);">
            مرحباً بك في لوحة التحكم
        </h1>
        <p style="color: var(--text-light); font-size: 0.95rem;">
            تابع حضورك وإدارة مقرراتك الدراسية
        </p>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-3">
        <div class="stat-card">
            <h3>إجمالي الحضور</h3>
            <div class="value">{{ $studentStats['total_records'] ?? 0 }}</div>
            <div class="subtitle">جلسة حضور</div>
        </div>
        <div class="stat-card info">
            <h3>المقررات المسجلة</h3>
            <div class="value">{{ $studentStats['total_courses'] ?? 0 }}</div>
            <div class="subtitle">مقرر نشط</div>
        </div>
        <div class="stat-card warning">
            <h3>جلسات الحضور</h3>
            <div class="value">{{ $studentStats['total_sessions'] ?? 0 }}</div>
            <div class="subtitle">جلسة متاحة</div>
        </div>
    </div>

    <!-- Courses Section -->
    <div class="card">
        <div class="card-header">
            <span>📚 مقرراتي الدراسية</span>
            <a href="{{ route('student.courses.index') }}" class="btn btn-sm btn-primary">عرض الكل</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>اسم المقرر</th>
                            <th>الكود</th>
                            <th>المحاضر</th>
                            <th>الحضور</th>
                            <th>الغياب</th>
                            <th>النسبة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courseSummaries as $cs)
                        <tr>
                            <td data-label="اسم المقرر">{{ $cs['course']->name ?? '-' }}</td>
                            <td data-label="الكود">{{ $cs['course']->code ?? '-' }}</td>
                            <td data-label="المحاضر">{{ $cs['course']->instructor->name ?? '-' }}</td>
                            <td data-label="الحضور">{{ $cs['attended'] }}</td>
                            <td data-label="الغياب">{{ $cs['absent'] }}</td>
                            <td data-label="النسبة">
                                <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                    <div style="flex: 1;">
                                        <div class="progress">
                                            <div class="progress-bar success" style="width: {{ $cs['percentage'] }}%;"></div>
                                        </div>
                                    </div>
                                    <span style="font-weight: 700; color: var(--success);">{{ $cs['percentage'] }}%</span>
                                </div>
                            </td>
                            <td data-label="الإجراءات">
                                <a href="/student/courses/{{ $cs['course']->id }}" class="btn btn-sm btn-secondary">عرض</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center">لا توجد مقررات مسجلة</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="grid grid-2">
        <!-- QR Code Scanner -->
        <div class="card">
            <div class="card-header">
                <span>📷 مسح QR Code</span>
            </div>
            <div class="card-body" style="text-align: center;">
                <p style="color: var(--text-light); margin-bottom: var(--spacing-lg); font-size: 0.9rem;">
                    امسح رمز QR لتسجيل حضورك
                </p>
                <a href="/student/scan-qr" class="btn btn-primary btn-lg btn-block" style="margin-bottom: var(--spacing-md); text-decoration: none; color: white;">
                    📷 مسح QR Code
                </a>
                <div id="qr-scanner" style="display: none; margin-top: var(--spacing-lg);">
                    <video id="video" autoplay playsinline muted style="width: 100%; height: auto; border-radius: var(--radius-lg); max-height: 300px; background: var(--dark); display: none; object-fit: cover;"></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                </div>
            </div>
        </div>

        <!-- NFC Card Reader -->
        <div class="card">
            <div class="card-header">
                <span>📱 قراءة بطاقة NFC</span>
            </div>
            <div class="card-body" style="text-align: center;">
                <p style="color: var(--text-light); margin-bottom: var(--spacing-lg); font-size: 0.9rem;">
                    اقرأ بطاقة NFC لتسجيل حضورك
                </p>
                <button class="btn btn-success btn-lg btn-block" onclick="startNFCScanning()">
                    📱 ابدأ القراءة
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications Section -->
    <div class="card">
        <div class="card-header">
            <span>🔔 الإشعارات والتنبيهات</span>
        </div>
        <div class="card-body" style="display: grid; gap: var(--spacing-md);">
            @forelse($notifications as $notification)
                <div class="alert alert-{{ $notification->type === 'warning' ? 'warning' : ($notification->type === 'info' ? 'info' : 'success') }}">
                    <span class="alert-icon">{{ $notification->type === 'warning' ? '⚠️' : ($notification->type === 'info' ? 'ℹ️' : '✓') }}</span>
                    <div>
                        <strong>{{ $notification->title ?? ucfirst($notification->type) }}:</strong>
                        {{ $notification->message }}
                        @if($notification->course)
                            <span> - {{ $notification->course->name }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    <span class="alert-icon">ℹ️</span>
                    <div>
                        لا توجد إشعارات في الوقت الحالي.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Attendance Records -->
    <div class="card">
        <div class="card-header">
            <span>📋 آخر سجلات الحضور</span>
            <a href="/student/attendance" class="btn btn-sm btn-secondary">عرض الكل</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>المقرر</th>
                            <th>التاريخ والوقت</th>
                            <th>طريقة التحقق</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRecords as $r)
                        <tr>
                            <td data-label="المقرر">{{ $r->course->name ?? '-' }}</td>
                            <td data-label="التاريخ والوقت">{{ optional($r->marked_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td data-label="طريقة التحقق"><span class="badge badge-primary">{{ $r->verification_method ?? '—' }}</span></td>
                            <td data-label="الحالة"><span class="badge badge-success">حاضر</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center">لا توجد سجلات حضور مؤخراً</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
function updateVideoElement(video) {
    video.setAttribute('playsinline', 'true');
    video.setAttribute('muted', 'true');
    video.setAttribute('autoplay', 'true');
    video.playsInline = true;
    video.muted = true;
    video.autoplay = true;
    video.style.width = '100%';
    video.style.height = 'auto';
    video.style.objectFit = 'cover';
    video.style.position = 'relative';
    video.style.zIndex = '1000';
    video.style.display = 'none';
    video.style.visibility = 'visible';
    video.style.opacity = '1';
}

function waitForVideoEvent(video, eventName, timeout = 7000) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
            video.removeEventListener(eventName, onEvent);
            reject(new Error(`Timeout waiting for ${eventName}`));
        }, timeout);

        function onEvent() {
            clearTimeout(timer);
            video.removeEventListener(eventName, onEvent);
            resolve();
        }

        video.addEventListener(eventName, onEvent);
    });
}

async function startQRScanner() {
    const scanner = document.getElementById('qr-scanner');
    const video = document.getElementById('video');

    if (scanner.style.display === 'none') {
        scanner.style.display = 'block';
        updateVideoElement(video);

        try {
            const primaryConstraints = {
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };
            let stream;

            try {
                stream = await navigator.mediaDevices.getUserMedia(primaryConstraints);
                console.log('[Dashboard QR] Primary getUserMedia succeeded', primaryConstraints);
            } catch (primaryError) {
                console.warn('[Dashboard QR] Primary getUserMedia failed', primaryError);
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                console.log('[Dashboard QR] Fallback getUserMedia succeeded', { video: true });
            }

            video.srcObject = stream;
            console.log('[Dashboard QR] Assigned srcObject to video', video.srcObject);

            const tracks = stream.getTracks().map(track => ({ kind: track.kind, label: track.label, readyState: track.readyState }));
            console.log('[Dashboard QR] Stream tracks info', tracks);

            try {
                await waitForVideoEvent(video, 'loadedmetadata');
                console.log('[Dashboard QR] loadedmetadata fired');
            } catch (e) {
                console.warn('[Dashboard QR] loadedmetadata timeout', e);
            }

            try {
                await waitForVideoEvent(video, 'canplay');
                console.log('[Dashboard QR] canplay fired');
            } catch (e) {
                console.warn('[Dashboard QR] canplay timeout', e);
            }

            try {
                await video.play();
                console.log('[Dashboard QR] video.play succeeded');
            } catch (playError) {
                console.warn('[Dashboard QR] video.play failed', playError);
            }

            video.style.display = 'block';

            if (video.videoWidth === 0 || video.videoHeight === 0) {
                console.warn('[Dashboard QR] zero video dimensions', { videoWidth: video.videoWidth, videoHeight: video.videoHeight });
                setTimeout(() => {
                    if (video.videoWidth === 0 || video.videoHeight === 0) {
                        console.warn('[Dashboard QR] reinitializing stream due to zero dimensions');
                        startQRScanner();
                    }
                }, 1000);
            }

            scanQRCode();
        } catch (error) {
            console.error('[Dashboard QR] Error accessing camera:', error);
            alert('خطأ في الوصول للكاميرا: ' + error.message);
        }
    } else {
        scanner.style.display = 'none';
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }
    }
}

function scanQRCode() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    const scan = async () => {
        if (video.readyState === video.HAVE_ENOUGH_DATA && video.videoWidth > 0 && video.videoHeight > 0) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            try {
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code) {
                    console.log('QR Code detected:', code.data);
                    
                    fetch('/student/scan-qr', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ qr_data: code.data })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('تم تسجيل الحضور بنجاح!');
                            document.getElementById('qr-scanner').style.display = 'none';
                            if (video.srcObject) {
                                video.srcObject.getTracks().forEach(track => track.stop());
                            }
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            alert('خطأ: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('خطأ في الاتصال بالخادم');
                    });
                    
                    return;
                }
            } catch (e) {
                console.log('QR scanning error:', e);
            }
        }
        requestAnimationFrame(scan);
    };

    scan();
}

async function startNFCScanning() {
    if ('NDEFReader' in window) {
        try {
            const ndef = new NDEFReader();
            await ndef.scan();
            alert('جاري البحث عن بطاقات NFC...');
            
            ndef.onreading = event => {
                console.log('NFC tag detected');
                alert('تم قراءة بطاقة NFC بنجاح');
            };
        } catch (error) {
            alert('NFC غير متاح على هذا الجهاز: ' + error.message);
        }
    } else {
        alert('NFC غير مدعوم في هذا المتصفح');
    }
}
</script>
@endsection

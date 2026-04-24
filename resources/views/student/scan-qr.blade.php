@extends('layouts.app')

@section('title', 'مسح QR Code للحضور')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>مسح QR Code لتسجيل الحضور</h1>
        <a href="{{ route('student.attendance') }}" class="btn btn-secondary">العودة إلى الحضور</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>كاميرا المسح</h3>
                </div>
                <div class="card-body">
                    <div id="qr-scanner" class="text-center">
                        <div id="camera-status" class="alert alert-info mb-3">
                            <i class="fas fa-camera"></i> جاري تهيئة الكاميرا...
                        </div>
                        <button id="start-camera-btn" class="btn btn-primary btn-block mb-3">🎥 ابدأ الكاميرا</button>
                        <video id="preview" autoplay playsinline muted style="width: 100%; max-width: 500px; height: auto; border: 2px solid #007bff; border-radius: 10px; display: none; background: #000; object-fit: cover;"></video>
                        <div id="loading" class="mt-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">جاري التحميل...</span>
                            </div>
                            <p class="mt-2">جاري تحميل الكاميرا...</p>
                        </div>
                        <div id="scanning-indicator" class="mt-3" style="display: none;">
                            <div class="alert alert-warning">
                                <i class="fas fa-search"></i> جاري البحث عن رمز QR...
                            </div>
                        </div>
                    </div>
                    <div id="result" class="mt-3" style="display: none;">
                        <div class="alert alert-success">
                            <h4>تم المسح بنجاح!</h4>
                            <p id="scan-result"></p>
                        </div>
                    </div>
                    <div id="error" class="mt-3" style="display: none;">
                        <div class="alert alert-danger">
                            <h4>خطأ في المسح</h4>
                            <p id="error-message"></p>
                            <button id="retry-btn" class="btn btn-primary btn-sm mt-2">إعادة المحاولة</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>تعليمات</h3>
                </div>
                <div class="card-body">
                    <ol>
                        <li>تأكد من أن الكاميرا مفعلة في المتصفح</li>
                        <li>وجه الكاميرا نحو رمز QR المعروض من قبل المحاضر</li>
                        <li>انتظر حتى يتم التعرف على الرمز تلقائياً</li>
                        <li>سيتم تسجيل حضورك فوراً</li>
                    </ol>
                    <div class="alert alert-info">
                        <strong>ملاحظة:</strong> تأكد من أن الإضاءة جيدة وأن الرمز واضح في مجال رؤية الكاميرا.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let video = document.getElementById('preview');
    let canvas = document.createElement('canvas');
    let ctx = canvas.getContext('2d');
    let stream = null;
    let scanning = false;
    let retryCount = 0;
    const maxRetries = 3;
    const startButton = document.getElementById('start-camera-btn');
    const cameraStatus = document.getElementById('camera-status');
    const loadingIndicator = document.getElementById('loading');
    const scanningIndicator = document.getElementById('scanning-indicator');
    const errorContainer = document.getElementById('error');
    const resultContainer = document.getElementById('result');

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true || document.referrer.includes('android-app://');
    console.log('[QR Camera] Standalone mode:', isStandalone);

    function log(...args) {
        console.log('[QR Camera]', ...args);
    }

    function warn(...args) {
        console.warn('[QR Camera]', ...args);
    }

    function updateStatus(message, className = 'alert alert-info mb-3') {
        cameraStatus.innerHTML = message;
        cameraStatus.className = className;
    }

    function getComputedVideoInfo() {
        const style = window.getComputedStyle(video);
        return {
            display: style.display,
            visibility: style.visibility,
            opacity: style.opacity,
            width: style.width,
            height: style.height
        };
    }

    function debugVideoState(label) {
        log(label, {
            readyState: video.readyState,
            videoWidth: video.videoWidth,
            videoHeight: video.videoHeight,
            srcObject: !!video.srcObject,
            ...getComputedVideoInfo()
        });
    }

    function applyVideoAttributes() {
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
        video.style.visibility = 'visible';
        video.style.opacity = '1';
        video.style.display = 'none';
    }

    function forceRepaint() {
        const originalDisplay = video.style.display;
        video.style.display = 'none';
        void video.offsetHeight;
        video.style.display = originalDisplay || 'block';
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function waitForVideoEvent(eventName, timeout = 7000) {
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

    async function getCameraStream() {
        log('navigator.mediaDevices available:', !!navigator.mediaDevices, 'getUserMedia available:', !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia));
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('navigator.mediaDevices.getUserMedia غير مدعوم');
        }

        const primaryConstraints = {
            video: {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };

        const fallbackConstraints = { video: true };

        try {
            log('Requesting camera with primary constraints', primaryConstraints);
            return await navigator.mediaDevices.getUserMedia(primaryConstraints);
        } catch (primaryError) {
            warn('Primary getUserMedia failed', primaryError);
            if (primaryError.name === 'OverconstrainedError' || primaryError.name === 'NotFoundError' || primaryError.name === 'NotReadableError') {
                log('Retrying with fallback constraints', fallbackConstraints);
                return await navigator.mediaDevices.getUserMedia(fallbackConstraints);
            }
            throw primaryError;
        }
    }

    async function attachStream(mediaStream) {
        applyVideoAttributes();
        stream = mediaStream;
        log('Attaching stream to video.srcObject', mediaStream);
        video.srcObject = stream;
        log('After assignment video.srcObject', video.srcObject);

        const streamTracks = stream.getTracks().map(track => ({
            kind: track.kind,
            label: track.label,
            enabled: track.enabled,
            readyState: track.readyState
        }));
        log('Stream tracks info', streamTracks);

        try {
            await waitForVideoEvent('loadedmetadata');
            log('loadedmetadata event fired');
        } catch (e) {
            warn('loadedmetadata event timeout', e);
        }

        try {
            await waitForVideoEvent('canplay');
            log('canplay event fired');
        } catch (e) {
            warn('canplay event timeout', e);
        }

        try {
            await video.play();
            log('video.play() succeeded');
        } catch (e) {
            warn('video.play() failed', e);
        }

        forceRepaint();
        video.style.display = 'block';
        loadingIndicator.style.display = 'none';
        scanningIndicator.style.display = 'block';
        updateStatus('<i class="fas fa-check-circle text-success"></i> تم تشغيل الكاميرا بنجاح', 'alert alert-success mb-3');
        debugVideoState('Preview ready');

        if (video.videoWidth === 0 || video.videoHeight === 0) {
            warn('Video dimensions are zero after start', { videoWidth: video.videoWidth, videoHeight: video.videoHeight });
            setTimeout(() => {
                if (video.videoWidth === 0 || video.videoHeight === 0) {
                    warn('Reattaching stream due to zero dimensions');
                    recoverCamera();
                }
            }, 1000);
            return;
        }

        startScanning();
    }

    async function startCamera() {
        updateStatus('<i class="fas fa-camera"></i> طلب إذن الوصول للكاميرا...');
        loadingIndicator.style.display = 'block';
        scanningIndicator.style.display = 'none';
        errorContainer.style.display = 'none';
        resultContainer.style.display = 'none';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            const message = 'الكاميرا غير مدعومة في هذا المتصفح';
            console.error('[QR Camera] Missing navigator.mediaDevices or getUserMedia');
            updateStatus('<i class="fas fa-exclamation-triangle text-danger"></i> ' + message, 'alert alert-danger mb-3');
            showError(message);
            return;
        }

        try {
            const mediaStream = await getCameraStream();
            await attachStream(mediaStream);
        } catch (err) {
            console.error('[QR Camera] Error accessing camera:', err);
            let errorMessage = 'خطأ في الوصول إلى الكاميرا: ' + (err.message || err.name);
            if (err.name === 'NotAllowedError') {
                errorMessage = 'يجب منح إذن الوصول للكاميرا في المتصفح.';
            } else if (err.name === 'NotFoundError') {
                errorMessage = 'لم يتم العثور على كاميرا.';
            } else if (err.name === 'NotReadableError') {
                errorMessage = 'لم يتمكن المتصفح من قراءة الكاميرا.';
            }
            updateStatus('<i class="fas fa-exclamation-triangle text-danger"></i> ' + errorMessage, 'alert alert-danger mb-3');
            showError(errorMessage);
        }
    }

    async function recoverCamera() {
        if (retryCount >= maxRetries) {
            const message = 'لم يتمكن النظام من تهيئة الكاميرا. يرجى إعادة تحميل الصفحة أو تجربة متصفح آخر.';
            console.error('[QR Camera] Max camera recovery retries reached');
            updateStatus('<i class="fas fa-exclamation-triangle text-danger"></i> ' + message, 'alert alert-danger mb-3');
            showError(message);
            return;
        }

        retryCount += 1;
        console.warn('[QR Camera] Attempting camera recovery', retryCount);
        stopScanning();
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        await sleep(500);
        startCamera();
    }

    function stopScanning() {
        scanning = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }

    function startScanning() {
        scanning = true;
        scanQRCode();
    }

    function scanQRCode() {
        if (!scanning) return;

        if (video.readyState >= video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            try {
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert'
                });

                if (code) {
                    log('QR Code detected:', code.data);
                    stopScanning();
                    handleScan(code.data);
                    return;
                }
            } catch (error) {
                console.error('[QR Camera] QR scanning error:', error);
            }
        }

        requestAnimationFrame(scanQRCode);
    }

    function handleScan(content) {
        console.log('Processing scan:', content);

        // إرسال البيانات إلى الخادم
        fetch('/student/scan-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                qr_data: content
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.success);
            } else {
                showError(data.error);
                // إعادة تشغيل المسح بعد الخطأ
                setTimeout(() => {
                    startCamera();
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            showError('خطأ في الاتصال بالخادم. تحقق من اتصال الإنترنت.');
            setTimeout(() => {
                startCamera();
            }, 3000);
        });
    }

    function showSuccess(message) {
        document.getElementById('result').style.display = 'block';
        document.getElementById('error').style.display = 'none';
        document.getElementById('scan-result').textContent = message;
        document.getElementById('qr-scanner').innerHTML = '<div class="alert alert-success"><h4>✅ تم تسجيل الحضور بنجاح!</h4><p>' + message + '</p></div>';
    }

    function showError(message) {
        document.getElementById('error').style.display = 'block';
        document.getElementById('result').style.display = 'none';
        document.getElementById('error-message').textContent = message;
    }

    startButton.addEventListener('click', async function() {
        startButton.disabled = true;
        startButton.textContent = '⏳ جاري تهيئة الكاميرا...';
        await startCamera();
        startButton.disabled = false;
        startButton.textContent = '🔄 إعادة تشغيل الكاميرا';
    });

    document.getElementById('retry-btn').addEventListener('click', async function() {
        await startCamera();
    });

    window.addEventListener('beforeunload', function() {
        stopScanning();
    });
});
</script>
@endsection
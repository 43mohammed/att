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
                        <video id="preview" style="width: 100%; max-width: 500px; border: 2px solid #007bff; border-radius: 10px; display: none;"></video>
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
                            <button id="retry-btn" class="btn btn-primary btn-sm mt-2" onclick="startCamera()">إعادة المحاولة</button>
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

    async function startCamera() {
        try {
            document.getElementById('camera-status').innerHTML = '<i class="fas fa-camera"></i> طلب إذن الوصول للكاميرا...';
            
            const constraints = {
                video: {
                    facingMode: 'environment', // استخدم الكاميرا الخلفية
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            };

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;

            video.addEventListener('loadedmetadata', function() {
                document.getElementById('camera-status').innerHTML = '<i class="fas fa-check-circle text-success"></i> تم تشغيل الكاميرا بنجاح';
                document.getElementById('preview').style.display = 'block';
                document.getElementById('loading').style.display = 'none';
                document.getElementById('scanning-indicator').style.display = 'block';
                startScanning();
            });

        } catch (error) {
            console.error('Error accessing camera:', error);
            let errorMessage = 'خطأ في الوصول إلى الكاميرا: ';
            if (error.name === 'NotAllowedError') {
                errorMessage += 'يجب منح إذن الوصول للكاميرا في المتصفح.';
            } else if (error.name === 'NotFoundError') {
                errorMessage += 'لم يتم العثور على كاميرا.';
            } else {
                errorMessage += error.message;
            }
            document.getElementById('camera-status').innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i> ' + errorMessage;
            document.getElementById('camera-status').className = 'alert alert-danger';
        }
    }

    function startScanning() {
        scanning = true;
        scanQRCode();
    }

    function stopScanning() {
        scanning = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }

    function scanQRCode() {
        if (!scanning) return;

        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            
            try {
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert'
                });

                if (code) {
                    console.log('QR Code detected:', code.data);
                    stopScanning();
                    handleScan(code.data);
                    return;
                }
            } catch (error) {
                console.error('QR scanning error:', error);
            }
        }

        if (scanning) {
            requestAnimationFrame(scanQRCode);
        }
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

    // بدء تشغيل الكاميرا عند تحميل الصفحة
    startCamera();

    // تنظيف عند مغادرة الصفحة
    window.addEventListener('beforeunload', function() {
        stopScanning();
    });
});
</script>
@endsection
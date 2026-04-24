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
                    <style>
                        #qr-scanner {
                            position: relative;
                            overflow: hidden;
                        }
                        #preview {
                            position: relative;
                            z-index: 1000;
                        }
                        #scan-overlay {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            width: 250px;
                            height: 250px;
                            border: 3px solid #00ff00;
                            border-radius: 15px;
                            pointer-events: none;
                            z-index: 1001;
                            box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
                        }
                        #scan-line {
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 2px;
                            background: linear-gradient(90deg, transparent, #00ff00, transparent);
                            animation: scanAnimation 2s linear infinite;
                            z-index: 1002;
                        }
                        @keyframes scanAnimation {
                            0% { top: 0; }
                            50% { top: 50%; }
                            100% { top: 100%; }
                        }
                        .qr-detected {
                            border-color: #ff0000 !important;
                            box-shadow: 0 0 30px rgba(255, 0, 0, 0.5) !important;
                        }
                    </style>
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
    // Clean Architecture Classes
    class DeviceOptimizer {
        constructor() {
            this.performance = this.detectPerformance();
            this.settings = this.getOptimizedSettings();
        }

        detectPerformance() {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            const memory = navigator.deviceMemory || 4;
            const cores = navigator.hardwareConcurrency || 2;

            let score = 0;
            if (cores >= 4) score += 2;
            else if (cores >= 2) score += 1;

            if (memory >= 4) score += 2;
            else if (memory >= 2) score += 1;

            if (connection && connection.effectiveType === '4g') score += 1;

            return score >= 3 ? 'high' : score >= 1 ? 'medium' : 'low';
        }

        getOptimizedSettings() {
            const baseSettings = {
                low: { resolution: 0.5, fps: 15, scanRate: 200 },
                medium: { resolution: 0.75, fps: 20, scanRate: 100 },
                high: { resolution: 1.0, fps: 30, scanRate: 50 }
            };
            return baseSettings[this.performance];
        }
    }

    class CameraHandler {
        constructor(video, onStreamReady, onError) {
            this.video = video;
            this.onStreamReady = onStreamReady;
            this.onError = onError;
            this.stream = null;
            this.retryCount = 0;
            this.maxRetries = 3;
            this.isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                              window.navigator.standalone === true ||
                              document.referrer.includes('android-app://');
        }

        async start() {
            try {
                const constraints = this.getConstraints();
                this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                await this.attachStream();
            } catch (error) {
                this.handleError(error);
            }
        }

        getConstraints() {
            return {
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    frameRate: { ideal: deviceOptimizer.settings.fps }
                },
                audio: false
            };
        }

        async attachStream() {
            this.video.srcObject = this.stream;
            this.video.playsInline = true;
            this.video.muted = true;
            this.video.autoplay = true;

            await Promise.race([
                new Promise(resolve => this.video.addEventListener('loadedmetadata', resolve, { once: true })),
                new Promise(resolve => this.video.addEventListener('canplay', resolve, { once: true }))
            ]);

            await this.video.play();
            this.onStreamReady();
        }

        handleError(error) {
            console.error('[CameraHandler] Error:', error);
            if (this.retryCount < this.maxRetries) {
                this.retryCount++;
                setTimeout(() => this.start(), 1000);
            } else {
                this.onError(error);
            }
        }

        stop() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
        }

        resume() {
            if (!this.stream && this.video.srcObject) {
                this.start();
            }
        }
    }

    class ScanEngine {
        constructor(video, canvas, onDetect, deviceOptimizer, uiUpdater) {
            this.video = video;
            this.canvas = canvas;
            this.ctx = canvas.getContext('2d');
            this.onDetect = onDetect;
            this.uiUpdater = uiUpdater;
            this.deviceOptimizer = deviceOptimizer;
            this.scanning = false;
            this.lastFrame = null;
            this.motionThreshold = 0.01;
            this.scanRegion = { x: 0.25, y: 0.25, width: 0.5, height: 0.5 };
            this.resolution = deviceOptimizer.settings.resolution;
            this.scanRate = deviceOptimizer.settings.scanRate;
            this.lastScanTime = 0;
            this.cooldown = 2000; // 2 seconds debounce
            this.lastDetection = null;
            this.focusThreshold = 50;
            this.animationId = null;
            this.blurCount = 0;
        }

        start() {
            this.scanning = true;
            this.animate();
        }

        stop() {
            this.scanning = false;
            if (this.animationId) {
                cancelAnimationFrame(this.animationId);
            }
        }

        animate() {
            if (!this.scanning) return;

            const now = Date.now();
            const timeSinceLastScan = now - this.lastScanTime;

            if (timeSinceLastScan >= this.scanRate) {
                this.processFrame();
                this.lastScanTime = now;
            }

            this.animationId = requestAnimationFrame(() => this.animate());
        }

        processFrame() {
            if (this.video.readyState < this.video.HAVE_ENOUGH_DATA) return;

            const width = this.video.videoWidth * this.resolution;
            const height = this.video.videoHeight * this.resolution;

            this.canvas.width = width;
            this.canvas.height = height;
            this.ctx.drawImage(this.video, 0, 0, width, height);

            // Motion detection
            if (!this.detectMotion()) {
                this.scanRate = Math.min(this.scanRate + 10, 500); // Slow down
                return;
            }
            this.scanRate = this.deviceOptimizer.settings.scanRate; // Reset to fast

            // Focus check
            if (!this.checkFocus()) {
                this.blurCount++;
                if (this.blurCount > 5) {
                    this.uiUpdater.updateState('HOLD_STEADY');
                }
                return;
            }
            this.blurCount = 0;
            if (this.uiUpdater.currentState === 'HOLD_STEADY') {
                this.uiUpdater.updateState('SEARCHING');
            }

            // Lighting enhancement
            this.enhanceLighting();

            // Dynamic region
            this.adjustScanRegion();

            // Multi-resolution scan
            this.scanAtResolutions();
        }

        detectMotion() {
            const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
            const currentFrame = new Uint8ClampedArray(imageData.data);

            if (!this.lastFrame) {
                this.lastFrame = currentFrame;
                return true;
            }

            let diff = 0;
            for (let i = 0; i < currentFrame.length; i += 4) {
                diff += Math.abs(currentFrame[i] - this.lastFrame[i]) +
                       Math.abs(currentFrame[i+1] - this.lastFrame[i+1]) +
                       Math.abs(currentFrame[i+2] - this.lastFrame[i+2]);
            }
            diff /= currentFrame.length;

            this.lastFrame = currentFrame;
            return diff > this.motionThreshold;
        }

        checkFocus() {
            const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
            const data = imageData.data;
            let contrast = 0;

            for (let i = 0; i < data.length; i += 4) {
                const gray = (data[i] + data[i+1] + data[i+2]) / 3;
                contrast += Math.abs(gray - 128);
            }
            contrast /= data.length / 4;

            return contrast > this.focusThreshold;
        }

        enhanceLighting() {
            const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
            const data = imageData.data;

            for (let i = 0; i < data.length; i += 4) {
                data[i] = Math.min(255, data[i] * 1.2);     // R
                data[i+1] = Math.min(255, data[i+1] * 1.2); // G
                data[i+2] = Math.min(255, data[i+2] * 1.2); // B
            }

            this.ctx.putImageData(imageData, 0, 0);
        }

        adjustScanRegion() {
            // Expand if no recent detection, shrink if detected
            if (this.lastDetection && Date.now() - this.lastDetection < 5000) {
                this.scanRegion = { x: 0.4, y: 0.4, width: 0.2, height: 0.2 };
            } else {
                this.scanRegion.width = Math.min(this.scanRegion.width + 0.05, 0.8);
                this.scanRegion.height = Math.min(this.scanRegion.height + 0.05, 0.8);
                this.scanRegion.x = Math.max(0.1, this.scanRegion.x - 0.025);
                this.scanRegion.y = Math.max(0.1, this.scanRegion.y - 0.025);
            }
        }

        scanAtResolutions() {
            const resolutions = [this.resolution, Math.min(this.resolution * 1.5, 1.0)];

            for (const res of resolutions) {
                const scanWidth = Math.floor(this.canvas.width * this.scanRegion.width);
                const scanHeight = Math.floor(this.canvas.height * this.scanRegion.height);
                const scanX = Math.floor(this.canvas.width * this.scanRegion.x);
                const scanY = Math.floor(this.canvas.height * this.scanRegion.y);

                const imageData = this.ctx.getImageData(scanX, scanY, scanWidth, scanHeight);

                try {
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert'
                    });

                    if (code) {
                        this.lastDetection = Date.now();
                        this.onDetect(code.data);
                        return;
                    }
                } catch (error) {
                    console.error('[ScanEngine] Scan error:', error);
                }
            }
        }
    }

    class UIUpdater {
        constructor() {
            this.states = {
                INITIALIZING: 'جاري تهيئة الكاميرا...',
                SEARCHING: 'جاري البحث عن رمز QR...',
                HOLD_STEADY: 'انتظر... الرمز غير واضح',
                DETECTED: 'تم اكتشاف رمز QR ✅'
            };
            this.currentState = 'INITIALIZING';
            this.scanLine = null;
            this.overlay = null;
        }

        init() {
            this.createOverlay();
            this.createScanLine();
        }

        createOverlay() {
            this.overlay = document.createElement('div');
            this.overlay.id = 'scan-overlay';
            this.overlay.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 250px;
                height: 250px;
                border: 3px solid #00ff00;
                border-radius: 15px;
                pointer-events: none;
                z-index: 1001;
                box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
            `;
            document.getElementById('qr-scanner').appendChild(this.overlay);
        }

        createScanLine() {
            this.scanLine = document.createElement('div');
            this.scanLine.id = 'scan-line';
            this.scanLine.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 2px;
                background: linear-gradient(90deg, transparent, #00ff00, transparent);
                animation: scanAnimation 2s linear infinite;
                z-index: 1002;
            `;
            this.overlay.appendChild(this.scanLine);
        }

        updateState(state, message = null) {
            this.currentState = state;
            const statusEl = document.getElementById('camera-status');
            const msg = message || this.states[state];
            statusEl.innerHTML = `<i class="fas fa-${this.getIcon(state)}"></i> ${msg}`;
            statusEl.className = `alert alert-${this.getAlertClass(state)} mb-3`;

            // Highlight overlay on detection
            if (this.overlay) {
                if (state === 'DETECTED') {
                    this.overlay.classList.add('qr-detected');
                } else {
                    this.overlay.classList.remove('qr-detected');
                }
            }
        }

        getIcon(state) {
            switch(state) {
                case 'INITIALIZING': return 'camera';
                case 'SEARCHING': return 'search';
                case 'HOLD_STEADY': return 'pause';
                case 'DETECTED': return 'check-circle';
            }
        }

        getAlertClass(state) {
            switch(state) {
                case 'INITIALIZING': return 'info';
                case 'SEARCHING': return 'warning';
                case 'HOLD_STEADY': return 'secondary';
                case 'DETECTED': return 'success';
            }
        }

        showSuccess(message) {
            document.getElementById('result').style.display = 'block';
            document.getElementById('error').style.display = 'none';
            document.getElementById('scan-result').textContent = message;
        }

        showError(message) {
            document.getElementById('error').style.display = 'block';
            document.getElementById('result').style.display = 'none';
            document.getElementById('error-message').textContent = message;
        }
    }

    class AudioHapticFeedback {
        constructor() {
            this.audioContext = null;
            this.beepBuffer = null;
        }

        async init() {
            try {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                await this.createBeep();
            } catch (error) {
                console.warn('[AudioHaptic] Audio not available:', error);
            }
        }

        async createBeep() {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.setValueAtTime(800, this.audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, this.audioContext.currentTime + 0.1);

            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.2);

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.2);
        }

        playBeep() {
            if (this.audioContext) {
                this.createBeep();
            }
        }

        vibrate() {
            if (navigator.vibrate) {
                navigator.vibrate(100);
            }
        }

        feedback() {
            this.playBeep();
            this.vibrate();
        }
    }

    // Main Application
    const deviceOptimizer = new DeviceOptimizer();
    const uiUpdater = new UIUpdater();
    const audioHaptic = new AudioHapticFeedback();

    let video = document.getElementById('preview');
    let canvas = document.createElement('canvas');
    let cameraHandler = null;
    let scanEngine = null;

    async function init() {
        uiUpdater.init();
        await audioHaptic.init();

        cameraHandler = new CameraHandler(video, onCameraReady, onCameraError);
        scanEngine = new ScanEngine(video, canvas, onQRDetect, deviceOptimizer, uiUpdater);

        document.getElementById('start-camera-btn').addEventListener('click', startCamera);
        document.getElementById('retry-btn').addEventListener('click', startCamera);

        // PWA resume handling
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                cameraHandler.resume();
            }
        });
    }

    function startCamera() {
        uiUpdater.updateState('INITIALIZING');
        cameraHandler.start();
    }

    function onCameraReady() {
        video.style.display = 'block';
        uiUpdater.updateState('SEARCHING');
        scanEngine.start();
    }

    function onCameraError(error) {
        uiUpdater.showError('خطأ في الكاميرا: ' + error.message);
    }

    function onQRDetect(data) {
        uiUpdater.updateState('DETECTED');
        audioHaptic.feedback();

        // Debounce
        if (scanEngine.lastDetection && Date.now() - scanEngine.lastDetection < scanEngine.cooldown) {
            return;
        }

        // Send to server
        fetch('/student/scan-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ qr_data: data })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                uiUpdater.showSuccess(result.success);
                // Continue scanning for next QR
                setTimeout(() => uiUpdater.updateState('SEARCHING'), 2000);
            } else {
                uiUpdater.showError(result.error);
                setTimeout(() => uiUpdater.updateState('SEARCHING'), 3000);
            }
        })
        .catch(error => {
            uiUpdater.showError('خطأ في الاتصال');
            setTimeout(() => uiUpdater.updateState('SEARCHING'), 3000);
        });
    }

    init();
});
</script>
@endsection
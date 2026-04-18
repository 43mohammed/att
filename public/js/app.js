// تطبيق نظام الحضور والغياب

// تسجيل Service Worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then(registration => {
        console.log('✅ Service Worker registered:', registration);
    }).catch(error => {
        console.log('❌ Service Worker registration failed:', error);
    });
}

// دالة لإرسال طلب AJAX
async function fetchAPI(url, options = {}) {
    const defaultOptions = {
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    };

    try {
        const response = await fetch(url, { ...defaultOptions, ...options });
        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Expected JSON response, got ${contentType}: ${text.slice(0, 240)}`);
        }

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'حدث خطأ');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// دالة لعرض الإشعارات
function showNotification(message, type = 'info', duration = 5000) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">×</button>
    `;
    
    const container = document.querySelector('#alerts') || document.body;
    container.appendChild(alertDiv);
    
    if (duration > 0) {
        setTimeout(() => alertDiv.remove(), duration);
    }
}

// دالة لمسح QR Code
async function startQRScanner() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    
    if (!video || !canvas) {
        showNotification('عناصر الفيديو غير موجودة', 'danger');
        return;
    }

    const ctx = canvas.getContext('2d');

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment' } 
        });
        video.srcObject = stream;

        const scan = async () => {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);

                try {
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    
                    if (code) {
                        console.log('✅ QR Code detected:', code.data);
                        stream.getTracks().forEach(track => track.stop());
                        await handleQRCode(code.data);
                        return;
                    }
                } catch (e) {
                    console.log('QR scanning error:', e);
                }
            }
            requestAnimationFrame(scan);
        };

        scan();
    } catch (error) {
        showNotification('خطأ في الوصول إلى الكاميرا: ' + error.message, 'danger');
        console.error('Camera error:', error);
    }
}

// معالجة QR Code
async function handleQRCode(data) {
    try {
        const url = new URL(data);
        const sessionId = url.searchParams.get('session');
        const token = url.searchParams.get('token');

        if (!sessionId || !token) {
            showNotification('رمز QR غير صحيح', 'danger');
            return;
        }

        // الحصول على موقع GPS إذا لزم الأمر
        let latitude = null, longitude = null;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;
                    recordAttendance(sessionId, 'qrcode', latitude, longitude);
                },
                error => {
                    console.log('GPS error:', error);
                    recordAttendance(sessionId, 'qrcode', null, null);
                }
            );
        } else {
            recordAttendance(sessionId, 'qrcode', null, null);
        }
    } catch (error) {
        showNotification('خطأ في معالجة رمز QR', 'danger');
        console.error('QR handling error:', error);
    }
}

// تسجيل الحضور
async function recordAttendance(sessionId, method, latitude = null, longitude = null) {
    try {
        showNotification('جاري تسجيل الحضور...', 'info', 0);
        
        const response = await fetchAPI('/attendance/record', {
            method: 'POST',
            body: JSON.stringify({
                session_id: sessionId,
                verification_method: method,
                latitude,
                longitude,
            }),
        });

        if (response.success) {
            showNotification('✅ تم تسجيل الحضور بنجاح', 'success');
            // إعادة تحميل الصفحة بعد ثانيتين
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification(response.message || 'حدث خطأ', 'danger');
        }
    } catch (error) {
        showNotification('خطأ في تسجيل الحضور: ' + error.message, 'danger');
        console.error('Attendance recording error:', error);
    }
}

// دالة لإنشاء جلسة حضور
async function createSession(courseId, startTime, gpsRequired = false, nfcActive = false) {
    try {
        const response = await fetchAPI('/session/create', {
            method: 'POST',
            body: JSON.stringify({
                course_id: courseId,
                session_date: new Date().toISOString().split('T')[0],
                start_time: startTime,
                gps_required: gpsRequired,
                nfc_active: nfcActive,
            }),
        });

        if (response.success) {
            showNotification('✅ تم إنشاء جلسة الحضور بنجاح', 'success');
            return response.session;
        } else {
            showNotification('خطأ في إنشاء الجلسة', 'danger');
        }
    } catch (error) {
        showNotification('خطأ في إنشاء الجلسة: ' + error.message, 'danger');
        console.error('Session creation error:', error);
    }
}

// دالة لإغلاق جلسة
async function closeSession(sessionId) {
    try {
        const response = await fetchAPI(`/session/${sessionId}/close`, {
            method: 'POST',
        });

        if (response.success) {
            showNotification('✅ تم إغلاق الجلسة بنجاح', 'success');
            setTimeout(() => location.reload(), 1500);
        }
    } catch (error) {
        showNotification('خطأ في إغلاق الجلسة: ' + error.message, 'danger');
    }
}

// دالة لعرض سجل الحضور
async function loadAttendanceRecord(courseId) {
    try {
        const response = await fetchAPI(`/attendance/student/${courseId}`);
        
        if (response.success) {
            const container = document.getElementById('attendance-records');
            if (container) {
                container.innerHTML = response.records.map(record => `
                    <div class="attendance-record present">
                        <span>${new Date(record.marked_at).toLocaleString('ar-SA')}</span>
                        <span>${record.verification_method}</span>
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading attendance records:', error);
    }
}

// دالة لدعم NFC
async function startNFCScanning() {
    if ('NDEFReader' in window) {
        try {
            const ndef = new NDEFReader();
            await ndef.scan();
            showNotification('🔍 جاري البحث عن بطاقات NFC...', 'info', 0);
            
            ndef.onreading = event => {
                const message = event.message;
                for (const record of message.records) {
                    if (record.recordType === 'text') {
                        const text = new TextDecoder().decode(record.data);
                        handleQRCode(text);
                    }
                }
            };

            ndef.onreadingerror = () => {
                showNotification('❌ خطأ في قراءة NFC', 'danger');
            };
        } catch (error) {
            showNotification('NFC غير متاح على هذا الجهاز: ' + error.message, 'warning');
        }
    } else {
        showNotification('NFC غير مدعوم في هذا المتصفح', 'danger');
    }
}

// تحميل مكتبة jsQR
function loadQRLibrary() {
    if (window.jsQR) return; // تم التحميل بالفعل

    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js';
    script.onload = () => {
        console.log('✅ jsQR library loaded');
    };
    script.onerror = () => {
        console.error('❌ Failed to load jsQR library');
    };
    document.head.appendChild(script);
}

// تهيئة التطبيق
document.addEventListener('DOMContentLoaded', () => {
    loadQRLibrary();
    
    // التحقق من دعم PWA
    if (window.matchMedia('(display-mode: standalone)').matches) {
        console.log('✅ تطبيق PWA قيد التشغيل');
    }

    // تحميل الإشعارات فقط إذا كان المستخدم مصدقاً عليه أو إذا كان هناك عنصر الإشعارات
    const notificationsElement = document.getElementById('notifications');
    const isAuthenticated = document.body.dataset.auth === 'true';
    if (isAuthenticated && notificationsElement) {
        loadNotifications();
    }
});

// حفظ البيانات محلياً (Offline Support)
class LocalStorage {
    static saveAttendanceRecord(record) {
        let records = JSON.parse(localStorage.getItem('attendance_records') || '[]');
        records.push({ ...record, timestamp: Date.now() });
        localStorage.setItem('attendance_records', JSON.stringify(records));
    }

    static getOfflineRecords() {
        return JSON.parse(localStorage.getItem('attendance_records') || '[]');
    }

    static clearOfflineRecords() {
        localStorage.removeItem('attendance_records');
    }

    static syncOfflineRecords() {
        const records = this.getOfflineRecords();
        if (records.length === 0) return;

        records.forEach(record => {
            recordAttendance(record.session_id, record.verification_method, record.latitude, record.longitude);
        });
        this.clearOfflineRecords();
    }
}

// مراقبة الاتصال بالإنترنت
window.addEventListener('online', () => {
    showNotification('✅ تم استعادة الاتصال بالإنترنت', 'success');
    LocalStorage.syncOfflineRecords();
});

window.addEventListener('offline', () => {
    showNotification('⚠️ فقدت الاتصال بالإنترنت - سيتم حفظ البيانات محلياً', 'warning');
});

// تحميل الإشعارات
async function loadNotifications() {
    try {
        const response = await fetchAPI('/notifications');
        if (response.success && response.notifications) {
            const container = document.getElementById('notifications');
            if (container) {
                container.innerHTML = response.notifications.map(notification => `
                    <div class="notification ${notification.type}">
                        <strong>${notification.title}</strong>
                        <p>${notification.message}</p>
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}

// طلب إذن الإشعارات
function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

// إرسال إشعار
function sendNotification(title, options = {}) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, {
            icon: '/image/icon-192x192.png',
            badge: '/image/icon-192x192.png',
            ...options,
        });
    }
}

// تحديث الإحصائيات
async function updateStats(courseId) {
    try {
        const response = await fetchAPI(`/attendance/student/${courseId}`);
        if (response.success && response.stats) {
            const statsContainer = document.getElementById('stats');
            if (statsContainer) {
                statsContainer.innerHTML = `
                    <div class="stat">
                        <span>إجمالي الجلسات:</span>
                        <strong>${response.stats.total}</strong>
                    </div>
                    <div class="stat">
                        <span>الحضور:</span>
                        <strong>${response.stats.attended}</strong>
                    </div>
                    <div class="stat">
                        <span>الغياب:</span>
                        <strong>${response.stats.absent}</strong>
                    </div>
                    <div class="stat">
                        <span>النسبة:</span>
                        <strong>${response.stats.percentage}%</strong>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error updating stats:', error);
    }
}

// دالة مساعدة لتنسيق التاريخ
function formatDate(date) {
    return new Date(date).toLocaleDateString('ar-SA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

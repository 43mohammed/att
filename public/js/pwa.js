/**
 * PWA Attendance System - Advanced JavaScript
 * QR Code, NFC, GPS, Notifications
 */

// ============================================
// QR Code Scanner
// ============================================

class QRCodeScanner {
    constructor() {
        this.video = null;
        this.canvas = null;
        this.isScanning = false;
    }

    async start(elementId) {
        this.video = document.getElementById(elementId);
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' }
            });
            
            this.video.srcObject = stream;
            this.video.style.display = 'block';
            this.isScanning = true;
            
            this.scan();
        } catch (error) {
            console.error('❌ خطأ في الوصول للكاميرا:', error);
            alert('يرجى السماح بالوصول للكاميرا');
        }
    }

    stop() {
        if (this.video && this.video.srcObject) {
            this.video.srcObject.getTracks().forEach(track => track.stop());
            this.video.style.display = 'none';
            this.isScanning = false;
        }
    }

    scan() {
        if (!this.isScanning) return;

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        
        canvas.width = this.video.videoWidth;
        canvas.height = this.video.videoHeight;
        
        context.drawImage(this.video, 0, 0, canvas.width, canvas.height);
        
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        
        // محاولة فك تشفير QR Code
        try {
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code) {
                this.onCodeDetected(code.data);
                this.stop();
                return;
            }
        } catch (error) {
            console.log('جاري البحث عن QR Code...');
        }
        
        requestAnimationFrame(() => this.scan());
    }

    onCodeDetected(data) {
        console.log('✅ تم اكتشاف QR Code:', data);
        // إرسال البيانات للسيرفر
        submitAttendance(data, 'qrcode');
    }
}

// ============================================
// NFC Scanner
// ============================================

class NFCScanner {
    async start() {
        if (!('NDEFReader' in window)) {
            alert('NFC غير مدعوم في هذا الجهاز');
            return;
        }

        try {
            const ndef = new NDEFReader();
            await ndef.scan();
            
            ndef.onreading = (event) => {
                const decoder = new TextDecoder();
                for (const record of event.message.records) {
                    if (record.recordType === 'text') {
                        const text = decoder.decode(record.data);
                        this.onTagDetected(text);
                    }
                }
            };

            ndef.onerror = () => {
                alert('❌ خطأ في قراءة NFC');
            };

            console.log('✅ تم تفعيل قارئ NFC');
        } catch (error) {
            console.error('❌ خطأ في NFC:', error);
        }
    }

    onTagDetected(data) {
        console.log('✅ تم اكتشاف NFC Tag:', data);
        submitAttendance(data, 'nfc');
    }
}

// ============================================
// GPS Location
// ============================================

class GPSLocation {
    static async getLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject('GPS غير مدعوم');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const { latitude, longitude, accuracy } = position.coords;
                    resolve({ latitude, longitude, accuracy });
                },
                (error) => {
                    console.error('❌ خطأ في الحصول على الموقع:', error);
                    reject(error.message);
                }
            );
        });
    }

    static calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // نصف قطر الأرض بالكيلومتر
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c; // المسافة بالكيلومتر
    }
}

// ============================================
// Attendance Submission
// ============================================

async function submitAttendance(data, method) {
    try {
        // الحصول على الموقع إن أمكن
        let location = null;
        try {
            location = await GPSLocation.getLocation();
        } catch (error) {
            console.log('تحذير: لم يتمكن من الحصول على الموقع:', error);
        }

        // إرسال الطلب
        const response = await fetch('/api/attendance/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({
                qr_token: method === 'qrcode' ? data : null,
                nfc_data: method === 'nfc' ? data : null,
                latitude: location?.latitude,
                longitude: location?.longitude,
                accuracy: location?.accuracy,
                verification_method: method
            })
        });

        const result = await response.json();

        if (response.ok) {
            showNotification('✅ تم تسجيل الحضور بنجاح!', 'success');
            // حفظ في IndexedDB للعمل بدون اتصال
            await saveToIndexedDB('attendance', result);
        } else {
            showNotification('❌ ' + (result.message || 'خطأ في التسجيل'), 'error');
        }
    } catch (error) {
        console.error('❌ خطأ:', error);
        showNotification('❌ خطأ في الاتصال', 'error');
        // محاولة الحفظ المحلي
        await saveToIndexedDB('pending_attendance', { data, method });
    }
}

// ============================================
// Notifications
// ============================================

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} animate-slide-down`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;

    const container = document.querySelector('.app-content') || document.body;
    container.insertBefore(notification, container.firstChild);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// ============================================
// IndexedDB Storage
// ============================================

class IndexedDBStorage {
    constructor(dbName = 'AttendanceDB') {
        this.dbName = dbName;
        this.db = null;
    }

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, 1);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve();
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains('attendance')) {
                    db.createObjectStore('attendance', { keyPath: 'id', autoIncrement: true });
                }
                if (!db.objectStoreNames.contains('pending_attendance')) {
                    db.createObjectStore('pending_attendance', { keyPath: 'id', autoIncrement: true });
                }
            };
        });
    }

    async save(storeName, data) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.add(data);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }

    async getAll(storeName) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }

    async clear(storeName) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.clear();

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve();
        });
    }
}

// ============================================
// Global Storage Instance
// ============================================

const storage = new IndexedDBStorage();

async function saveToIndexedDB(storeName, data) {
    try {
        if (!storage.db) {
            await storage.init();
        }
        await storage.save(storeName, { ...data, timestamp: new Date() });
        console.log('✅ تم الحفظ محلياً:', storeName);
    } catch (error) {
        console.error('❌ خطأ في الحفظ المحلي:', error);
    }
}

// ============================================
// Sync Pending Data
// ============================================

async function syncPendingData() {
    try {
        if (!storage.db) {
            await storage.init();
        }

        const pendingData = await storage.getAll('pending_attendance');

        for (const item of pendingData) {
            try {
                await submitAttendance(item.data, item.method);
                // حذف من المعلقة بعد النجاح
                const transaction = storage.db.transaction(['pending_attendance'], 'readwrite');
                transaction.objectStore('pending_attendance').delete(item.id);
            } catch (error) {
                console.error('❌ فشل المزامنة:', error);
            }
        }
    } catch (error) {
        console.error('❌ خطأ في المزامنة:', error);
    }
}

// مزامنة عند الاتصال بالإنترنت
window.addEventListener('online', syncPendingData);

// ============================================
// Push Notifications
// ============================================

async function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.log('المتصفح لا يدعم الإشعارات');
        return;
    }

    if (Notification.permission === 'granted') {
        return;
    }

    if (Notification.permission !== 'denied') {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            console.log('✅ تم منح صلاحية الإشعارات');
        }
    }
}

function sendNotification(title, options = {}) {
    if (Notification.permission === 'granted') {
        new Notification(title, {
            icon: '/images/icon-192x192.png',
            badge: '/images/icon-192x192.png',
            ...options
        });
    }
}

// ============================================
// Initialize PWA
// ============================================

document.addEventListener('DOMContentLoaded', async () => {
    // تهيئة IndexedDB
    try {
        await storage.init();
        console.log('✅ تم تهيئة IndexedDB');
    } catch (error) {
        console.error('❌ خطأ في تهيئة IndexedDB:', error);
    }

    // طلب صلاحية الإشعارات
    await requestNotificationPermission();

    // مزامنة البيانات المعلقة
    if (navigator.onLine) {
        syncPendingData();
    }

    // إضافة مستمعي الأحداث
    setupEventListeners();
});

function setupEventListeners() {
    // زر مسح QR Code
    const qrButton = document.getElementById('startQR');
    if (qrButton) {
        qrButton.addEventListener('click', () => {
            const scanner = new QRCodeScanner();
            scanner.start('video');
        });
    }

    // زر مسح NFC
    const nfcButton = document.getElementById('startNFC');
    if (nfcButton) {
        nfcButton.addEventListener('click', () => {
            const nfc = new NFCScanner();
            nfc.start();
        });
    }
}

// ============================================
// Offline Detection
// ============================================

window.addEventListener('offline', () => {
    showNotification('⚠️ أنت غير متصل بالإنترنت', 'warning');
});

window.addEventListener('online', () => {
    showNotification('✅ تم استعادة الاتصال', 'success');
    syncPendingData();
});

// ============================================
// Export
// ============================================

window.QRCodeScanner = QRCodeScanner;
window.NFCScanner = NFCScanner;
window.GPSLocation = GPSLocation;
window.submitAttendance = submitAttendance;
window.showNotification = showNotification;
window.syncPendingData = syncPendingData;

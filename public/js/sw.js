const CACHE_NAME = 'attendance-system-v1';
const RUNTIME_CACHE = 'attendance-runtime';
const API_CACHE = 'attendance-api';
const URLS_TO_CACHE = [
    '/',
    '/css/pwa.css',
    '/js/pwa.js',
    '/manifest.json',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js'
];

// Update cache names
self.addEventListener('install', event => {
    console.log('🔧 Service Worker installing...');
    event.waitUntil(
        Promise.all([
            caches.open(CACHE_NAME).then(cache => {
                console.log('📦 Caching app shell');
                return cache.addAll(URLS_TO_CACHE).catch(error => {
                    console.log('❌ Cache addAll error:', error);
                });
            }),
            caches.open(RUNTIME_CACHE),
            caches.open(API_CACHE)
        ])
    );
    self.skipWaiting();
});

// تثبيت Service Worker
self.addEventListener('install', event => {
    console.log('🔧 Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('📦 Caching app shell');
            return cache.addAll(URLS_TO_CACHE).catch(error => {
                console.log('❌ Cache addAll error:', error);
            });
        })
    );
    self.skipWaiting();
});

// تفعيل Service Worker
self.addEventListener('activate', event => {
    console.log('✅ Service Worker activated');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME && 
                        cacheName !== RUNTIME_CACHE && 
                        cacheName !== API_CACHE) {
                        console.log('🗑️ Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// استراتيجية الـ Caching
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // تجاهل الطلبات غير GET
    if (request.method !== 'GET') {
        return;
    }

    // تجاهل طلبات Chrome extensions
    if (url.protocol === 'chrome-extension:') {
        return;
    }

    // استراتيجية Cache First للملفات الثابتة
    if (isStaticAsset(url.pathname)) {
        event.respondWith(
            caches.match(request).then(response => {
                return response || fetch(request).then(response => {
                    // حفظ النسخة الجديدة في الـ Cache
                    if (response && response.status === 200) {
                        const responseToCache = response.clone();
                        caches.open(RUNTIME_CACHE).then(cache => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return response;
                });
            }).catch(() => {
                // إرجاع صفحة offline إذا فشلت
                return new Response('Offline - Static asset not available', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({
                        'Content-Type': 'text/plain'
                    })
                });
            })
        );
        return;
    }

    // استراتيجية Network First للـ API
    if (url.pathname.includes('/api/') || url.pathname.includes('/attendance/')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // حفظ النسخة الناجحة
                    if (response && response.status === 200) {
                        const responseToCache = response.clone();
                        caches.open(API_CACHE).then(cache => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // محاولة الحصول على النسخة المخزنة
                    return caches.match(request).then(response => {
                        if (response) {
                            return response;
                        }
                        // إرجاع استجابة خطأ
                        return new Response(
                            JSON.stringify({
                                success: false,
                                message: 'لا يوجد اتصال بالإنترنت. البيانات المخزنة قد تكون قديمة.',
                            }),
                            {
                                status: 503,
                                statusText: 'Service Unavailable',
                                headers: new Headers({
                                    'Content-Type': 'application/json',
                                }),
                            }
                        );
                    });
                })
        );
        return;
    }

    // استراتيجية Stale While Revalidate للصفحات
    event.respondWith(
        caches.match(request).then(response => {
            const fetchPromise = fetch(request).then(response => {
                // حفظ النسخة الجديدة
                if (response && response.status === 200) {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(request, responseToCache);
                    });
                }
                return response;
            });

            return response || fetchPromise;
        }).catch(() => {
            return new Response('Offline - Page not available', {
                status: 503,
                statusText: 'Service Unavailable',
                headers: new Headers({
                    'Content-Type': 'text/plain'
                })
            });
        })
    );
});

// معالجة الرسائل من العميل
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        Promise.all([
            caches.delete(CACHE_NAME),
            caches.delete(RUNTIME_CACHE),
            caches.delete(API_CACHE)
        ]).then(() => {
            console.log('✅ All caches cleared');
        });
    }

    if (event.data && event.data.type === 'SYNC_OFFLINE_DATA') {
        console.log('🔄 Syncing offline data...');
        event.waitUntil(syncOfflineData());
    }
});

// Background Sync
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-attendance') {
        event.waitUntil(syncOfflineData());
    }
});

// معالجة الإشعارات
self.addEventListener('push', event => {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.message || data.body || 'إشعار جديد',
        icon: '/image/icon-192x192.png',
        badge: '/image/icon-192x192.png',
        tag: data.type || 'notification',
        requireInteraction: data.type === 'absence_critical',
        data: {
            url: data.url || '/',
        },
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'نظام الحضور والغياب', options)
    );
});

// معالجة النقر على الإشعار
self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(clientList => {
            // البحث عن نافذة مفتوحة
            for (const client of clientList) {
                if (client.url === event.notification.data.url && 'focus' in client) {
                    return client.focus();
                }
            }
            // فتح نافذة جديدة إذا لم تكن هناك نافذة مفتوحة
            if (clients.openWindow) {
                return clients.openWindow(event.notification.data.url);
            }
        })
    );
});

// دالة مساعدة للتحقق من الملفات الثابتة
function isStaticAsset(pathname) {
    return /\.(js|css|png|jpg|jpeg|svg|gif|webp|woff|woff2|ttf|eot)$/i.test(pathname);
}

// دالة مزامنة البيانات المحفوظة محلياً
async function syncOfflineData() {
    try {
        const db = await openIndexedDB();
        const records = await getAllRecords(db, 'attendance_records');

        for (const record of records) {
            try {
                const response = await fetch('/attendance/record', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(record),
                });

                if (response.ok) {
                    await deleteRecord(db, 'attendance_records', record.id);
                    console.log('✅ Record synced:', record.id);
                }
            } catch (error) {
                console.error('❌ Error syncing record:', error);
            }
        }
    } catch (error) {
        console.error('❌ Error in syncOfflineData:', error);
    }
}

// دالة فتح قاعدة البيانات المحلية
function openIndexedDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('AttendanceSystemDB', 1);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);

        request.onupgradeneeded = event => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('attendance_records')) {
                db.createObjectStore('attendance_records', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

// دالة الحصول على جميع السجلات
function getAllRecords(db, storeName) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.getAll();

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

// دالة حذف سجل
function deleteRecord(db, storeName, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.delete(id);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}

// Periodic Background Sync
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'sync-attendance') {
        event.waitUntil(syncOfflineData());
    }
});

console.log('✅ Service Worker loaded and ready');

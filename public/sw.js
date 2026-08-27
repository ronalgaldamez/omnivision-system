self.addEventListener('push', function (event) {
    let payload = {};
    try {
        payload = event.data ? event.data.json() : {};
    } catch (e) {
        payload = { title: 'Omnivisión', body: event.data ? event.data.text() : '' };
    }

    const title = payload.title || 'Omnivisión';
    const options = {
        body: payload.body || '',
        icon: payload.icon || '/android-chrome-192x192.png',
        badge: payload.badge || '/favicon-32x32.png',
        tag: payload.tag || 'omnivision-notification',
        data: {
            url: (payload.data && payload.data.url) || '/',
        },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

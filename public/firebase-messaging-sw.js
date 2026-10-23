// public/firebase-messaging-sw.js

// Give the service worker access to Firebase Messaging.
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Initialize the Firebase app in the service worker
// 🔥 IMPORTANT: Replace with YOUR Firebase config
firebase.initializeApp({
    apiKey: "AIzaSyAJ4cUzBdwCS6117NhnqZwtvom8V5qoizQ",
    authDomain: "marketplacenaija.firebaseapp.com",
    projectId: "marketplacenaija",
    storageBucket: "marketplacenaija.firebasestorage.app",
    messagingSenderId: "1012615360042",
    appId: "1:1012615360042:web:e440cb7833d7732af99d53"
});



// Retrieve an instance of Firebase Messaging
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    // Customize notification here
    const notificationTitle = payload.notification.title || 'New Notification';
    const notificationOptions = {
        body: payload.notification.body || 'You have a new notification',
        icon: payload.notification.image || '/favicon.ico',
        badge: '/favicon.ico',
        data: payload.data,
        requireInteraction: false,
        tag: 'marketplace-notification'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', function(event) {
    console.log('[firebase-messaging-sw.js] Notification click received.', event);

    event.notification.close();

    // Get the data from notification
    const data = event.notification.data;

    // Determine URL to open
    let urlToOpen = '/';

    if (data && data.type === 'follower_notification' && data.ad_id) {
        // Open the ad detail page
        urlToOpen = `/ad/${data.ad_id}/${data.title_slug}/${data.state_slug}`;
    }

    // Open or focus the app
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(function(clientList) {
                // Check if app is already open
                for (let i = 0; i < clientList.length; i++) {
                    let client = clientList[i];
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }
                // If not open, open new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// resources/js/message-notification.js

export async function initializeMessageNotifications() {
    // Lazy load Echo and Pusher
    const Echo = (await import('laravel-echo')).default;
    const Pusher = (await import('pusher-js')).default;

    window.Pusher = Pusher;

    let token = document.querySelector('meta[name="csrf-token"]');

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY || '41a30e61ffd80a89fc68',
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'eu',
        forceTLS: true,
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': token ? token.content : '',
            }
        }
    });

    setupNotifications();
}

function setupNotifications() {
    if (!window.Laravel?.userId) {
        console.log("User not logged in, skipping notifications");
        return;
    }

    const userId = window.Laravel.userId;
    const notificationSound = new Audio(window.Laravel.soundUrl);
    const notificationIcon = window.Laravel.iconUrl;
    const UNREAD_URL = window.Laravel.unreadUrl;

    let previousCount = null;

    console.log('Initializing notifications for user:', userId);

    // Request notification permission (non-blocking)
    if ("Notification" in window && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
            console.log("Notification permission:", permission);
        });
    }

    // ✅ Listen for real-time messages (immediate - important for UX)
    if (userId) {
        window.Echo.private(`user.${userId}`)
            .listen('.new.message', (e) => {
                console.log("📩 New message received:", e.message);

                if (notificationSound) {
                    notificationSound.play().catch(err => console.warn("Sound failed:", err));
                }

                showNotification("📩 New Message", "You received a new message!");

                // Immediately update badge
                updateUnreadMessages();
            });
    }

    function updateUnreadMessages() {
        fetch(UNREAD_URL)
            .then(response => response.json())
            .then(data => {
                const count = data.count ?? data.unread_count ?? 0;
                const badges = document.querySelectorAll('.unread-badge');
                badges.forEach(badge => {
                    if (count > 0) {
                        badge.style.display = 'flex';
                        badge.textContent = count > 99 ? '99+' : count;
                    } else {
                        badge.style.display = 'none';
                    }
                });

                // Only show notification if count increased
                if (previousCount !== null && count > previousCount) {
                    showNotification("📩 New Message", `You have ${count} unread message(s).`);
                    if (notificationSound) {
                        notificationSound.play().catch(e => console.warn('Sound failed:', e));
                    }
                }

                previousCount = count;
            })
            .catch(error => {
                console.error("Unread message check failed:", error);
            });
    }

    function showNotification(title, body) {
        if ("Notification" in window && Notification.permission === "granted") {
            new Notification(title, {
                body: body,
                icon: notificationIcon
            });
        }
    }

    // ✅ DEFERRED: Wait until page is loaded before first API call
    if (document.readyState === 'complete') {
        // Page already loaded
        setTimeout(updateUnreadMessages, 500);
    } else {
        // Wait for page load
        window.addEventListener('load', () => {
            setTimeout(updateUnreadMessages, 500);
        });
    }

    // Continue polling every 10 seconds
    setInterval(updateUnreadMessages, 10000);
}

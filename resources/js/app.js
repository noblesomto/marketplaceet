// resources/js/app.js

// ✅ Keep lightweight essentials
import './bootstrap';
import './scroll';
import './lga';

// ✅ Lazy load SweetAlert2
window.Swal = {
    fire: async (options) => {
        const Swal = (await import('sweetalert2')).default;
        return Swal.fire(options);
    },
    confirm: async (title, text, confirmText = 'Yes', cancelText = 'No') => {
        const Swal = (await import('sweetalert2')).default;
        return Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText
        });
    },
    success: async (title, text) => {
        const Swal = (await import('sweetalert2')).default;
        return Swal.fire({
            title,
            text,
            icon: 'success'
        });
    },
    error: async (title, text) => {
        const Swal = (await import('sweetalert2')).default;
        return Swal.fire({
            title,
            text,
            icon: 'error'
        });
    }
};

// ✅ Lazy load features based on what's on the page
document.addEventListener('DOMContentLoaded', () => {
    // Load Trix only if there's a rich text editor
    if (document.querySelector('trix-editor')) {
        import('trix').then(() => {
            console.log('✅ Trix editor loaded');
        });
    }

    // ✅ UPDATED: Defer message notifications to prevent blocking
    if (window.Laravel?.userId) {
        // Use requestIdleCallback for optimal performance (loads when browser is idle)
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                import('./message-notification.js').then((module) => {
                    module.initializeMessageNotifications();
                    console.log('✅ Message notifications initialized');
                });
            }, { timeout: 2000 }); // Fallback: force load after 2s max
        } else {
            // Fallback for Safari and older browsers
            setTimeout(() => {
                import('./message-notification.js').then((module) => {
                    module.initializeMessageNotifications();
                    console.log('✅ Message notifications initialized');
                });
            }, 1000); // Load after 1 second
        }
    } else {
        console.log('ℹ️ User not logged in, skipping message notifications');
    }
});

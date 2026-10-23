<!-- Cookie Consent Popup -->
<div id="cookieConsent"
     class="hidden fixed bottom-24 md:bottom-8 md:left-4 inset-x-0 md:inset-x-auto md:w-1/4 z-[9999]
            bg-white/95 border-t border-gray-200 shadow-lg animate-slide-up">
    <div class="max-w-7xl mx-auto px-3 py-3 grid grid-cols-12 gap-3 items-start">
        <!-- Text Container -->
        <div class="col-span-10">
            <p id="cookieText"
               class="text-xs text-gray-700 leading-relaxed line-clamp-2 transition-all duration-300">
                We use cookies to personalize content, analyze performance, enhance security, and deliver ads relevant to your interests. By continuing to use this website, you consent to our use of cookies as described in our
                <a href="{{ route('cookie.policy') }}" class="underline font-medium text-secondary_dark hover:text-gray-900">
                    Cookies Policy
                </a>.
            </p>
            <button id="toggleCookieText"
                    class="text-xs underline text-secondary_dark hover:text-dark_green mt-1">
                More
            </button>
        </div>

        <!-- Actions -->
        <div class="col-span-2 flex flex-col md:flex-row items-center justify-end gap-2">
            <button id="acceptCookies"
                    class="bg-secondary_dark hover:bg-dark_green text-white text-sm font-semibold px-4 py-2 rounded-md uppercase transition">
                OK
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes slideUp {
        from { transform: translateY(100%); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }
    @keyframes slideDown {
        from { transform: translateY(0); opacity: 1; }
        to   { transform: translateY(100%); opacity: 0; }
    }
    .animate-slide-up { animation: slideUp 0.3s ease-out; }
    .animate-slide-down { animation: slideDown 0.3s ease-out; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cookieConsent = document.getElementById('cookieConsent');
    const acceptButton = document.getElementById('acceptCookies');
    const toggleButton = document.getElementById('toggleCookieText');
    const text = document.getElementById('cookieText');

    // Show popup if user hasn't accepted yet
    //if (!localStorage.getItem('cookieConsent')) {
       // cookieConsent.classList.remove('hidden');
    //}

    // Handle accept button
    acceptButton.addEventListener('click', function() {
        localStorage.setItem('cookieConsent', 'accepted');
        localStorage.setItem('cookieConsentDate', new Date().toISOString());
        cookieConsent.classList.remove('animate-slide-up');
        cookieConsent.classList.add('animate-slide-down');
        setTimeout(() => cookieConsent.classList.add('hidden'), 300);
    });

    // Handle see more/less toggle
    toggleButton.addEventListener('click', function(e) {
        e.preventDefault();
        const isExpanded = !text.classList.contains('line-clamp-2');

        if (isExpanded) {
            // Currently expanded, collapse it
            text.classList.add('line-clamp-2');
            toggleButton.textContent = 'See more';
        } else {
            // Currently collapsed, expand it
            text.classList.remove('line-clamp-2');
            toggleButton.textContent = 'See less';
        }
    });
});
</script>

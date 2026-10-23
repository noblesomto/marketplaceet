{{--
    App Install Banner — shown on Android and non-Safari iOS browsers only.
    Safari iOS is handled by the <meta name="apple-itunes-app"> tag in <head>.
    Re-appears after 30 days if previously dismissed.
--}}
<div id="app-install-banner" style="display:none" aria-label="Install our app" role="banner">
    <div class="flex items-center gap-3 px-3 py-2.5">
        <button
            onclick="dismissAppBanner()"
            class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 leading-none text-2xl"
            aria-label="Close app install banner">
            &times;
        </button>

        <img
            src="{{ asset('frontend/images/favicon.png') }}"
            alt="Marketplace Ethiopia app icon"
            class="w-11 h-11 rounded-xl object-cover flex-shrink-0">

        <div class="flex flex-col min-w-0">
            <span class="font-semibold text-gray-900 text-sm leading-tight">Marketplace Ethiopia</span>
            <span class="text-xs text-gray-500 leading-tight">Buy &amp; Sell Faster With Our App</span>
        </div>

        <a id="app-install-link"
           href="#"
           class="ml-auto flex-shrink-0 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-4 py-1.5 rounded-full transition-colors">
            Install
        </a>
    </div>
</div>

<style>
#app-install-banner {
    position: sticky;
    top: 0;
    z-index: 9999;
    width: 100%;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
/* Never show on desktop — belt-and-suspenders alongside UA check */
@media (min-width: 768px) {
    #app-install-banner { display: none !important; }
}
</style>

<script>
(function () {
    var IOS_URL     = 'https://apps.apple.com/us/app/marketplace-naija-buy-sell/id6753354778';
    var ANDROID_URL = 'https://play.google.com/store/apps/details?id=com.app.marketplacenaija';
    var DISMISS_KEY = 'appBannerDismissedAt';
    var THIRTY_DAYS = 30 * 24 * 60 * 60 * 1000;

    var ua        = navigator.userAgent || navigator.vendor || window.opera;
    var isAndroid = /android/i.test(ua);
    var isIOS     = /iphone|ipad|ipod/i.test(ua);

    // Chrome/Firefox/Edge on iOS embed "CriOS", "FxiOS", "EdgiOS" — everything else is Safari-engine
    var isSafariIOS = isIOS && !/CriOS|FxiOS|EdgiOS|OPiOS/.test(ua);

    // Desktop or non-mobile browser: do nothing
    if (!isAndroid && !isIOS) return;

    // iOS Safari: the <meta name="apple-itunes-app"> native banner handles it
    if (isSafariIOS) return;

    // Respect the 30-day dismissal window
    var dismissedAt = localStorage.getItem(DISMISS_KEY);
    if (dismissedAt && (Date.now() - parseInt(dismissedAt, 10)) < THIRTY_DAYS) return;

    var banner = document.getElementById('app-install-banner');
    var link   = document.getElementById('app-install-link');

    link.href = isAndroid ? ANDROID_URL : IOS_URL;
    banner.style.display = 'block';
})();

function dismissAppBanner() {
    var banner = document.getElementById('app-install-banner');
    banner.style.display = 'none';
    localStorage.setItem('appBannerDismissedAt', Date.now().toString());
}
</script>

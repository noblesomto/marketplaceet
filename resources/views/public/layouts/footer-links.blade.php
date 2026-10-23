
<section class="px-2 pt-5  hidden lg:block border-t-2 border-t-gray-300">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-base">
            <div class="flex flex-col">
                <h4 class="font-bold">Marketplace Ethiopia</h4>
                <ul class="flex flex-col space-y-1 mt-2">
                    <li><a href="/about-us" class="hover:text-secondary-200">About Us</a></li>
                    <li><a href="/career" class="hover:text-secondary-200">Career</a></li>
                    <li><a href="/privacy-policy" class="hover:text-secondary-200">Privacy Policy</a></li>
                    <li><a href="/cookie-policy" class="hover:text-secondary-200">Cookies Policy</a></li>
                    <li><a href="/billing-policy" class="hover:text-secondary-200">Billing Policy</a></li>
                    <li><a href="/copyright-policy" class="hover:text-secondary-200">Copyright Policy</a></li>
                    <li><a href="/sell-online" class="hover:text-secondary-200">Sell Online</a></li>
                    <li><a href="/advertise-with-us" class="hover:text-secondary-200">Advertise With Us</a></li>
                </ul>
            </div>

            <div class="flex flex-col">
                <h4 class="font-bold">Help & Support</h4>
                <ul class="flex flex-col space-y-1 mt-2">
                    <li><a href="/safety-tips" class="hover:text-secondary-200">Tips for your safety</a></li>
                    <li><a href="/our-terms" class="hover:text-secondary-200">Terms of Use</a></li>
                    <li><a href="/contact-us" class="hover:text-secondary-200">Contact Us</a></li>
                    <li><a href="/payments-refunds" class="hover:text-secondary-200">Payment & Refund</a></li>
                    <li><a href="/how-it-works" class="hover:text-secondary-200">How It Works</a></li>
                    <li><a href="https://wa.me/{{ preg_replace('/\D/', '', config('global.site_phone')) }}" target="_blank" class="hover:text-secondary-200">Chat Support</a></li>
                    <li><a href="/dmca-policy" class="hover:text-secondary-200">Copyright Infringement Policy</a></li>
                </ul>
            </div>

            <div class="flex flex-col">
                <h4 class="font-bold">Our Resources</h4>
                <ul class="flex flex-col space-y-1 mt-2">
                    <li><a href="/faq" class="hover:text-secondary-200">FAQ</a></li>
                    <li><a href="/blog" class="hover:text-secondary-200">Blog</a></li>
                    <li><a href="#" class="hover:text-secondary-200">Mobile Apps</a></li>
                </ul>
                <div class="flex flex-col gap-2 mt-1">
                    <a href="https://apps.apple.com/us/app/marketplace-naija-buy-sell/id6753354778" aria-label="Mobile app download">
                        <img class="w-24" alt="Mobile app download app store" src="{{ asset('frontend/images/app-store.svg') }}">
                    </a>
                    <a href="https://play.google.com/store/apps/details?id=com.app.marketplacenaija" aria-label="Mobile app download">
                        <img class="w-24" alt="Mobile app download play store" src="{{ asset('frontend/images/play-store.svg') }}">
                    </a>
                </div>
            </div>

            <div class="flex flex-col">
                <h4 class="font-bold">Social Media</h4>
                <ul class="flex flex-col space-y-1 mt-2">
                    <li><a href="https://www.facebook.com/MarketplaceNaijaOnline/" target="_blank" class="hover:text-secondary-200">Facebook</a></li>
                    <li><a href="https://www.instagram.com/marketplacenaija/" target="_blank" class="hover:text-secondary-200">Instagram</a></li>
                    <li><a href="https://www.tiktok.com/@marketplace.naija" target="_blank" class="hover:text-secondary-200">TikTok</a></li>
                    <li><a href="https://www.youtube.com/@marketplacenaija" target="_blank" class="hover:text-secondary-200">YouTube</a></li>
                    <li><a href="https://www.pinterest.com/marketplacenaija/" target="_blank" class="hover:text-secondary-200">Pinterest</a></li>
                    <li><a href="https://whatsapp.com/channel/0029Vb73O6n0G0XmPQpNIc47" target="_blank" class="hover:text-secondary-200">Whatsapp</a></li>
                </ul>
            </div>

        </div>
        

    @php $sisterSites = \App\Models\SisterSite::activeOrdered(); @endphp
    @if($sisterSites->isNotEmpty())
    <!--Sister Sites -->
    <div class="flex justify-center items-center gap-3 mt-5 pt-4 border-t-2 border-t-gray-300 flex-wrap">
        @foreach($sisterSites as $site)
            <a href="{{ $site->url }}" target="_blank" rel="noopener" title="{{ $site->country_name }}">
                <img src="{{ asset($site->flag) }}" alt="{{ $site->country_name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 hover:opacity-80">
            </a>
        @endforeach
    </div>
    @endif

    <!--Copy Rights -->
    <div class="flex justify-center gap-2 mt-5 pb-10 text-sm text-center {{ $sisterSites->isEmpty() ? 'border-t-2 border-t-gray-300' : '' }} pt-4">
        <div class=" ">
            <span class="font-semibold px-2">Marketplace Ethiopia. {{ date('Y ') }}</span>
        </div>
        <div class="">
            All rights reserved
        </div>

    </div>
   
    </div>
</section>


<div class="relative">

    <!-- Button to open the modal -->
    <button aria-label="Menu Button" id="openModal" class="m-1 md:m-4 px-4 py-2 text-gray-700 rounded">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
        </svg>
    </button>

    <!-- Overlay and Modal -->
    <div id="modalOverlay" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden z-40">
        <div id="leftModal"
             class="fixed inset-y-0 left-0 w-2/4 max-w-md bg-white shadow-lg z-50 transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col h-screen">

            <!-- Header -->
            <div class="p-5 border-b flex justify-between items-center bg-gray-50">
                <span class="font-bold text-gray-800">Menu</span>
                <button id="closeModal" class="text-gray-400 p-1">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-4">
                <nav class="space-y-1">
                    <a href="/" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-lg">Home</a>
                    <a href="/about-us" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-lg">About Us</a>
                    <a href="/how-it-works" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-lg">How It Works</a>
                    <a href="/blog" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-lg">Blog</a>
                    <a href="/privacy-policy" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-lg">Privacy Policy</a>
                    <a href="/cookie-policy" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-lg">Cookie Policy</a>
                    <a href="/our-terms" class="block px-4 py-2 text-gray-700 hover:bg-green-50 rounded-lg">Terms of Use</a>
                </nav>

                <div class="my-4 border-t border-gray-100"></div>
                <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Support</p>

                <nav class="space-y-1">
                    <a href="/faq" class="block px-4 py-2 text-sm text-gray-600">FAQ</a>
                    <a href="/safety-tips" class="block px-4 py-2 text-sm text-gray-600">Safety Tips</a>
                    <a href="/contact-us" class="block px-4 py-2 text-sm text-gray-600">Contact Us</a>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', config('global.site_phone')) }}" target="_blank" class="block px-4 py-2 text-sm text-green-600 font-bold">Chat Support</a>
                </nav>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t">
                @if(session()->get('user_id') == '')
                    <a href="/login" class="block w-full text-center py-2 bg-green-700 text-white rounded-xl font-bold">Login</a>
                @else
                    <a href="/user/index" class="block w-full text-center py-2 bg-gray-800 text-white rounded-xl font-bold">Dashboard</a>
                @endif
            </div>
            <div class="mt-10"></div>

        </div>
    </div>
</div>

<script>
    const openModalButton = document.getElementById('openModal');
    const closeModalButton = document.getElementById('closeModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const leftModal = document.getElementById('leftModal');

    openModalButton.addEventListener('click', () => {
        modalOverlay.classList.remove('hidden');
        leftModal.classList.remove('-translate-x-full');
        document.body.classList.add('overflow-hidden');
    });

    closeModalButton.addEventListener('click', () => {
        leftModal.classList.add('-translate-x-full');
        document.body.classList.remove('overflow-hidden');
        setTimeout(() => modalOverlay.classList.add('hidden'), 300);
    });

    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            leftModal.classList.add('-translate-x-full');
            document.body.classList.remove('overflow-hidden');
            setTimeout(() => modalOverlay.classList.add('hidden'), 300);
        }
    });
</script>

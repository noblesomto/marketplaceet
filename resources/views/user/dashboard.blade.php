@include('user.layouts.header')

<div class="flex h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed z-30 inset-y-0 left-0 w-64 bg-dark text-white transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="p-6">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <nav class="mt-6">
                <a href="#" class="block py-2.5 px-4 rounded hover:bg-gray-700">Dashboard</a>
                <a href="/my-ads" class="block py-2.5 px-4 rounded hover:bg-gray-700">My Listings</a>
                <a href="/profile" class="block py-2.5 px-4 rounded hover:bg-gray-700">Profile</a>
            </nav>
        </div>
    </div>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 z-20 hidden md:hidden"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <header class="p-4 bg-gray-100 shadow flex">
            <button id="menu-button" class="md:hidden bg-dark text-white p-2 rounded mr-5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
				  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
				</svg>
            </button>
            <h2 class="text-2xl"><a href="/"><img src="{{ asset('frontend/images/logo.svg') }}" class="h-9"></a></h2>
        </header>

        <main class="bg-white p-6">
            <p>Your main content goes here...</p>
        </main>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const menuButton = document.getElementById('menu-button');

    menuButton.addEventListener('click', function () {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });

    overlay.addEventListener('click', function () {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });
</script>



@include('user.layouts.footer')

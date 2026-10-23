<section class="mt-5 w-full bg-secondary_dark h-auto min-h-12 hidden lg:block">
    <div class="w-full max-w-7xl mx-auto px-4">
        <form action="/search" method="GET" class="py-2">
            <div class="grid grid-cols-12 gap-2 items-center">
                <!-- Search Input Section -->
                <div class="col-span-6 xl:col-span-5">
                    <div class="flex flex-col lg:flex-row gap-2">
                        <div class="w-full lg:flex-1">
                            <input class="appearance-none block w-full h-10 text-gray-700 border border-gray-200 rounded-lg lg:rounded-r-none py-2 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500 text-sm"
                                   id="grid-city"
                                   type="text"
                                   name="product"
                                   placeholder="What are you looking for?">
                        </div>
                        <div class="w-full lg:flex-1">
                            <div class="relative">
                                <label for="category" class="sr-only">Category</label>
                                <select name="category"
                                        class="block appearance-none w-full h-10 bg-gray-200 border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded-lg lg:rounded-l-none leading-tight focus:outline-none focus:bg-gray-200 focus:border-gray-500 text-sm">
                                    <option value="">Select Category</option>
                                    @foreach(getCategories() as $category)
                                        <option value="{{ $category->id }}">{{ $category->category }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Section -->
                <div class="col-span-3 xl:col-span-2">
                    <div class="relative">
                        @php
                            // Keys = DB values (must match adverts.state exactly), Values = display labels
                            $states = \App\Models\State::orderBy('name')->pluck('name', 'name');
                        @endphp
                        <label for="location" class="sr-only">Location</label>
                        <select name="location"
                                class="block appearance-none w-full h-10 bg-gray-200 border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-gray-200 focus:border-gray-500 text-sm">
                            <option value="" selected="selected">Location</option>
                            @foreach ($states as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="col-span-1">
                    <button type="submit"
                            class="bg-dark_green hover:bg-white hover:text-dark_green font-semibold text-white w-full h-10 px-2 rounded-lg text-sm transition-colors duration-200 flex items-center justify-center">
                        <i class="fa fa-search lg:mr-1"></i>
                        <span class="hidden xl:inline">Search</span>
                    </button>
                </div>

                <!-- Action Buttons Section -->
                <div class="col-span-2 xl:col-span-4">
                    <div class="flex items-center justify-end space-x-2">

                        <!-- User Menu -->
                        <div class="group relative cursor-pointer">
                            <a class="flex items-center px-2 py-2 text-sm font-semibold text-white hover:bg-white hover:text-dark_green rounded-lg transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <span class="hidden xl:inline">My Account</span>
                            </a>

                            <!-- Dropdown Menu -->
                            @if(session()->get('user_id') =='')
                            <div class="absolute right-0 mt-1 w-40 bg-gray-100 text-gray-800 shadow-xl rounded-lg opacity-0 scale-95 invisible                                        group-hover:opacity-100 group-hover:scale-100 group-hover:visible
                                        transition-all duration-200 origin-top z-10">
                                <a href="/about-us" class="block border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm">About Us</a>
                                <a href="/how-it-works" class="block border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm">How It Works</a>
                                <a href="/faq" class="block border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm">FAQ</a>
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', config('global.site_phone')) }}" target="_blank" class="block py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm">Chat Support</a>
                                <a href="/contact-us" class="block py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm">Contact Us</a>
                            </div>
                            @else
                            <div class="absolute right-0 mt-1 w-40 bg-gray-100 text-gray-800 shadow-xl rounded-lg
                                        opacity-0 scale-95 invisible
                                        group-hover:opacity-100 group-hover:scale-100 group-hover:visible
                                        transition-all duration-200 origin-top z-10">
                                <a href="/user/index" class="flex items-center gap-2 border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm"><i class="bi bi-speedometer2 text-xs"></i><span>Dashboard</span></a>
                                <a href="/user/my-ads" class="flex items-center gap-2 border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm"><i class="bi bi-badge-ad text-xs"></i><span>My Ads</span></a>
                                <a href="/user/favourites" class="flex items-center gap-2 border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm"><i class="bi bi-heart text-xs"></i><span>Wishlists</span></a>
                                <a href="/user/payment" class="flex items-center gap-2 border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm"><i class="bi bi-box2 text-xs"></i><span>Purchases</span></a>
                                <a href="/user/messages" class="flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2 relative">
                                    <span><i class="bi bi-envelope"></i></span>
                                    <span>Messages</span>
                                    <!-- Notification badge -->
                                    <div class="unread-badge absolute top-0 right-10 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs"
                                         style="display: none;">
                                        0
                                    </div>
                                </a>
                                <a href="/user/feedbacks" class="flex items-center gap-2 border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm"><i class="bi bi-chat-right-dots text-xs"></i><span>Feedback</span></a>
                                <a href="/user/profile" class="flex items-center gap-2 border-b border-gray-100 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm"><i class="bi bi-person text-xs"></i><span>Profile</span></a>
                                <a href="/user/logout" class="flex items-center gap-2 py-2 px-2 font-semibold text-gray-500 hover:text-black rounded text-sm"><i class="bi bi-box-arrow-right text-xs"></i><span>Logout</span></a>
                            </div>
                            @endif
                        </div>

                        <!-- Notifications -->
                        <div class="relative">
                            @php
                                $count = getUserNotificationCount();
                            @endphp
                            <a aria-label="Notifications" href="/user/notifications" class="flex items-center justify-center p-2 text-white hover:bg-white hover:text-dark_green rounded-lg transition-colors duration-200">
                                @if($count >= 1)
                                    <div class="absolute -top-1 -right-1 bg-dark_green text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold">
                                        {{ $count > 9 ? '9+' : $count }}
                                    </div>
                                @endif
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

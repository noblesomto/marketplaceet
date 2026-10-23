<section class="bg-white py-3 border-t border-t-gray-300 fixed bottom-0 left-0 w-full block lg:hidden">
	<div class="flex justify-between">
		<a href="/">
			<div class="flex flex-col items-center mx-2">
				<div>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
				</div>
				<div class="font-semibold text-xs">Home</div>
			</div>
		</a>

		<a href="/user/favourites">
			<div class="flex flex-col items-center mx-2">
				<div>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
				</div>
				<div class="font-semibold text-xs">Favourites</div>
			</div>
		</a>
		
		<a href="/user/post-ad" class="flex flex-col items-center mx-2">
			<div class=" relative -mt-10 w-14 h-14 bg-dark_green rounded-full flex items-center justify-center shadow-xl">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="white"
                         viewBox="0 0 24 24"
                         stroke="white"
                         stroke-width="1.5"
                         class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>

                </div>

            </div>
            <div class="font-semibold text-xs mt-1">Sell</div>
		</a>
		<a href="/user/messages">
		    <div class="flex flex-col items-center mx-2 relative">
		        <!-- Notification badge - hidden by default if count is 0 -->
		        <div class="unread-badge absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs" 
				     style="display: none;">
				    0
				</div>
		        <div>
		            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
		                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
		            </svg>
		        </div>
		        <div class="font-semibold text-xs">Messages</div>
		    </div>
		</a>
		<a href="/user/profile">
			<div class="flex flex-col items-center mx-2">
				<div>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 hover:size-6 hover:fill-dark_green">
	                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
	                </svg>
				</div>
				<div class="font-semibold text-xs">Profile</div>
			</div>
		</a>
	</div>
</section>

<script>
	const openSearchButton = document.getElementById('openSearch');
	const closeSearchButton = document.getElementById('closeSearch');
	const searchOverlay = document.getElementById('searchOverlay');

	// Function to open the search overlay
	function openSearchOverlay() {
	    searchOverlay.classList.remove('hidden');
	}

	// Add click event to both open buttons
	openSearchButton.addEventListener('click', openSearchOverlay);


	closeSearchButton.addEventListener('click', () => {
	    searchOverlay.classList.add('hidden');
	});

	// Close search on overlay click
	searchOverlay.addEventListener('click', (e) => {
	    if (e.target === searchOverlay) {
	        searchOverlay.classList.add('hidden');
	    }
	});        
</script>
<audio id="notificationSound" src="{{ asset('frontend/sound/new-message.mp3') }}" preload="auto"></audio>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userId = {!! json_encode(session('user_id')) !!};
        const notificationSound = document.getElementById('notificationSound');
        const notificationIcon = "{{ asset('frontend/images/message-icon.png') }}";
        let previousCount = null; // Changed from 0 to null

        // Request notification permission
        if ("Notification" in window && Notification.permission !== 'granted') {
            Notification.requestPermission().then(permission => {
                console.log("Notification permission:", permission);
            });
        }

        // Listen for real-time messages via Pusher
        if (userId) {
            Echo.private(`user.${userId}`)
                .listen('.new.message', (e) => {
                    console.log("📩 New message received:", e.message);
                    console.log("Subscribing to: user." + userId);

                    // Play sound
                    if (notificationSound) {
                        notificationSound.play().catch(err => console.warn("Sound failed:", err));
                    }

                    // Show browser notification
                    showNotification("📩 New Message", "You received a new message!");

                    // Update unread message badge
                    updateUnreadMessages();
                });
        }

        // Polling fallback for unread count
        function updateUnreadMessages() {
            fetch("{{ url('/unread-messages-count') }}")
                .then(response => response.json())
                .then(data => {
                    const badges = document.querySelectorAll('.unread-badge');

                    // Update UI
                    badges.forEach(badge => {
                        if (data.count > 0) {
                            badge.style.display = 'flex';
                            badge.textContent = data.count;
                        } else {
                            badge.style.display = 'none';
                        }
                    });

                    // Notify only if count increased
                    if (previousCount !== null && data.count > previousCount) {
                        showNotification("📩 New Message", `You have ${data.count} unread message(s).`);
                        if (notificationSound) {
                            notificationSound.play().catch(e => console.warn('Sound failed:', e));
                        }
                    }

                    previousCount = data.count;
                })
                .catch(error => {
                    console.error("Unread message check failed:", error);
                });
        }

        function showNotification(title, body) {
            if ("Notification" in window) {
                if (Notification.permission === "granted") {
                    new Notification(title, {
                        body: body,
                        icon: notificationIcon
                    });
                } else if (Notification.permission !== "denied") {
                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") {
                            new Notification(title, {
                                body: body,
                                icon: notificationIcon
                            });
                        }
                    });
                }
            }
        }

        // Initial call + polling every 10 seconds
        updateUnreadMessages();
        setInterval(updateUnreadMessages, 10000);
    });
</script>



</body>
</html>

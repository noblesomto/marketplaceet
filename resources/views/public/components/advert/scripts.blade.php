<!-- Share Modal HTML -->
<div id="shareModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" id="shareOverlay"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Share this Ad</h3>
                <button id="closeModalShare" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex justify-center gap-4 py-4">
                 @include('public.components.social-share', ['url' => url()->current(), 'title' => $ad->ad_title])
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('shareBtn').addEventListener('click', async () => {

    const shareTitle = {!! json_encode($ad->ad_title ?? '') !!};
    const shareText = {!! json_encode($ad->meta_description ?? Str::limit(strip_tags($ad->description ?? ''), 160)) !!};
    const shareUrl = window.location.href;

    if (navigator.share) {
        try {
            await navigator.share({
                title: shareTitle,
                text: shareText,
                url: shareUrl
            });
        } catch (err) {
            console.log('Share cancelled', err);
        }
    } else {
        alert("Sharing is not supported on this device.");
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wishlist-toggle').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const adId = this.dataset.adId;
            const svg = this.querySelector('svg');

            fetch(`/user/add-wishlist/${adId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle icon
                    if (data.in_wishlist) {
                        svg.classList.add('fill-red-500', 'text-red-500');
                        svg.classList.remove('fill-none');
                        this.title = 'Remove from Wishlist';

                        // SweetAlert for added to wishlist
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Wishlist!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        }).then(() => {
                            location.reload(); // Refresh page after alert
                        });
                    } else {
                        svg.classList.remove('fill-red-500', 'text-red-500');
                        svg.classList.add('fill-none');
                        this.title = 'Add to Wishlist';

                        // SweetAlert for removed from wishlist
                        Swal.fire({
                            icon: 'info',
                            title: 'Removed from Wishlist',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        }).then(() => {
                            location.reload(); // Refresh page after alert
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Error SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please login to save this item to your favorites',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
            });
        });
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;



        // --- 2. MODALS ---
        function toggleModal(modalID, show) {
            const el = document.getElementById(modalID);
            if(el) {
                if(show) el.classList.remove('hidden');
                else el.classList.add('hidden');
            }
        }

        document.getElementById('openModalShare')?.addEventListener('click', () => toggleModal('shareModal', true));
        document.getElementById('closeModalShare')?.addEventListener('click', () => toggleModal('shareModal', false));
        document.getElementById('shareOverlay')?.addEventListener('click', () => toggleModal('shareModal', false));

        // --- 3. FOLLOW LOGIC ---
        const followBtn = document.getElementById('followButton');
        if (followBtn) {
            const ownerId = followBtn.dataset.userId;

            // Initial Check
            fetch(`/api/check-following/${ownerId}`).then(r => r.json()).then(data => {
                updateFollowUI(data.isFollowing);
            }).catch(e => console.error(e));

            followBtn.addEventListener('click', async () => {
                try {
                    const res = await fetch('/api/toggle-follow', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                        body: JSON.stringify({ followee_id: ownerId })
                    });
                    if(res.status === 401) window.location.href = '/login';
                    const data = await res.json();
                    updateFollowUI(data.isFollowing);
                    Swal.fire({icon: 'success', title: data.isFollowing ? 'Followed' : 'Unfollowed', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false});
                } catch(e) { Swal.fire('Error', 'Something went wrong', 'error'); }
            });

            function updateFollowUI(isFollowing) {
                if(isFollowing) {
                    followBtn.innerHTML = 'Unfollow Seller';
                    followBtn.classList.replace('bg-transparent', 'bg-dark_green');
                    followBtn.classList.replace('text-dark_green', 'text-dark_green');
                } else {
                    followBtn.innerHTML = 'Follow Seller';
                    followBtn.classList.replace('bg-dark_green', 'bg-transparent');
                    followBtn.classList.replace('text-white', 'text-dark_green');
                }
            }
        }

        // --- 4. SHOW CONTACT ---
        const contactBtn = document.getElementById('showContact');
        if(contactBtn) {
            contactBtn.addEventListener('click', function() {
                const box = document.getElementById('contactPhone');
                box.classList.toggle('hidden');
                this.innerText = box.classList.contains('hidden') ? 'Call' : 'Hide Number';
            });
        }
    });
</script>
<script>
    const openBtn = document.getElementById('openNavModal');
    const closeBtn = document.getElementById('closeNavModal');
    const modal = document.getElementById('modal');
    const overlay = document.getElementById('overlay');

    function openModal() {
      overlay.classList.remove('hidden');
      setTimeout(() => {
        overlay.classList.add('opacity-100');
        modal.classList.remove('translate-y-full');
      }, 10);
    }

    function closeModal() {
      modal.classList.add('translate-y-full');
      overlay.classList.remove('opacity-100');
      setTimeout(() => {
        overlay.classList.add('hidden');
      }, 300);
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);
  </script>

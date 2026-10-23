@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6  mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        My Notifications
    </div>

    <div class="max-w-4xl mx-auto mt-4 rounded-lg">
    @forelse($groupedNotifications as $dateGroup => $notifications)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-3 px-2">{{ $dateGroup }}</h3>
            @foreach($notifications as $row)
                @if($row->advert)
                    <a href="{{ url($row->advert->state_slug . '/' . $row->advert->title_slug .'/'. $row->advert->ad_id) }}" class="block">
                        <div class="flex p-2 border-b bg-white mb-2 hover:bg-gray-50 space-x-2">
                            <div class="flex-[30%] sm:flex-[15%]">
                                <img class="w-20 h-20 object-cover rounded-full"
                                     src="{{ $row->advert->getFirstMediaUrl('images', 'thumbnail') ?: asset('frontend/images/default.png') }}"
                                     onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';">
                            </div>
                            <div class="flex-[70%] sm:flex-[85%]">
                                <div class="flex justify-between">
                                    <strong>{{ $row->type }}</strong>
                                    <div class="flex space-x-2">
                                        <div>
                                            @if ($row->is_read == 0)
                                                <i class="bi bi-envelope" title="Unread Notification"></i>
                                            @else
                                                <i class="bi bi-envelope-open" title="Read Notification"></i>
                                            @endif
                                        </div>

                                        <span>
                                            <a href="javascript:void(0);"
                                               class="deleteNotificationBtn"
                                               data-id="{{ $row->id }}">
                                                <i class="bi bi-trash text-red-500" title="Delete Notification"></i>
                                            </a>
                                        </span>

                                    </div>
                                </div>
                                {{ $row->message }}<br>
                                <small class="text-gray-500">{{ $row->created_at->format('g:i A') }}</small>
                            </div>
                        </div>
                    </a>
                @else
                    <div class="flex p-2 border-b bg-white mb-2 opacity-50">
                        <div class="flex-[70%] sm:flex-[85%]">
                            <strong>{{ $row->type }}</strong><br>
                            {{ $row->message }}<br>
                            <small class="text-gray-500">Advert no longer available</small>
                        </div>
                    </div>
                @endif

            @endforeach
        </div>
    @empty
        <div class="p-2 text-gray-500">No notifications</div>
    @endforelse
</div>
</section>

<script>
document.querySelectorAll('.deleteNotificationBtn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();

        let id = this.dataset.id;
        let notificationItem = this.closest('.flex');
        let dateGroupContainer = notificationItem.closest('.mb-6');

        Swal.fire({
            title: "Are you sure?",
            text: "This notification will be deleted permanently.",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {

            if (!result.isConfirmed) return;

            fetch(`/user/delete-notification/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {

                    // Fade out
                    notificationItem.style.transition = "opacity 0.3s";
                    notificationItem.style.opacity = "0";

                    setTimeout(() => {
                        notificationItem.remove();

                        // Remove empty group
                        if (dateGroupContainer.querySelectorAll('.flex').length === 0) {
                            dateGroupContainer.remove();
                        }

                        // SweetAlert success
                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: "Notification deleted successfully.",
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {
                            // Refresh page after success alert
                            location.reload();
                        });

                    }, 300);
                }
            });
        });
    });
});
</script>


@include('user.layouts.footer')

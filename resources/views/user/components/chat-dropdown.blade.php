<div x-data="{ open: false }" class="relative dropdown inline-block">
    <!-- Dropdown Button -->
    <button
        type="button"
        @click="open = !open"
        class="dropdown-button inline-flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
        aria-haspopup="true"
        :aria-expanded="open"
    >
        <i class="bi bi-three-dots-vertical text-xl text-gray-600"></i>
    </button>

    <!-- Dropdown Menu -->
    <div
        x-cloak
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5"
    >
        @php
            $user_id = session('user_id');
            $currentUser = $user_id ? \App\Models\User::where('user_id', $user_id)->first() : null;
            $isBlocked = false;
            $isArchived = false;

            if ($currentUser && isset($receiver) && isset($advert)) {
                $isBlocked = $currentUser->hasBlocked($receiver->user_id, $advert->id ?? null);
                $isArchived = $currentUser->hasArchivedConversation($advert->id ?? null, $receiver->user_id);
            }
        @endphp
        <div class="space-y-2 py-2">
            <!-- Block / Unblock User -->
            <form
                action="{{ $isBlocked ? '/user/unblock' : '/user/block' }}"
                method="POST"
                class="block"
            >
                @csrf
                @if($isBlocked)
                    @method('DELETE')
                @endif

                <input type="hidden" name="blocked_id" value="{{ $receiver->user_id }}">
                <input type="hidden" name="advert_id" value="{{ $advert->id ?? '' }}">

                <button
                    type="button"
                    onclick="confirmAction(this, '{{ $isBlocked ? 'Unblock this user?' : 'Block this user from messaging you?' }}')"
                    class="w-full text-left px-4 py-2 text-sm {{ $isBlocked ? 'text-green-700 hover:bg-green-50 hover:text-green-900' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-150"
                >
                    {{ $isBlocked ? 'Unblock User' : 'Block User' }}
                </button>
            </form>

            <!-- Report User -->
            <a
                href="/report-user/{{ $receiver->user_id }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors duration-150"
            >
                Report User
            </a>

            <!-- Archive / Unarchive Chat -->
            <form
                action="{{ $isArchived ? route('messages.unarchive') : route('messages.archive') }}"
                method="POST"
                class="block"
            >
                @csrf
                @if($isArchived)
                    @method('DELETE')
                @endif

                <input type="hidden" name="advert_id" value="{{ $advert->id ?? '' }}">
                <input type="hidden" name="other_user_id" value="{{ $receiver->user_id }}">

                <button
                    type="button"
                    onclick="confirmAction(this, '{{ $isArchived ? 'Unarchive this conversation? It will reappear in your message list.' : 'Archive this conversation? It will be hidden from your message list.' }}')"
                    class="w-full text-left px-4 py-2 text-sm {{ $isArchived ? 'text-blue-700 hover:bg-blue-50 hover:text-blue-900' : 'text-orange-700 hover:bg-orange-50 hover:text-orange-900' }} transition-colors duration-150"
                >
                    {{ $isArchived ? 'Unarchive Chat' : 'Archive Chat' }}
                </button>
            </form>
        </div>
    </div>
</div>



<!-- Reusable confirmation function -->
<script>
function confirmAction(button, message) {
    Swal.fire({
        title: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, continue',
        cancelButtonText: 'Cancel',
        background: '#fff',
        customClass: {
            popup: 'rounded-2xl shadow-lg',
            title: 'text-gray-800 text-lg font-medium',
            confirmButton: 'px-4 py-2 rounded-lg text-white font-medium',
            cancelButton: 'px-4 py-2 rounded-lg text-white font-medium'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest('form').submit();
        }
    });
}
</script>

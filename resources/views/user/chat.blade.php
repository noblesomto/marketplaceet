@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<div class="chat-page-wrapper">

    {{-- ─── LEFT SIDEBAR (desktop only) ─────────────────────────────── --}}
    <aside class="chat-left-panel">

        {{-- Advert card --}}
        <div class="p-5 border-b border-gray-100">
            <a href="{{ url($advert->state_slug . '/' . $advert->title_slug . '/' . $advert->ad_id) }}"
               class="block group">
                <div class="relative rounded-xl overflow-hidden mb-4 bg-gray-100">
                    <img src="{{ $advert->getFirstMediaUrl('images', 'optimized') ?: asset('frontend/images/default.png') }}"
                         alt="{{ $advert->ad_title }}"
                         class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300">
                    @if($advert->sold === 'Yes')
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                            <span class="bg-red-500 text-white font-bold text-sm px-4 py-1.5 rounded-full tracking-wide uppercase">Sold</span>
                        </div>
                    @endif
                </div>

                <h3 class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2 mb-2 group-hover:text-dark_green transition-colors">
                    {{ $advert->ad_title }}
                </h3>

                @if($advert->contact_price === 'yes')
                    <p class="text-sm text-gray-500 italic">Contact for price</p>
                @elseif($advert->price)
                    <p class="text-dark_green font-bold text-xl">
                        Br {{ number_format((float) preg_replace('/[^\d.]/', '', $advert->price), 0) }}
                        @if($advert->price_type && $advert->price_type !== 'Fixed')
                            <span class="text-xs font-normal text-gray-500 ml-1">· {{ $advert->price_type }}</span>
                        @endif
                    </p>
                @endif

                @if($advert->state)
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                        <i class="bi bi-geo-alt"></i> {{ $advert->lga ? $advert->lga . ', ' : '' }}{{ $advert->state }}
                    </p>
                @endif
            </a>

            <a href="{{ url($advert->state_slug . '/' . $advert->title_slug . '/' . $advert->ad_id) }}"
               class="mt-4 flex items-center justify-center gap-2 w-full px-4 py-2 border border-dark_green text-dark_green text-sm font-semibold rounded-lg hover:bg-green-50 transition-colors">
                View Full Listing <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        {{-- Contact info --}}
        <div class="p-5 border-b border-gray-100 flex-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                {{ $advert->user_id == $user->user_id ? 'Buyer' : 'Seller' }}
            </p>
            @php
                $otherName   = $receiver->name ?? 'User';
                $nameParts   = explode(' ', trim($otherName));
                $sideInitial = strtoupper(substr($nameParts[0], 0, 1));
                if (count($nameParts) > 1) {
                    $sideInitial .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
                }
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <span class="text-gray-700 font-semibold text-sm">{{ $sideInitial }}</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ $otherName }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Marketplace member</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="p-5 space-y-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Options</p>
            @php
                $sideUser     = $user;
                $sideBlocked  = $isBlocked;
                $sideArchived = $sideUser->hasArchivedConversation($advert->id ?? null, $receiver->user_id);
            @endphp

            {{-- Block / Unblock --}}
            <form action="{{ $sideBlocked ? '/user/unblock' : '/user/block' }}" method="POST">
                @csrf
                @if($sideBlocked) @method('DELETE') @endif
                <input type="hidden" name="blocked_id" value="{{ $receiver->user_id }}">
                <input type="hidden" name="advert_id"  value="{{ $advert->id ?? '' }}">
                <button type="button"
                        onclick="confirmAction(this, '{{ $sideBlocked ? 'Unblock this user?' : 'Block this user from messaging you?' }}')"
                        class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $sideBlocked ? 'text-green-700 hover:bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="bi {{ $sideBlocked ? 'bi-person-check' : 'bi-slash-circle' }}"></i>
                    {{ $sideBlocked ? 'Unblock User' : 'Block User' }}
                </button>
            </form>

            {{-- Report --}}
            <a href="/report-user/{{ $receiver->user_id }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="bi bi-flag"></i> Report User
            </a>

            {{-- Archive / Unarchive --}}
            <form action="{{ $sideArchived ? route('messages.unarchive') : route('messages.archive') }}" method="POST">
                @csrf
                @if($sideArchived) @method('DELETE') @endif
                <input type="hidden" name="advert_id"     value="{{ $advert->id ?? '' }}">
                <input type="hidden" name="other_user_id" value="{{ $receiver->user_id }}">
                <button type="button"
                        onclick="confirmAction(this, '{{ $sideArchived ? 'Unarchive this conversation? It will reappear in your message list.' : 'Archive this conversation? It will be hidden from your message list.' }}')"
                        class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $sideArchived ? 'text-blue-700 hover:bg-blue-50' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                    <i class="bi {{ $sideArchived ? 'bi-archive' : 'bi-archive' }}"></i>
                    {{ $sideArchived ? 'Unarchive Chat' : 'Archive Chat' }}
                </button>
            </form>
        </div>

    </aside>

    {{-- ─── RIGHT CHAT PANEL ───────────────────────────────────────── --}}
    <div class="chat-right-panel">

        {{-- HEADER --}}
        <div class="chat-header-bar">
            <div class="flex items-center gap-3 px-4 py-3">

                {{-- Back (mobile only) --}}
                <a href="/user/messages" class="flex-shrink-0 lg:hidden">
                    <i class="bi bi-chevron-left text-dark_green text-xl"></i>
                </a>

                {{-- Advert thumbnail + other person name --}}
                <a href="{{ url($advert->state_slug . '/' . $advert->title_slug . '/' . $advert->ad_id) }}"
                   class="flex items-center gap-3 min-w-0 flex-1">
                    <img src="{{ $advert->getFirstMediaUrl('images', 'thumbnail') ?: asset('frontend/images/default.png') }}"
                         alt="{{ $advert->ad_title }}"
                         class="w-10 h-10 rounded-full object-cover flex-shrink-0 border border-gray-200">
                    <div class="min-w-0">
                        <h2 class="font-semibold text-gray-900 text-sm truncate leading-tight">
                            {{ $advert->owner->name }}
                        </h2>
                        <p class="text-xs text-gray-500 truncate leading-tight">
                            {{ Str::limit($advert->ad_title, 45) }}
                        </p>
                    </div>
                </a>

                {{-- Dropdown (block / archive / report — mobile & desktop) --}}
                <div class="flex-shrink-0">
                    @include('user.components.chat-dropdown')
                </div>
            </div>
        </div>

        {{-- MESSAGES --}}
        <div id="chat-box" class="chat-box">
            @foreach($messages as $msg)
                @php
                    $msgSender      = $msg->sender_id == $user->user_id ? $user : $receiver;
                    $isCurrentUser  = $msg->sender_id == $user->user_id;
                    $nameParts      = explode(' ', trim($msgSender->name));
                    $initials       = strtoupper(substr($nameParts[0], 0, 1));
                    if (count($nameParts) > 1) {
                        $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
                    }
                @endphp

                <div class="chat-message flex mb-3 {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">

                    {{-- Avatar (receiver side) --}}
                    @if(!$isCurrentUser)
                        <div class="flex-shrink-0 mr-2 self-end">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-700 text-xs font-semibold">{{ $initials }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Bubble + timestamp --}}
                    <div class="max-w-[72%] lg:max-w-[65%]">
                        <div class="message-bubble px-4 py-2.5 rounded-2xl text-sm leading-relaxed
                            {{ $isCurrentUser
                                ? 'bg-secondary-200 text-gray-800 rounded-br-md'
                                : 'bg-white text-gray-800 rounded-bl-md shadow-sm border border-gray-100' }}">

                            @if($msg->message_content)
                                <p>{{ $msg->message_content }}</p>
                            @endif

                            @if($msg->images->count())
                                <div class="mt-2 grid gap-1.5
                                    {{ $msg->images->count() === 1 ? 'grid-cols-1' :
                                       ($msg->images->count() === 2 ? 'grid-cols-2' : 'grid-cols-2 sm:grid-cols-3') }}">
                                    @foreach($msg->images as $messageImage)
                                        <div class="relative group">
                                            <img src="{{ $messageImage->getFirstMediaUrl('message_images', 'thumbnail') }}"
                                                 alt="Shared image"
                                                 class="w-full h-28 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                                 onclick="enlargeImage('{{ $messageImage->getFirstMediaUrl('message_images', 'large') }}')">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-gray-400 mt-1 {{ $isCurrentUser ? 'text-right pr-1' : 'text-left pl-1' }}">
                            {{ \Carbon\Carbon::parse($msg->created_at)->format('M j, g:i A') }}
                        </p>
                    </div>

                    {{-- Avatar (current user side) --}}
                    @if($isCurrentUser)
                        <div class="flex-shrink-0 ml-2 self-end">
                            <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center">
                                <span class="text-white text-xs font-semibold">{{ $initials }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- FOOTER --}}
        <div class="chat-footer-bar">

            {{-- Sold status bar --}}
            @if($advert->sold === 'Yes' && $advert->user_id != $user->user_id)
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="bg-green-50 border border-green-200 text-green-800 text-xs font-medium rounded-lg px-3 py-1.5">
                        @if(!isset($payment) || empty($payment))
                            <i class="bi bi-bag-check mr-1"></i> Item Sold
                        @elseif($payment->buyer_status === 'pending')
                            <a href="/user/order-details/{{ $payment->id }}" class="flex items-center gap-1">
                                <i class="bi bi-check-all"></i> Confirm Received
                            </a>
                        @else
                            <span class="capitalize"><i class="bi bi-check-circle mr-1"></i>{{ $payment->buyer_status }}</span>
                        @endif
                    </span>
                    <a href="/report-ad/{{ $advert->id }}"
                       class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs font-medium rounded-lg px-3 py-1.5 flex items-center gap-1">
                        <i class="bi bi-exclamation-triangle"></i> Report Issue
                    </a>
                </div>
            @endif

            {{-- Offer toggle (buyers only, first message) --}}
            @if($advert->user_id != $user->user_id && count($messages) == 0)
                <div class="space-y-3 mb-3">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" id="toggleSwitch" class="sr-only peer">
                            <div class="w-10 h-5 bg-gray-300 rounded-full transition-colors duration-200 peer-checked:bg-dark_green"></div>
                            <div class="dot absolute left-0.5 top-0.5 bg-white w-4 h-4 rounded-full transition-transform duration-200 peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm text-gray-600">Send the seller an offer?</span>
                    </label>
                    <div id="extraInputWrapper" class="hidden">
                        <label for="extraInput" class="block text-sm font-medium text-gray-700 mb-1">Offer Amount</label>
                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:border-dark_green focus-within:ring-1 focus-within:ring-dark_green">
                            <span class="px-3 py-2 bg-gray-50 text-gray-500 border-r border-gray-300 text-sm">Br </span>
                            <input type="text"
                                   id="extraInput"
                                   name="amount"
                                   placeholder="0"
                                   maxlength="11"
                                   pattern="[0-9]*"
                                   disabled
                                   class="flex-1 px-3 py-2 text-sm outline-none bg-white">
                        </div>
                    </div>
                </div>
            @endif

            {{-- Message form --}}
            <form id="chat-form">
                @csrf
                <input type="hidden" name="advert_id"   value="{{ $advert->id }}">
                <input type="hidden" name="receiver_id"  value="{{ $receiver->user_id }}">

                {{-- Message input row --}}
                <div id="send-message" class="flex items-end gap-2 bg-gray-50 rounded-xl border border-gray-200 px-3 py-2 focus-within:border-dark_green focus-within:ring-1 focus-within:ring-dark_green transition-all">

                    {{-- Camera / file attach --}}
                    <div class="flex-shrink-0 pb-1 relative">
                        <input type="file"
                               id="fileUpload"
                               class="hidden"
                               multiple
                               accept="image/*"
                               onchange="updateFileCount(this)">
                        <label for="fileUpload" class="cursor-pointer text-gray-400 hover:text-dark_green transition-colors block">
                            <i class="bi bi-paperclip text-xl"></i>
                        </label>
                        <span id="fileBadge"
                              class="hidden absolute -top-2 -right-2 bg-dark_green text-white text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center">
                            0
                        </span>
                    </div>

                    {{-- Textarea --}}
                    <div class="flex-1">
                        <textarea id="chat-input"
                                  name="message"
                                  rows="1"
                                  class="auto-resize w-full bg-transparent outline-none text-sm text-gray-700 placeholder-gray-400 resize-none pt-1"
                                  placeholder="Type your message…"
                                  autocomplete="off"></textarea>
                    </div>

                    {{-- Send button --}}
                    <div class="flex-shrink-0 pb-1">
                        <button type="submit" id="sendBtn"
                                class="w-9 h-9 rounded-full bg-dark_green hover:bg-secondary_dark flex items-center justify-center transition-colors">
                            <i class="bi bi-send-fill text-white text-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>{{-- end chat-right-panel --}}
</div>{{-- end chat-page-wrapper --}}


{{-- Image lightbox --}}
<div id="image-overlay"
     class="fixed inset-0 bg-black/85 hidden items-center justify-center z-50 p-4"
     onclick="this.classList.add('hidden'); this.classList.remove('flex');">
    <img id="overlay-image" src="" alt="Enlarged image"
         class="max-w-full max-h-full rounded-xl shadow-2xl object-contain">
</div>


<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleSwitch');
    const wrapper = document.getElementById('extraInputWrapper');
    const amountInput = document.getElementById('extraInput');
    const chatInput = document.getElementById('chat-input');
    const chatForm = document.getElementById('chat-form');
    const sendBtn = document.getElementById('sendBtn');
    const fileUpload = document.getElementById('fileUpload');
    const fileBadge = document.getElementById('fileBadge');

    // Image preview container (you might want to add this to your HTML)
    let imagePreviewContainer = document.getElementById('imagePreviewContainer');
    if (!imagePreviewContainer) {
        imagePreviewContainer = document.createElement('div');
        imagePreviewContainer.id = 'imagePreviewContainer';
        imagePreviewContainer.className = 'image-preview-container hidden flex flex-wrap gap-2 p-2 bg-gray-50 rounded-lg mb-2';
        chatForm.insertBefore(imagePreviewContainer, document.getElementById('send-message'));
    }

    let selectedFiles = [];

    function fmtCurrency(val) {
        const n = parseFloat(String(val).replace(/,/g, ''));
        if (isNaN(n)) return val;
        return n.toLocaleString('en-ET');
    }

    if (toggle && wrapper && amountInput) {
        // Toggle switch functionality
        toggle.addEventListener('change', function () {
            if (toggle.checked) {
                wrapper.classList.remove('hidden');
                amountInput.removeAttribute('disabled');
                amountInput.setAttribute('required', 'required');
                if (amountInput.value) {
                    chatInput.value = `I'd like to offer Br ${fmtCurrency(amountInput.value)}`;
                }
            } else {
                wrapper.classList.add('hidden');
                amountInput.removeAttribute('required');
                amountInput.setAttribute('disabled', 'disabled');
                amountInput.value = '';
                if (chatInput.value.startsWith("I'd like to offer")) {
                    chatInput.value = '';
                }
            }
        });

        // Update chat input on amount change
        amountInput.addEventListener('input', function() {
            if (toggle.checked && amountInput.value) {
                chatInput.value = `I'd like to offer Br ${fmtCurrency(amountInput.value)}`;
            }
        });

        // Set correct state on page load
        if (toggle.checked) {
            wrapper.classList.remove('hidden');
            amountInput.removeAttribute('disabled');
            amountInput.setAttribute('required', 'required');
            if (amountInput.value) {
                chatInput.value = `I'd like to offer Br ${fmtCurrency(amountInput.value)}`;
            }
        } else {
            wrapper.classList.add('hidden');
            amountInput.setAttribute('disabled', 'disabled');
        }
    }

    // Handle file selection
    if (fileUpload && fileBadge) {
        fileUpload.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            selectedFiles = files;
            updateFileDisplay();
        });
    }

    // Update file display and badge
    function updateFileDisplay() {
        const fileCount = selectedFiles.length;

        // Update badge
        if (fileCount > 0) {
            fileBadge.textContent = fileCount;
            fileBadge.classList.remove('hidden');
        } else {
            fileBadge.classList.add('hidden');
        }

        // Update preview container
        imagePreviewContainer.innerHTML = '';

        if (fileCount > 0) {
            imagePreviewContainer.classList.remove('hidden');

            selectedFiles.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'relative inline-block';
                        previewDiv.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" class="w-16 h-16 object-cover rounded-lg border">
                            <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold hover:bg-red-600" onclick="removeImage(${index})">
                                ×
                            </button>
                        `;
                        imagePreviewContainer.appendChild(previewDiv);
                    };
                    reader.readAsDataURL(file);
                } else {
                    // For non-image files, show file name
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'relative inline-block bg-gray-200 p-2 rounded-lg';
                    previewDiv.innerHTML = `
                        <div class="flex items-center">
                            <i class="bi bi-file-earmark text-gray-600 mr-2"></i>
                            <span class="text-sm truncate max-w-20">${file.name}</span>
                            <button type="button" class="ml-2 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs font-bold hover:bg-red-600" onclick="removeImage(${index})">
                                ×
                            </button>
                        </div>
                    `;
                    imagePreviewContainer.appendChild(previewDiv);
                }
            });
        } else {
            imagePreviewContainer.classList.add('hidden');
        }
    }

    // Global function to remove image
    window.removeImage = function(index) {
        selectedFiles.splice(index, 1);
        updateFileDisplay();

        // Update the file input
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileUpload.files = dt.files;
    };

    // Auto-resize textarea
    if (chatInput) {
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }

    // Handle form submission
    if (chatForm && chatInput) {
        $(chatForm).on('submit', function (e) {
            e.preventDefault();
            let message = chatInput.value.trim();
            const hasFiles = selectedFiles.length > 0;
            // Check if we have content to send (message or files)
            if (message === '' && !hasFiles) {
                return; // Don't send empty messages
            }
            // Update message for offer
            if (toggle && toggle.checked && amountInput.value && !message.includes(fmtCurrency(amountInput.value))) {
                message = `I'd like to offer Br ${fmtCurrency(amountInput.value)}`;
                chatInput.value = message;
            }
            // Disable button + show loading
            sendBtn.disabled = true;
            const originalIcon = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="bi bi-hourglass-split text-white text-sm"></i>';
            // Create FormData for file upload
            const formData = new FormData();
            // Add form fields
            formData.append('_token', $('input[name="_token"]').val());
            formData.append('advert_id', $('input[name="advert_id"]').val());
            formData.append('receiver_id', $('input[name="receiver_id"]').val());
            formData.append('message', message);
            // Add amount if applicable
            if (toggle && toggle.checked && amountInput.value) {
                formData.append('amount', amountInput.value);
            }
            // Add files
            selectedFiles.forEach((file, index) => {
                formData.append(`images[${index}]`, file);
            });
            $.ajax({
                url: '{{ route("chat.sendMessage", ["advertId" => $advert->id, "receiverId" => $receiver->user_id]) }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    // Reset form
                    chatInput.value = '';
                    chatInput.style.height = 'auto';
                    // Reset file upload
                    fileUpload.value = '';
                    selectedFiles = [];
                    updateFileDisplay();
                    // Reset offer toggle
                    if (toggle && toggle.checked) {
                        amountInput.value = '';
                        wrapper.classList.add('hidden');
                        amountInput.setAttribute('disabled', 'disabled');
                        amountInput.removeAttribute('required');
                        toggle.checked = false;
                    }
                    loadMessages(true);

                    // No success alert - silent success
                },
                error: function (xhr, status, error) {
                    console.error('Error sending message:', error);

                    // Show user-friendly error message based on status code
                    let errorTitle = 'Error';
                    let errorMessage = 'Error sending message';

                    if (xhr.status === 422) {
                        // Validation errors
                        errorTitle = 'Validation Error';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                    } else if (xhr.status === 401) {
                        // Unauthorized
                        errorTitle = 'Unauthorized';
                        errorMessage = 'You are not authorized to perform this action';
                    } else if (xhr.status === 403) {
                        // Forbidden
                        errorTitle = 'Access Denied';
                        errorMessage = 'You do not have permission to send this message';
                    } else if (xhr.status === 404) {
                        // Not found
                        errorTitle = 'Not Found';
                        errorMessage = 'The conversation could not be found';
                    } else if (xhr.status === 500) {
                        // Server error
                        errorTitle = 'Server Error';
                        errorMessage = 'An internal server error occurred. Please try again later';
                    }
                    // Handle your custom HTTP errors
                    else if (xhr.status === 460) {
                        errorTitle = 'Insufficient Credits';
                        errorMessage = xhr.responseJSON?.message || 'You do not have enough credits';
                    } else if (xhr.status === 461) {
                        errorTitle = 'Chat Limit Reached';
                        errorMessage = xhr.responseJSON?.message || 'You have reached your daily chat limit';
                    } else if (xhr.status === 462) {
                        errorTitle = 'User Blocked';
                        errorMessage = xhr.responseJSON?.message || 'You cannot send messages to this user';
                    } else if (xhr.status === 463) {
                        errorTitle = 'File Too Large';
                        errorMessage = xhr.responseJSON?.message || 'The file is too large to upload';
                    }
                    // Fallback to server message if available
                    else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    showSweetAlert('error', errorTitle, errorMessage);
                },
                complete: function () {
                    // Re-enable button and restore original icon
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = originalIcon;
                }
            });
        });
    }

    function showSweetAlert(type, title, message) {
        Swal.fire({
            icon: type,
            title: title,
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    // Load chat messages
    function loadMessages(forceScroll) {
        const chatBox = $('#chat-box');
        if (chatBox.length) {
            $.ajax({
                url: '{{ route("chat.show", ["advertId" => $advert->id, "receiverId" => $receiver->user_id]) }}',
                method: 'GET',
                success: function (data) {
                    const wasAtBottom = forceScroll || chatBox.scrollTop() + chatBox.outerHeight() >= chatBox[0].scrollHeight - 10;
                    chatBox.html($(data).find('#chat-box').html());

                    if (wasAtBottom) {
                        chatBox.scrollTop(chatBox[0].scrollHeight);
                    }
                },
                error: function() {
                    console.error('Error loading messages');
                }
            });
        }
    }

    // Poll for messages
    if ($('#chat-box').length) {
        // Scroll to bottom on page load (server-rendered messages)
        const chatBox = document.getElementById('chat-box');
        requestAnimationFrame(function () {
            chatBox.scrollTop = chatBox.scrollHeight;
        });

        setInterval(loadMessages, 5000);

        // Refresh messages on page load and force scroll to bottom
        setTimeout(function () {
            loadMessages(true);
        }, 500);
    }

    // Handle drag and drop for files
    if (chatForm) {
        chatForm.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('drag-over');
        });

        chatForm.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
        });

        chatForm.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');

            const files = Array.from(e.dataTransfer.files);
            if (files.length > 0) {
                // Filter for allowed file types (images)
                const allowedFiles = files.filter(file => file.type.startsWith('image/'));
                if (allowedFiles.length > 0) {
                    selectedFiles = [...selectedFiles, ...allowedFiles];
                    updateFileDisplay();

                    // Update the file input
                    const dt = new DataTransfer();
                    selectedFiles.forEach(file => dt.items.add(file));
                    fileUpload.files = dt.files;
                }
            }
        });
    }

    // Keyboard shortcuts
    if (chatInput) {
        chatInput.addEventListener('keydown', function(e) {
            // Submit on Ctrl+Enter or Cmd+Enter
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    }
});
</script>

<script>
function enlargeImage(src) {
    const overlay = document.getElementById('image-overlay');
    const overlayImg = document.getElementById('overlay-image');
    overlayImg.src = src;
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
}

document.getElementById('image-overlay').addEventListener('click', function () {
    this.classList.add('hidden');
    this.classList.remove('flex');
});
</script>

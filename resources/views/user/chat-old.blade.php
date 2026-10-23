@include('user.layouts.header')
@include('user.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('user.layouts.search')
<style>
    /* Tailwind-compatible switch animation */
    input:checked ~ div.dot {
        transform: translateX(100%);
    }
    input:checked ~ div.w-11 {
        background-color: #3b82f6; /* Tailwind blue-500 */
    }
</style>
    <style>

        .sticky-form {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: white;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }


        /* Custom dark green color */
        .bg-dark_green { background-color: #0f5132; }
        .text-dark_green { color: #0f5132; }
        .bg-secondary-200 { background-color: #e5e7eb; }

        /* Auto-resize textarea */
        .auto-resize {
            resize: none;
            overflow: hidden;
            min-height: 40px;
            max-height: 120px;
        }

        /* Enhanced Image Modal Styles */
#imageModal {
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease-out;
}

#imageModal.hidden {
    animation: fadeOut 0.3s ease-out;
}

#modalImage {
    transition: opacity 0.2s ease-in-out;
    max-width: 90vw;
    max-height: 90vh;
    object-fit: contain;
}

#prevBtn, #nextBtn {
    transition: all 0.2s ease-in-out;
    backdrop-filter: blur(10px);
}

#prevBtn:hover, #nextBtn:hover {
    background-color: rgba(0, 0, 0, 0.7);
    transform: scale(1.1);
}

/* Close button enhancement */
#imageModal .absolute.top-5.right-5 {
    transition: all 0.2s ease-in-out;
    backdrop-filter: blur(10px);
    padding: 8px;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.4);
}

#imageModal .absolute.top-5.right-5:hover {
    background-color: rgba(0, 0, 0, 0.7);
    transform: scale(1.1);
}

/* Animation keyframes */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

/* Loading spinner for images */
#modalImage[src=""], #modalImage:not([src]) {
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 50 50"><path fill="%23fff" d="M25 5A20 20 0 1 0 25 45A20 20 0 1 0 25 5m0 4A16 16 0 1 1 25 41A16 16 0 1 1 25 9z" opacity=".3"/><path fill="%23fff" d="M25 5A20 20 0 0 1 45 25h-4A16 16 0 0 0 25 9V5z"><animateTransform attributeName="transform" dur="1s" repeatCount="indefinite" type="rotate" values="0 25 25;360 25 25"/></path></svg>') center center no-repeat;
    background-size: 40px 40px;
}
    </style>


<div class="chat-container">
        <section class="max-w-2xl mx-auto text-sm pt-2">
            <div class="flex flex-col">
                <div class="pb-5">
                    <!-- Chat Header -->
                    <div class="flex items-center mb-2 p-2 bg-white rounded-lg gap-4">
                        <a href="/user/messages"><i class="bi bi-chevron-left text-dark_green text-xl"></i></a>
                        <a href="{{ url($advert->state_slug . '/' . $advert->title_slug .'/'. $advert->ad_id) }}" class="flex items-center">
                            <div class="mr-2">
                                <img class="w-12 h-12 rounded-full bg-gray-300" src="{{  asset('uploads/images/'.$advert->firstImage->image) }}" alt="{{ $advert->ad_title }}">
                            </div>
                            <div class="flex-col">
                                <h4 class="font-semibold text-lg">{{ $advert->owner->name }}</h4>
                                <h6 class="text-base -mt-2 text-sm lg:text-base">{{ $advert->ad_title }}</h6>
                            </div>
                        </a>
                    </div>

                    <!-- Chat Messages -->
                    <div id="chat-box" class="chat-box overflow-y-auto rounded-lg mb-2 max-h-[350px] sm:max-h-[360px] md:max-h-[380px] lg:max-h-[500px] bg-white p-3">
                        @foreach($messages as $msg)
                            @php
                                // Get the sender information
                                $sender = $msg->sender_id == $user->user_id ? $user : $receiver;
                                $isCurrentUser = $msg->sender_id == $user->user_id;

                                // Generate initials
                                $nameParts = explode(' ', trim($sender->name));
                                $initials = strtoupper(substr($nameParts[0], 0, 1));
                                if (count($nameParts) > 1) {
                                    $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
                                }

                                // Avatar colors based on user
                                $avatarColor = $isCurrentUser ? 'bg-gray-500' : 'bg-gray-200';
                            @endphp

                            <div class="flex mb-4 {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
                                {{-- Avatar (left side for others, right side for current user) --}}
                                @if(!$isCurrentUser)
                                    <div class="flex-shrink-0 mr-1">
                                        <div class="w-10 h-10 rounded-full {{ $avatarColor }} flex items-center justify-center shadow-sm">
                                            <span class="text-gray-800 text-sm font-semibold">{{ $initials }}</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Message Content --}}
                                <div class="max-w-xs sm:max-w-md lg:max-w-lg">

                                    {{-- Message Bubble --}}
                                    <div class="rounded-2xl px-4 py-3 {{ $isCurrentUser
                                        ? 'bg-gray-200 text-gray-800 rounded-br-md'
                                        : 'bg-gray-50 text-gray-800 rounded-bl-md' }}">

                                        {{-- Text Content --}}
                                        @if($msg->message_content)
                                            <div class="text-sm leading-relaxed">{{ $msg->message_content }}</div>
                                        @endif

                                        {{-- Images --}}
                                        @if($msg->images->count())
                                            <div class="grid gap-2 {{ $msg->message_content ? 'mt-3' : '' }}
                                                {{ $msg->images->count() == 1 ? 'grid-cols-1' :
                                                   ($msg->images->count() == 2 ? 'grid-cols-2' :
                                                   'grid-cols-2 sm:grid-cols-3') }}">
                                                @foreach($msg->images as $index => $img)
                                                  <div class="relative group">
                                                    <img
                                                      src="{{ asset('uploads/chat/' . $img->image_path) }}"
                                                      alt="Shared image"
                                                      class="w-full h-24 sm:h-28 object-cover rounded-lg cursor-pointer transition-all duration-200 ease-in-out hover:scale-[1.02] hover:shadow-md"
                                                      {{-- pass a real JS array with @json --}}
                                                      onclick='openImageModal(@json($msg->images->pluck("image_path")), {{ $index }})'
                                                    >
                                                    {{-- IMPORTANT: don’t block clicks --}}
                                                    <div class="pointer-events-none absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-all duration-200 rounded-lg"></div>
                                                  </div>
                                                @endforeach

                                            </div>
                                        @endif
                                    </div>

                                    {{-- Timestamp --}}
                                    <div class="text-xs text-gray-500 mt-1 {{ $isCurrentUser ? 'text-right mr-1' : 'text-left ml-1' }}">
                                        {{ \Carbon\Carbon::parse($msg->created_at)->format('M j, g:i A') }}
                                    </div>
                                </div>

                                {{-- Avatar (right side for current user) --}}
                                @if($isCurrentUser)
                                    <div class="flex-shrink-0 ml-1">
                                        <div class="w-10 h-10 rounded-full {{ $avatarColor }} flex items-center justify-center shadow-sm">
                                            <span class="text-white text-sm font-semibold">{{ $initials }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </section>
    </div>


    <!-- Sticky Chat Form -->
    <div class="fixed bottom-0 inset-x-0 z-50 mx-auto max-w-2xl bg-white border-t border-gray-200 shadow-md">
        <form id="chat-form" class="max-w-2xl mx-auto p-4">
            @csrf
            <input type="hidden" name="advert_id" value="{{ $advert->id }}">
            <input type="hidden" name="receiver_id" value="{{ $receiver->user_id }}">

            @if($advert->user_id == $user->user_id)

            @else
            <div class="space-y-4 mt-4">
                <!-- Toggle Switch -->
                <label class="flex items-center cursor-pointer">
                    <!-- Switch -->
                    <div class="relative">
                        <input type="checkbox" id="toggleSwitch" class="sr-only">
                        <div class="w-11 h-6 bg-gray-300 rounded-full shadow-inner transition"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                    </div>
                    <!-- Label -->
                    <span class="ml-3 text-gray-700">Send the seller an offer?</span>
                </label>

                <!-- Conditionally Visible Input -->
                <div id="extraInputWrapper" class="hidden">
                    <label for="extraInput" class="block text-base font-medium text-gray-700">Enter Amount</label>
                    <input
                        type="text"
                        id="extraInput"
                        name="amount"
                        placeholder="Enter Amount"
                        maxlength="11"
                        pattern="[0-9]*"
                        disabled
                        class="mt-1 block h-10 w-full rounded-l-lg border border-gray-300 focus:outline-none focus:border-blue-400 px-2"
                    >
                </div>
            </div>
            @endif

            @if($advert->sold=="Yes" && $advert->user_id != $user->user_id)
                <div class="flex justify-between my-2">
                    <span class="bg-green-100 rounded-lg p-2">
                        @if($payment->buyer_status =="pending")
                            <a href="/payment/mark-received/{{ $payment->id }}" onclick="return confirm('Are you sure you want to confirm Received?');"><i class="bi bi-check-all"></i> Mark Received</a>
                        @else
                            <span class="capitalize">{{ $payment->buyer_status }}</span>
                        @endif
                    </span>
                    <span class="bg-yellow-100 rounded-lg p-2"><a href="/report-ad/{{ $advert->id }}"><i class="bi bi-exclamation-triangle"></i> Report an Issue</a> </span>
                </div>
            @endif

            <!-- Message Input -->
            <div id="send-message" class="flex items-center bg-gray-50 p-2 rounded-lg border">
                <div class="flex-shrink-0 mr-2">
                    <div class="relative inline-block">
                        <!-- Hidden file input -->
                        <input
                            type="file"
                            id="fileUpload"
                            class="hidden"
                            multiple
                            accept="image/*"
                            onchange="updateFileCount(this)"
                        >

                        <!-- Icon trigger -->
                        <label for="fileUpload" class="cursor-pointer">
                            <i class="bi bi-camera text-2xl"></i>
                        </label>

                        <!-- Badge -->
                        <span id="fileBadge"
                            class="hidden absolute -top-2 -right-2 bg-yellow-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                            0
                        </span>
                    </div>
                </div>

                <div class="flex-1 mr-2">
                    <textarea
                        id="chat-input"
                        name="message"
                        rows="1"
                        class="auto-resize w-full p-2 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-400 text-gray-700 resize-none"
                        placeholder="Type your message..."
                        autocomplete="off"
                    ></textarea>
                </div>

                <div class="flex-shrink-0">
                    <button type="submit" id="sendBtn" class="p-2">
                        <i class="bi bi-send-fill text-2xl text-dark_green"></i>
                    </button>
                </div>
            </div>

        </form>
    </div>

<!-- Image Modal -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50">
    <!-- Close button -->
    <span class="absolute top-5 right-5 text-white text-3xl cursor-pointer" onclick="closeImageModal()"><i class="bi bi-x-circle"></i></span>

    <!-- Prev button -->
    <button id="prevBtn" class="absolute left-5 text-white text-4xl font-bold px-3 py-1 bg-black bg-opacity-40 rounded-full"
            onclick="prevImage()"><i class="bi bi-chevron-left"></i></button>

    <!-- Image -->
    <img id="modalImage" src="" class="max-w-full max-h-full rounded-lg shadow-lg">

    <!-- Next button -->
    <button id="nextBtn" class="absolute right-5 text-white text-4xl font-bold px-3 py-1 bg-black bg-opacity-40 rounded-full"
            onclick="nextImage()"><i class="bi bi-chevron-right"></i></button>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    if (toggle && wrapper && amountInput) {
        // Toggle switch functionality
        toggle.addEventListener('change', function () {
            if (toggle.checked) {
                wrapper.classList.remove('hidden');
                amountInput.removeAttribute('disabled');
                amountInput.setAttribute('required', 'required');
                if (amountInput.value) {
                    chatInput.value = `Would you accept Br ${amountInput.value}`;
                }
            } else {
                wrapper.classList.add('hidden');
                amountInput.removeAttribute('required');
                amountInput.setAttribute('disabled', 'disabled');
                amountInput.value = '';
                if (chatInput.value.startsWith('Would you accept')) {
                    chatInput.value = '';
                }
            }
        });

        // Update chat input on amount change
        amountInput.addEventListener('input', function() {
            if (toggle.checked && amountInput.value) {
                chatInput.value = `Would you accept Br ${amountInput.value}`;
            }
        });

        // Set correct state on page load
        if (toggle.checked) {
            wrapper.classList.remove('hidden');
            amountInput.removeAttribute('disabled');
            amountInput.setAttribute('required', 'required');
            if (amountInput.value) {
                chatInput.value = `Would you accept Br ${amountInput.value}`;
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
            if (toggle && toggle.checked && amountInput.value && !message.includes(amountInput.value)) {
                message = `Would you accept Br ${amountInput.value}`;
                chatInput.value = message;
            }

            // Disable button + show loading
            sendBtn.disabled = true;
            const originalIcon = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="bi bi-hourglass-split text-2xl text-gray-400"></i>';

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

                    loadMessages();
                },
                error: function (xhr, status, error) {
                    console.error('Error sending message:', error);

                    // Show user-friendly error message
                    let errorMessage = 'Error sending message';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }

                    alert(errorMessage);
                },
                complete: function () {
                    // Re-enable button and restore original icon
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = originalIcon;
                }
            });
        });
    }

    // Load chat messages
    function loadMessages() {
        const chatBox = $('#chat-box');
        if (chatBox.length) {
            $.ajax({
                url: '{{ route("chat.show", ["advertId" => $advert->id, "receiverId" => $receiver->user_id]) }}',
                method: 'GET',
                success: function (data) {
                    const wasAtBottom = chatBox.scrollTop() + chatBox.outerHeight() >= chatBox.prop('scrollHeight') - 10;
                    chatBox.html($(data).find('#chat-box').html());

                    // Only auto-scroll if user was already at bottom
                    if (wasAtBottom) {
                        chatBox.scrollTop(chatBox.prop('scrollHeight'));
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
        setInterval(loadMessages, 5000);

        // Load messages on page load
        setTimeout(loadMessages, 1000);
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

// CSS for drag and drop visual feedback
const style = document.createElement('style');
style.textContent = `
    .drag-over {
        border: 2px dashed #3b82f6;
        background-color: rgba(59, 130, 246, 0.05);
    }

    .image-preview-container {
        max-height: 120px;
        overflow-y: auto;
    }

    .image-preview-container::-webkit-scrollbar {
        height: 4px;
    }

    .image-preview-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 2px;
    }

    .image-preview-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    .image-preview-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
`;
document.head.appendChild(style);
</script>



<script>
  const chatBox = document.getElementById('chat-box');
  
  // Function to check and update padding
  function updateChatBoxPadding() {
    if (chatBox.innerHTML.trim() === '') {
      chatBox.classList.remove('p-2');
    } else {
      chatBox.classList.add('p-2');
    }
  }
  
  // Initial check
  updateChatBoxPadding();
  

</script>
<script>
const IMAGE_BASE = @json(asset('uploads/chat'));

window.images = [];
window.currentIndex = 0;

window.openImageModal = function(imgArray, index) {
  window.images = imgArray;        // now a real JS array
  window.currentIndex = index;
  showImage();
  document.getElementById('imageModal').classList.remove('hidden');
};

function showImage() {
  const src = IMAGE_BASE + '/' + window.images[window.currentIndex];
  document.getElementById('modalImage').src = src;
}

window.closeImageModal = function() {
  document.getElementById('imageModal').classList.add('hidden');
};

window.prevImage = function() {
  window.currentIndex = (window.currentIndex - 1 + window.images.length) % window.images.length;
  showImage();
};

window.nextImage = function() {
  window.currentIndex = (window.currentIndex + 1) % window.images.length;
  showImage();
};

// Optional: keyboard controls
document.addEventListener('keydown', (e) => {
  const modalOpen = !document.getElementById('imageModal').classList.contains('hidden');
  if (!modalOpen) return;
  if (e.key === 'Escape') closeImageModal();
  if (e.key === 'ArrowLeft') prevImage();
  if (e.key === 'ArrowRight') nextImage();
});
</script>




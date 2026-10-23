@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')


<section class="w-full md:w-4/6 mx-auto">
  <div class=" my-5 hidden lg:block">
    <img class="object-cover w-full h-64" src="{{ asset('frontend/images/ads.jpg') }}">
  </div>

  <div class="grid grid-cols-6 gap-3">
        
        <div class="col-span-6 md:col-span-4">
            <div class="chat-container max-w-3xl mx-auto mt-10">
                <div id="message-box" class="bg-gray-200 p-4 h-96 overflow-y-auto">
                    <!-- Messages will be loaded here via AJAX -->
                </div>

                <div class="mt-4 flex">
                    <textarea id="message-input" class="w-3/4 p-2 border mr-2 rounded-lg" placeholder="Type your message..."></textarea>
                    <button id="send-button" class="btn btn-secondary text-white py-2 px-4 mt-2 w-1/4 flex justify-center items-center">
                        <span class="mr-1">Send</span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-span-6 md:col-span-2">
          <div>@include('public.components.advert.sidebar')</div>
        </div>
  </div>


</section>


<script>
  window.onload  = function()
  {
    let adId = {{ $ad->ad_id }};  // Make sure you pass the roomId to the blade file
    let userId = {{ $user->user_id }}; // The current user sending the message
    let adOwner = {{ $ad->user_id }};

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

     // Function to load messages
    function loadMessages() {
        fetch(`/messages/${adId}/${adOwner}`)
            .then(response => response.json())
            .then(data => {
                let messageBox = document.getElementById('message-box');
                messageBox.innerHTML = ''; // Clear the box
                data.forEach(message => {
                    let messageClass = (message.user_id == userId) ? 'bg-blue-500 text-white' : 'bg-gray-300';
                    messageBox.innerHTML += `
                        <div class="mb-2 p-2 ${messageClass} rounded">
                            <strong></strong> <span class="whitespace-pre-wrap break-words">${escapeHtml(message.message)}</span>
                        </div>
                    `;
                });
                messageBox.scrollTop = messageBox.scrollHeight; // Scroll to bottom
            });
    }


    // Handle message form submission
    document.getElementById('send-button').addEventListener('click', function() {
        let messageInput = document.getElementById('message-input');
        let message = messageInput.value;

        axios.post('/messages', {
            ad_id: adId,
            ad_owner: adOwner,
            message: message
        }).then(response => {
            messageInput.value = ''; 
            loadMessages();
        });
    });

    // Listen for new messages
    Echo.channel(`chat-room.${adId}`)
        .listen('.message.sent', (e) => {
            appendMessage(e.message);
        });

    function appendMessage(message,user) {
        const messagesDiv = document.getElementById('messages');
        const newMessage = document.createElement('div');
        newMessage.classList.add('mb-2');
        const span = document.createElement('span');
        span.className = 'whitespace-pre-wrap break-words';
        span.textContent = message.message || '';
        newMessage.appendChild(span);
        messagesDiv.appendChild(newMessage);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Load messages initially
    loadMessages();

    // Set interval to load messages every 5 seconds
    setInterval(loadMessages, 5000);
    

  }

</script>


@include('public.layouts.footer')



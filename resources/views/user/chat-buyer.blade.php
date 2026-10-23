@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6  mx-auto text-sm mt-10">
    <div class="grid grid-cols-1 md:grid-cols-10 gap-y-2">
        <div class="col-span-10 md:col-span-6 px-2">
            <div class="flex flex-col bg-white p-3">
                <div class="flex items-center">
                    <a href="/advert/{{ $ad->id }}/{{ $ad->title_slug }}" class="flex items-center">
                        <div class="mr-2"><img class="w-10 h-10 rounded-full" src="{{  asset('uploads/images/'.$ad->firstImage->image) }}"> </div>
                        <div><h4 class="font-semibold text-lg">{{ $ad->ad_title }}</h4> </div>
                    </a>
                </div>
                <div class="chat-container  mt-2">
  
                    <div id="message-box" class="bg-gray-50 p-4 h-96 overflow-y-auto">
                        <!-- Messages will be loaded here via AJAX -->
                    </div>

                    <div class="mt-4 flex">
                        <textarea id="message-input" class="w-3/4 p-2 border mr-2 rounded-lg" placeholder="Type your message..."></textarea>
                        <button id="send-button" class="btn btn-secondary text-white  mt-2 w-1/4 flex justify-center  items-center">
                            <span class="mr-1 hidden sm:block">Send</span>
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-4 px-2">
            @include('user.components.buyer-sidebar')
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script>
  window.onload  = function()
  {
    let adId = {{ $ad->id }};  // Make sure you pass the roomId to the blade file
    let userId = {{ $user->user_id }}; // The current user sending the message
    let adOwner = {{ $ad->user_id }};

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

     // Function to load messages
    function loadMessages() {
        fetch(`/my-messages/${adId}`)
            .then(response => response.json())
            .then(data => {
                let messageBox = document.getElementById('message-box');
                messageBox.innerHTML = ''; // Clear the box
                data.forEach(message => {
                    let messageClass = (message.sender_id == userId) ? 'bg-dark_green text-white' : 'bg-secondary-200';
                    let date = new Date(message.created_at);
                    let formattedDate = dayjs(message.created_at).format('MMM D, YYYY h:mm ');
                    messageBox.innerHTML += `
                        <div class="mb-2 p-1 ${messageClass} text-sm rounded">
                            <div class="flex flex-col">
                                <div class="">
                                    <strong class="whitespace-pre-wrap break-words">${escapeHtml(message.message_content)}</strong>
                                </div>
                                <div class="font-semibold text-xs ">
                                    <div class="w-full flex justify-end">
                                        ${formattedDate}
                                    </div>
                                </div>
                            </div>
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


    // Load messages initially
    loadMessages();

    // Set interval to load messages every 5 seconds
    setInterval(loadMessages, 5000);
    

  }

</script>


@include('user.layouts.footer')

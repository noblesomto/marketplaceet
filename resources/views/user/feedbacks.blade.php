@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2 flex justify-between">
        <span>Feedbacks</span>
        <div class="space-x-4">
        	<a title="Logout" href="/user/logout" ><i class="bi bi-box-arrow-right text-lg lg:text-3xl"></i></a>
            <a title="Feedbacks" href="/user/feedbacks" ><i class="bi bi-chat-right-dots text-lg lg:text-3xl"></i></a>
        	<a title="Settings" href="/user/settings"><i class="bi bi-gear text-lg lg:text-3xl"></i></a>
        </div>
    </div>

    <div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 md:mt-10 mb-20 rounded-lg">
    	<div class="w-full bg-white shadow p-3">
            <div class="my-2 flex justify-between">
            <div class="font-bold space-x-1">
                <span><i class="bi bi-chat-dots"></i></span>
               <span> {{ get_user_feedback_averages($user->user_id)['count'] }} Feedback(s)</span>
            </div>
            <div>
                @php
                    $avgRating = round(get_user_feedback_averages($user->user_id)['rating']);
                    $maxRating = 5;
                @endphp

                <div class="flex items-center">
                    <!-- Full stars -->
                    @for($i = 1; $i <= $avgRating; $i++)
                        <span class="text-xl text-yellow-400">★</span>
                    @endfor

                    <!-- Empty stars -->
                    @for($i = $avgRating + 1; $i <= $maxRating; $i++)
                        <span class="text-xl text-gray-300">★</span>
                    @endfor

                    <!-- Optional: Display numeric value -->
                    <span class="ml-2 font-semibold text-gray-600">{{ $avgRating }} of {{ $maxRating }}</span>
                </div>
            </div>

        </div>
			<div class="flex flex-col">
                @forelse($feedbacks as $row)
                    <div class="mb-2">
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <div class="w-10 h-10 rounded-full bg-dark_green flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">
                                        {{ strtoupper(substr($row->user->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $row->user->name)[1] ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <span class="text-lg font-semibold">{{ $row->user->name }}</span>
                            </div>
                            <div class="mt-2 text-base">
                                {{ $row->message }}
                            </div>
                        </div>
                        <div><span>{{ date('d/m/y', strtotime($row->created_at)) }}</span></div>
                    </div>
                @empty
                  <div class="flex flex-col mb-4 items-center bg-white">
                        <span>
                            <img width="100" height="100" src="{{ asset('frontend/images/no-feedback.png') }}" alt="No Feed backs"/>
                        </span>
                        <span class="text-center">
                            There are no Feedbacks yet.<br>
                            Ask your customers to leave a feedbaack about you <br>
                            Copy the link and send to to them
                        </span>
                    </div>
                @endforelse
                <button onclick="copyToClipboardModern('{{ url('/reviews/feedbacks/' . $user->user_id) }}')"
        class="bg-dark_green px-3 py-2 inline-block text-white font-semibold rounded mt-10">
    Copy my Link
</button>

<style>
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .toast.show {
        opacity: 1;
    }
    .toast.error {
        background: #f44336;
    }
</style>

<script>
async function copyToClipboardModern(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Link copied to clipboard!');
    } catch (err) {
        showToast('Failed to copy link', true);
    }
}

function showToast(message, isError = false) {
    const toast = document.createElement('div');
    toast.className = isError ? 'toast error' : 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}
</script>



                <div class="mt-6 px-2">
                  {{ $feedbacks->links('pagination::tailwind') }}
                </div>
		    </div>
		</div>
    </div>

    
</section>


@include('user.layouts.footer')

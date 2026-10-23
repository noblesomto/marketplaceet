@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2 flex justify-between">
        <a href="{{ url()->previous() }}">< Back</a>
        <span>Feedbacks about {{$seller->name}}</span>

    </div>
    @include('public.components.flash-message')
    <div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 md:mt-10 mb-20 rounded-lg">
        <div class="my-2 flex justify-between">
            <div class="font-bold space-x-1">
                <span><i class="bi bi-chat-dots"></i></span>
               <span> {{ get_user_feedback_averages($seller->user_id)['count'] }} Feedback(s)</span>
            </div>
            <div>
                @php
                    $avgRating = round(get_user_feedback_averages($seller->user_id)['rating']);
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
    	<div class="w-full">
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

                        </span>
                    </div>
                @endforelse

                <div class="mt-6 px-2">
                  {{ $feedbacks->links('pagination::tailwind') }}
                </div>
		    </div>
		</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</section>



@include('user.layouts.footer')

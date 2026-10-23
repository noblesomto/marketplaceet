@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2 flex justify-between">
        <span>Leave a Feedback for {{$seller->name}}</span>

    </div>
    @include('public.components.flash-message')
    <div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 md:mt-10 mb-20 rounded-lg">
        <div class="my-2">
            <h3 class="font-bold text-dark_green">How was your experience?</h3>

        </div>
    	<div class="w-full bg-white shadow p-3">
			<div class="flex flex-col">
                <script>
                    function feedbackForm(initial = {}) {
                        return {
                            submitted: false,
                            rating: initial.rating || 0,
                            satisfaction: initial.satisfaction || 0,
                            reliable: initial.reliable || 0,
                            friendly: initial.friendly || 0,
                            messageText: initial.messageText || '',
                            hoverRating: 0,
                            hoverSatisfaction: 0,
                            hoverReliable: 0,
                            hoverFriendly: 0,
                            validateForm() {
                                this.submitted = true;
                                return this.rating > 0 &&
                                       this.satisfaction > 0 &&
                                       this.reliable > 0 &&
                                       this.friendly > 0 &&
                                       this.messageText.trim().length > 0;
                            },
                            handleSubmit($el) {
                                if (this.validateForm()) {
                                    $el.submit();
                                } else {
                                    this.$nextTick(() => {
                                        const firstError = $el.querySelector('[x-show^="submitted &&"]:not([hidden])');
                                        if (firstError) {
                                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }
                                    });
                                }
                            }
                        }
                    }
                    </script>


               <form action="/reviews/feedbacks/{{ $seller->user_id }}" method="POST"
                  x-data="feedbackForm({
                    rating: {{ $feedback->rating ?? 0 }},
                    satisfaction: {{ $feedback->satisfaction ?? 0 }},
                    reliable: {{ $feedback->reliable ?? 0 }},
                    friendly: {{ $feedback->friendly ?? 0 }},
                    messageText: @js($feedback->message ?? '')
                })"

                  @submit.prevent="handleSubmit($el)">
                @csrf

                <input type="hidden" name="seller_id" value="{{ $seller->user_id }}">
                <!-- Rating Categories -->
                <div class="space-y-8">
                    <!-- Overall Rating -->
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">Overall Rating <span class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    @click="rating = {{$i}}"
                                    @mouseover="hoverRating = {{$i}}"
                                    @mouseleave="hoverRating = 0"
                                    class="focus:outline-none transition-transform hover:scale-110"
                                >
                                    <span class="text-xl"
                                        :class="{
                                            'text-gray-300': rating < {{$i}} && hoverRating < {{$i}},
                                            'text-yellow-400': rating >= {{$i}} || hoverRating >= {{$i}},
                                            'opacity-100': hoverRating === 0 || hoverRating >= {{$i}},
                                            'opacity-70': hoverRating > 0 && hoverRating < {{$i}}
                                        }">
                                        ★
                                    </span>
                                </button>
                            @endfor
                            <span class="ml-2 text-gray-500" x-text="rating ? rating + ' / 5' : 'Not rated'"></span>
                        </div>
                        <input type="hidden" name="rating" x-model="rating">
                        <p x-show="submitted && !rating" class="mt-1 text-sm text-red-600">Please select an overall rating</p>
                    </div>

                    <!-- Satisfaction -->
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">Satisfaction <span class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    @click="satisfaction = {{$i}}"
                                    @mouseover="hoverSatisfaction = {{$i}}"
                                    @mouseleave="hoverSatisfaction = 0"
                                    class="focus:outline-none transition-transform hover:scale-110"
                                >
                                    <span class="text-xl"
                                        :class="{
                                            'text-gray-300': satisfaction < {{$i}} && hoverSatisfaction < {{$i}},
                                            'text-yellow-400': satisfaction >= {{$i}} || hoverSatisfaction >= {{$i}},
                                            'opacity-100': hoverSatisfaction === 0 || hoverSatisfaction >= {{$i}},
                                            'opacity-70': hoverSatisfaction > 0 && hoverSatisfaction < {{$i}}
                                        }">
                                        ★
                                    </span>
                                </button>
                            @endfor
                            <span class="ml-2 text-gray-500" x-text="satisfaction ? satisfaction + ' / 5' : 'Not rated'"></span>
                        </div>
                        <input type="hidden" name="satisfaction" x-model="satisfaction">
                        <p x-show="submitted && !satisfaction" class="mt-1 text-sm text-red-600">Please select a satisfaction rating</p>
                    </div>

                    <!-- Reliability -->
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">Reliability <span class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    @click="reliable = {{$i}}"
                                    @mouseover="hoverReliable = {{$i}}"
                                    @mouseleave="hoverReliable = 0"
                                    class="focus:outline-none transition-transform hover:scale-110"
                                >
                                    <span class="text-xl"
                                        :class="{
                                            'text-gray-300': reliable < {{$i}} && hoverReliable < {{$i}},
                                            'text-yellow-400': reliable >= {{$i}} || hoverReliable >= {{$i}},
                                            'opacity-100': hoverReliable === 0 || hoverReliable >= {{$i}},
                                            'opacity-70': hoverReliable > 0 && hoverReliable < {{$i}}
                                        }">
                                        ★
                                    </span>
                                </button>
                            @endfor
                            <span class="ml-2 text-gray-500" x-text="reliable ? reliable + ' / 5' : 'Not rated'"></span>
                        </div>
                        <input type="hidden" name="reliable" x-model="reliable">
                        <p x-show="submitted && !reliable" class="mt-1 text-sm text-red-600">Please select a reliability rating</p>
                    </div>

                    <!-- Friendliness -->
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">Friendliness <span class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    @click="friendly = {{$i}}"
                                    @mouseover="hoverFriendly = {{$i}}"
                                    @mouseleave="hoverFriendly = 0"
                                    class="focus:outline-none transition-transform hover:scale-110"
                                >
                                    <span class="text-xl"
                                        :class="{
                                            'text-gray-300': friendly < {{$i}} && hoverFriendly < {{$i}},
                                            'text-yellow-400': friendly >= {{$i}} || hoverFriendly >= {{$i}},
                                            'opacity-100': hoverFriendly === 0 || hoverFriendly >= {{$i}},
                                            'opacity-70': hoverFriendly > 0 && hoverFriendly < {{$i}}
                                        }">
                                        ★
                                    </span>
                                </button>
                            @endfor
                            <span class="ml-2 text-gray-500" x-text="friendly ? friendly + ' / 5' : 'Not rated'"></span>
                        </div>
                        <input type="hidden" name="friendly" x-model="friendly">
                        <p x-show="submitted && !friendly" class="mt-1 text-sm text-red-600">Please select a friendliness rating</p>
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-lg font-medium text-gray-700 mb-2">Your Review <span class="text-red-500">*</span></label>
                        <textarea
                            id="message"
                            name="message"
                            rows="4"
                            x-model="messageText"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition"
                            placeholder="Share your experience..."></textarea>
                        <p x-show="submitted && !messageText" class="mt-1 text-sm text-red-600">Please enter your review</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8">
                    <button
                        type="submit"
                        class="w-full px-4 py-2 bg-dark_green hover:bg-secondary-200 text-white font-medium rounded-md shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-200">
                        Submit feedback
                    </button>
                </div>
            </form>
		    </div>
		</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</section>



@include('user.layouts.footer')

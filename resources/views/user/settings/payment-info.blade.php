@include('user.layouts.header')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2">
        Payment Information
        @include('public.components.flash-message')
    </div>
 
       	<div class="">
		            
		        	<div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 mb-20 rounded-lg">
				        <form method="POST" action="/user/payments" enctype="multipart/form-data">
				            @csrf
				        <div class="mt-1 font-semibold text-xl">Account Details:</div>

				        <div class="mb-4 mt-4">
				            
				            <label class="text-sm font-semibold">Bank Name *</label>
				            @if ($errors->has('bank_name'))
				                <span class="text-red-700 py-1">{{ $errors->first('bank_name') }}</span>
				            @endif
				            <select name="bank_name" class="w-full px-3 py-2 bg-white border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="">--Select Bank--</option>
                                @foreach($banks as $bank)
                                    <option
                                        value="{{ $bank->name }}"
                                        data-bank-code="{{ $bank->bank_code }}"
                                        @if($user->bank_name == $bank->name) selected @endif
                                    >
                                        {{ $bank->name }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Hidden input to store the bank code -->
                            <input type="hidden" name="bank_code" id="bankCode">
				        </div>

				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold">Account Name *</label>
				            @if ($errors->has('account_name'))
				                <span class="text-red-700 py-1">{{ $errors->first('account_name') }}</span>
				            @endif
				            <input type="text" id="name" name="account_name" placeholder="Account Name" value="{{ $user->account_name }}" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
				        </div>

				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold">Account Number *</label>
				            @if ($errors->has('account_number'))
				                <span class="text-red-700 py-1">{{ $errors->first('account_number') }}</span>
				            @endif
				            <input type="text" id="name" name="account_number" placeholder="Account Number" value="{{ $user->account_number }}" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
				        </div>

				        
				
		         				    

				          <div class="mt-8">
				            <button type="submit" class="btn btn-primary py-1 text-lg flex justify-center items-center">
				                <span>Update</span>
				                <span class="ml-2">
				                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
				                      <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
				                    </svg>
				                </span>
				            </button>
				          </div>
				     				        
				        </form>
				        
				    </div>
		            
		        </div>
  

    
</section>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankSelect = document.querySelector('select[name="bank_name"]');
    const bankCodeInput = document.getElementById('bankCode');

    // Set initial value if there's a selected option
    if (bankSelect.selectedIndex > 0) {
        const selectedOption = bankSelect.options[bankSelect.selectedIndex];
        bankCodeInput.value = selectedOption.getAttribute('data-bank-code');
    }

    // Update hidden input when selection changes
    bankSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        bankCodeInput.value = selectedOption.getAttribute('data-bank-code');
    });

});
</script>
@include('user.layouts.footer')

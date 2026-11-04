{{--
    Optional bank details popup for the post/edit ad forms. Buy Direct pays the
    seller out from escrow; sellers can add the details here without leaving
    (and losing) their ad draft, or later when the item sells.
    Driven by dashboard/js/buy-direct-field.js. Include outside the ad <form>.
--}}
@php $banks = \App\Models\Bank::orderBy('name')->get(['name', 'bank_code']); @endphp
<div id="bankDetailsModal" class="hidden fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 px-4" role="dialog" aria-modal="true" aria-labelledby="bankDetailsTitle">
    <div class="w-full max-w-md bg-white rounded-xl shadow-xl overflow-hidden">
        <div class="flex items-start justify-between px-5 py-4 border-b border-gray-200">
            <div>
                <h3 id="bankDetailsTitle" class="text-base font-semibold text-gray-900">Add your account details</h3>
                <p class="text-xs text-gray-500 mt-1">Buy Direct holds the buyer's payment until they confirm delivery, then pays it into this account.</p>
            </div>
            <button type="button" data-bank-modal-close class="ml-3 text-2xl leading-none text-gray-400 hover:text-gray-600" aria-label="Close">&times;</button>
        </div>

        <form id="bankDetailsForm" action="{{ route('user.payment.info') }}" method="POST" class="px-5 py-4 space-y-4" novalidate>
            @csrf
            <p data-bank-error class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></p>

            <div>
                <label for="bdBankName" class="block text-sm font-semibold text-gray-700 mb-1">Bank Name <span class="text-red-500">*</span></label>
                @if($banks->isNotEmpty())
                    <select id="bdBankName" name="bank_name" class="custom-select block w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-base focus:ring-dark_green" required>
                        <option value="">--Select Bank--</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->name }}" data-bank-code="{{ $bank->bank_code }}" @selected($user->bank_name == $bank->name)>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                @else
                    <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        The bank list isn't available right now. Please try again later or contact support.
                    </p>
                @endif
                <input type="hidden" name="bank_code" value="{{ $user->bank_code }}">
            </div>

            <div>
                <label for="bdAccountName" class="block text-sm font-semibold text-gray-700 mb-1">Account Name <span class="text-red-500">*</span></label>
                <input id="bdAccountName" type="text" name="account_name" value="{{ $user->account_name }}" autocomplete="name" class="block w-full px-3 py-2.5 rounded-lg border border-gray-300 text-base focus:ring-dark_green" required>
            </div>

            <div>
                <label for="bdAccountNumber" class="block text-sm font-semibold text-gray-700 mb-1">Account Number <span class="text-red-500">*</span></label>
                <input id="bdAccountNumber" type="text" name="account_number" value="{{ $user->account_number }}" inputmode="numeric" class="block w-full px-3 py-2.5 rounded-lg border border-gray-300 text-base focus:ring-dark_green" required>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" data-bank-modal-close class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50">Not now</button>
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg bg-dark_green text-white font-semibold hover:opacity-90 disabled:opacity-60" @disabled($banks->isEmpty())>Save details</button>
            </div>
        </form>
    </div>
</div>

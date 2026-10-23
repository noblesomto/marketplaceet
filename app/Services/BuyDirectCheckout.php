<?php

namespace App\Services;

use App\Models\Advert;
use App\Models\Lga;
use App\Models\Payment;
use App\Models\State;
use App\Models\User;
use App\Rules\EthiopianPhoneNumber;
use App\Support\AdvertVisibility;
use Illuminate\Support\Str;

/**
 * Shared Buy Direct checkout rules for web and API. Courier integrations are
 * off: the buyer gives a delivery location and the seller arranges delivery.
 * Amounts are always computed server-side from the advert price.
 */
class BuyDirectCheckout
{
    public static function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'phone'      => ['required', 'string', new EthiopianPhoneNumber()],
            'state'      => 'required|integer|exists:states,id',
            'lga'        => 'required|string|max:255',
            'address'    => 'required|string|min:5|max:500',
        ];
    }

    public static function messages(): array
    {
        return [
            'state.required'   => 'Please select your region.',
            'lga.required'     => 'Please select your zone / city.',
            'address.required' => 'Please enter your delivery address or a landmark.',
        ];
    }

    /** Returns an error message when the buyer may not Buy Direct this advert, else null. */
    public static function eligibilityError(?Advert $ad, ?User $buyer): ?string
    {
        if (!$ad || AdvertVisibility::reasonUnavailable($ad)) {
            return 'This advert is no longer available.';
        }
        if (strtolower((string) $ad->buy_direct) !== 'yes') {
            return 'Buy Direct is not available for this item.';
        }
        if (($ad->sold ?? 'No') === 'Yes') {
            return 'Sorry, this item has already been sold.';
        }
        if ((float) $ad->price <= 0) {
            return 'This item does not have a fixed price, so it cannot be bought directly.';
        }
        if ($buyer && $ad->user_id === $buyer->user_id) {
            return 'You cannot buy your own item.';
        }

        $seller = User::where('user_id', $ad->user_id)->first();
        if (!$seller || $seller->disable_account === 'yes' || !$seller->account_number || !$seller->bank_code) {
            return 'Buy Direct is temporarily unavailable for this item. Please contact the seller.';
        }

        return null;
    }

    /** Validates the zone belongs to the chosen region. */
    public static function lgaError(int $stateId, string $lga): ?string
    {
        return Lga::where('state_id', $stateId)->where('name', $lga)->exists()
            ? null
            : 'Please select a zone / city in the chosen region.';
    }

    /** Session/order payload for a validated checkout. */
    public static function build(Advert $ad, array $input): array
    {
        $state = State::find($input['state']);

        return array_merge(BuyDirectSettings::quote((float) $ad->price), [
            'advert_id'        => $ad->id,
            'first_name'       => trim($input['first_name']),
            'last_name'        => trim($input['last_name']),
            'phone'            => trim($input['phone']),
            'state_id'         => $state->id,
            'state_name'       => $state->name,
            'lga'              => $input['lga'],
            'delivery_address' => trim(strip_tags($input['address'])),
        ]);
    }

    /**
     * Create (or refresh) the pending order row for a checkout. A buyer's
     * earlier unpaid attempt for the same advert is reused so retries don't
     * pile up rows; its amounts are re-priced in case settings changed.
     */
    public static function upsertPendingOrder(array $checkout, string $buyerUserId, string $txRef, string $source): Payment
    {
        $attrs = [
            'payment_reference' => $txRef,
            'first_name'        => $checkout['first_name'],
            'last_name'         => $checkout['last_name'],
            'phone'             => $checkout['phone'],
            'amount'            => $checkout['item_price'],
            'commission'        => $checkout['commission'],
            'amount_paid'       => $checkout['grand_total'],
            'shipping_cost'     => 0,
            'shipping_method'   => null,
            'city'              => null,
            'state'             => $checkout['state_id'],
            'delivery_lga'      => $checkout['lga'],
            'delivery_address'  => $checkout['delivery_address'],
            'source'            => $source,
        ];

        $existing = Payment::where('advert_id', $checkout['advert_id'])
            ->where('user_id', $buyerUserId)
            ->where('payment_status', 'pending')
            ->first();

        if ($existing) {
            $existing->update($attrs);

            return $existing;
        }

        do {
            $orderCode = strtoupper(Str::random(8));
        } while (Payment::where('order_code', $orderCode)->exists());

        return Payment::create($attrs + [
            'advert_id'      => $checkout['advert_id'],
            'order_code'     => $orderCode,
            'user_id'        => $buyerUserId,
            'payment_status' => 'pending',
        ]);
    }
}

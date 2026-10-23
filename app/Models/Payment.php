<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'advert_id',
        'order_code',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'payment_reference',
        'trans_id', 
        'amount',
        'commission',
        'shipping',
        'amount_paid',
        'payment_status',
        'shipping_cost',
        'shipping_method',
        'city',
        'state',
        'shipping_status',
        'shipping_status_date',
        'buyer_status',
        'seller_status',
        'seller_status_date',
        'ship_code',
        'tracking_id',
        'seller_settlement',
        'settlement_date',
        'source',
        'delivery_address',
        'delivery_lga',
        'paid_at',
        'ship_by',
        'shipped_at',
        'auto_release_at',
        'buyer_confirmed_at',
        'release_type',
        'canceled_by',
        'cancel_reason',
        'canceled_at',
        'dispute_status',
        'dispute_reason',
        'dispute_opened_at',
        'dispute_resolution',
        'dispute_admin_note',
        'dispute_resolved_at',
        'refund_status',
        'refund_amount',
        'refund_reference',
        'refund_reason',
        'refund_error',
        'refunded_at',
        'payout_status',
        'payout_amount',
        'payout_reference',
        'payout_transfer_id',
        'payout_error',
        'payout_attempts',
        'paid_out_at',
    ];

    protected $casts = [
        'paid_at'             => 'datetime',
        'ship_by'             => 'datetime',
        'shipped_at'          => 'datetime',
        'auto_release_at'     => 'datetime',
        'buyer_confirmed_at'  => 'datetime',
        'canceled_at'         => 'datetime',
        'dispute_opened_at'   => 'datetime',
        'dispute_resolved_at' => 'datetime',
        'refunded_at'         => 'datetime',
        'paid_out_at'         => 'datetime',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_method');
    }

    public function stateRel()
    {
        return $this->belongsTo(State::class, 'state', 'id');
    }

    public function shippingMethod()
    {
        return $this->belongsTo(Shipping::class, 'shipping_method');
    }

    public function cityLocation()
    {
        return $this->belongsTo(GigLogistic::class, 'city');
    }

    public function stateLocation()
    {
        return $this->belongsTo(State::class, 'state');
    }

    /** Human-readable delivery location for orders placed without a courier. */
    public function deliveryLocationText(): string
    {
        $state = $this->stateLocation->name ?? null;

        return trim(implode(', ', array_filter([$this->delivery_address, $this->delivery_lga, $state])));
    }
}

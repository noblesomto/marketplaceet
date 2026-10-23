# 📦 Order Details & Seller Shipping Details — Mobile Integration Guide

Reference for implementing two screens:

1. **Buyer: Order Details** — what a buyer sees after paying for a Buy Direct order (`/user/order-details/{id}` on web).
2. **Seller: Shipping Details** — what a seller sees for a sold Buy Direct item (`/user/ad-shipping/{id}` on web).

Both endpoints now return **computed display fields** (badge label/icon/color, banner text, button visibility) alongside the raw status fields. **Render the computed fields directly — don't re-implement the status logic client-side.** They come from `App\Support\ShippingStatusBadge`, the single source of truth also used by the web pages, so mobile and web can never show conflicting statuses for the same order.

---

## The three status fields

Every paid order tracks three **independent** fields on the `payments` record. Don't conflate them:

| Field | Set by | Values | Meaning |
|---|---|---|---|
| `seller_status` | Seller, via *Update Shipping Status* | `pending` \| `shipped` \| `canceled` | Has the seller dropped the item off with the courier? Locked (read-only) once `shipping_status` leaves `pending`. |
| `shipping_status` | Shipper (GIG Logistics agent) | `pending` \| `shipped` \| `pickup` \| `delivered` \| `canceled` | Real physical shipment progress, set from the shipper portal. |
| `buyer_status` | Buyer, via *Confirm Delivery* / *Cancel Order* | `pending` \| `delivered` \| `canceled` | The only field that represents **confirmed receipt**. Releases payment to the seller when set to `delivered`. |

A single order can have all three at once, deliberately disagreeing — e.g. seller marked `shipped`, the shipper's own status has progressed further to `delivered`, but the buyer hasn't confirmed yet (`buyer_status: pending`). The overall badge shown to the user must reflect the *combination*, not any one field alone — that's what `status_badge` computes for you.

**Priority chain** (`ShippingStatusBadge::resolve()`): `buyer_status === 'delivered'` beats everything → then `shipping_status` (once it leaves `pending`) → then `seller_status`.

---

## 1. Buyer: Order Details Screen

### Endpoint

```
GET /api/user/payments/{paymentId}/details
Authorization: Bearer {token}
```

Buyer-only — returns `404` if the payment doesn't belong to the authenticated user, isn't paid, or doesn't exist.

### Sample response

Matches this screen state (seller shipped, shipper marked it delivered, buyer hasn't confirmed yet):

```json
{
  "success": true,
  "data": {
    "order": {
      "id": 2,
      "order_code": "RW3LEBAV",
      "reference": "5co2x73xun",
      "amount_paid": "169481",
      "payment_status": "paid",
      "shipping_status": "delivered",
      "seller_status": "shipped",
      "buyer_status": "pending",
      "tracking_id": "Test-07556750",
      "shipping_status_date": "2026-07-14 03:48:24",
      "seller_status_date": "2026-07-14 03:47:26",
      "created_at": "2026-06-29T10:43:20.000000Z"
    },
    "status_badge": {
      "stage": "ship_delivered",
      "label": "Pending Confirmation",
      "icon": "bi-hourglass-split",
      "color": "orange",
      "class": "bg-orange-100 text-orange-700 border-orange-200"
    },
    "shipping_badge": {
      "label": "Delivered",
      "icon": "bi-check-circle-fill",
      "color": "green",
      "class": "bg-green-100 text-green-700 border-green-200"
    },
    "status_banner": {
      "icon": "bi-hourglass-split",
      "color": "orange",
      "class": "bg-orange-50 border border-orange-200 text-orange-800",
      "text": "Your order has been marked as delivered on 14 Jul 2026. Please confirm receipt below."
    },
    "actions": {
      "can_confirm_delivery": true,
      "can_cancel": false
    },
    "product": {
      "name": "iPhone 15 Pro 128GB",
      "price": "150000",
      "state": "Lagos",
      "thumbnail_url": "https://www.marketplace.ng/uploads/.../thumbnail.webp",
      "ad_url": "https://www.marketplace.ng/ikeja/iphone-15-pro-128gb/8058"
    },
    "shipping_company": {
      "name": "GIG Logistics",
      "logo": "https://www.marketplace.ng/images/gig-logo.png"
    },
    "delivery_location": {
      "address": "No. 1 P & T Quarters, Market Road, Opposite Osisatech Polytechnic, Enugu",
      "city": "Enugu",
      "state": "Enugu"
    }
  }
}
```

### Screen layout → JSON field mapping

| Screen section | Source |
|---|---|
| Page title "Order #{code}" | `order.order_code` (fall back to `order.id` if null) |
| Top-right status pill | `status_badge.label` / `.icon` / `.color` |
| Colored banner under the title | `status_banner.text` / `.icon` / `.color` — **hide entirely if `status_banner` is `null`** (happens when `status_badge.stage === "pending"`, i.e. seller hasn't shipped yet) |
| "Item Purchased" card | `product.thumbnail_url`, `product.name` (tap → `product.ad_url`), `product.state`, `order.amount_paid` |
| "Selected Shipping" card | `shipping_company.name` / `.logo`, badge = `shipping_badge` (**note:** this can show a different label than the top badge — that's correct, it reflects `shipping_status` only), `order.tracking_id` (hide row if null) |
| "Selected Delivery / Pickup Information" card | `delivery_location.address` / `.city` / `.state` — hide the whole card if all three are null |
| "Order Summary" card | `order.created_at` (format `d MMM yyyy`), `order.payment_status` (green "✓ Paid" if `"paid"`, amber "⏳ Pending" otherwise), `order.reference`, `order.amount_paid` |
| Action buttons | see **Actions** below |

### Status badge reference (`status_badge`)

| `stage` | `label` | `icon` | `color` | When |
|---|---|---|---|---|
| `pending` | Pending | `bi-clock` | amber | Nothing has happened yet |
| `seller_shipped` | Dropped off at {company} | `bi-box-seam-fill` | blue | Seller marked `shipped`, shipper hasn't updated yet |
| `ship_shipped` | Shipped | `bi-truck` | blue | Shipper set `shipping_status = shipped` |
| `ship_pickup` | Ready for Pickup | `bi-shop` | purple | Shipper set `shipping_status = pickup` |
| `ship_delivered` | Pending Confirmation | `bi-hourglass-split` | orange | Shipper set `shipping_status = delivered`, buyer hasn't confirmed |
| `delivered` | Delivered | `bi-check-circle-fill` | green | Buyer confirmed receipt (`buyer_status = delivered`) — final state |
| `canceled` | Canceled | `bi-x-circle-fill` | red | Either party canceled |

`icon` values are Bootstrap Icons class names (web uses `bi` webfont). Map to your own icon set by name, or ignore and use your own icon per `stage`.

### Status banner reference (`status_banner`)

Only present (non-null) for the stages below — `pending` has no banner.

| `stage` | Banner text template |
|---|---|
| `seller_shipped` | "The seller has dropped off your order at {company}{ on {seller_status_date, d MMM yyyy}}. It will be on its way to you shortly." |
| `ship_shipped` | "Your order has been shipped{ on {shipping_status_date, d MMM yyyy}}. Estimated delivery: 3–7 working days." |
| `ship_pickup` | "Your order is ready for pickup at a nearby centre{ (updated {shipping_status_date, d MMM yyyy})}." |
| `ship_delivered` | "Your order has been marked as delivered{ on {shipping_status_date, d MMM yyyy}}. Please confirm receipt below." |
| `delivered` | "Order delivered{ on {shipping_status_date, d MMM yyyy}}. Thank you for shopping with us!" |
| `canceled` | "This order has been canceled. A refund is being processed and will be credited back to you shortly. Please contact support if you need help." |

The date suffix is already baked into `status_banner.text` by the API — the template above is just for reference, you don't need to build it yourself.

### Actions

**Confirm Delivery** — show button when `actions.can_confirm_delivery === true`.

```
POST /api/user/payments/{paymentId}/confirm-delivery
Authorization: Bearer {token}
```
- `200` `{"success": true, "message": "Delivery confirmed successfully"}`
- `404` `{"success": false, "message": "Order not found"}`
- `409` `{"success": false, "message": "Delivery already confirmed"}`

**Cancel Order** — show button only when `actions.can_cancel === true` (i.e. `can_confirm_delivery` is true AND the order hasn't shipped yet). When `can_confirm_delivery` is true but `can_cancel` is false, show this static message instead of a button: *"This order is already with {shipping_company.name}, so it can no longer be canceled here. Please contact support if you need help."*

```
POST /api/user/payments/{paymentId}/cancel-order
Authorization: Bearer {token}
```
- `200` `{"success": true, "message": "Order canceled. Your refund is being processed."}`
- `404` `{"success": false, "message": "Order not found."}`
- `422` `{"success": false, "message": "This order has already been canceled."}`
- `422` `{"success": false, "message": "This order has already been delivered and can no longer be canceled."}`
- `422` `{"success": false, "message": "This order is already with the shipping company and can no longer be canceled here. Please contact support."}`

When `actions.can_confirm_delivery` is `false` (order already `delivered` or `canceled`), show neither button — just the banner state.

### Error responses

| Code | Body | Meaning |
|---|---|---|
| `404` | `{"success": false, "message": "Order not found."}` | Payment doesn't exist, isn't yours, or isn't paid |

---

## 2. Seller: Shipping Details Screen

### Endpoint

```
GET /api/user/adverts/{advertId}/shipping-details
Authorization: Bearer {token}
```

Seller-only (must own the ad) — `advertId` is the advert's database `id`, not the public `ad_id`.

Also referenced in the `ad_sold` push notification payload: `click_action: "OPEN_AD_SHIPPING"`, `shipping_api_url`.

### Sample response

```json
{
  "success": true,
  "data": {
    "payment_id": 2,
    "advert_id": 8058,
    "ad_id": "8058",
    "shipping_status": "delivered",
    "buyer_status": "pending",
    "seller_status": "shipped",
    "tracking_id": "Test-07556750",
    "ship_code": "BVXIGSBBTO",
    "status_badge": {
      "stage": "ship_delivered",
      "label": "Pending Confirmation",
      "icon": "bi-hourglass-split",
      "color": "orange",
      "class": "bg-orange-100 text-orange-700 border-orange-200"
    },
    "shipping_badge": {
      "label": "Delivered",
      "icon": "bi-check-circle-fill",
      "color": "green",
      "class": "bg-green-100 text-green-700 border-green-200"
    },
    "actions": {
      "can_update_status": false
    },
    "seller_status_options": [
      { "value": "pending",  "label": "⏳ Pending" },
      { "value": "shipped",  "label": "📦 Shipped (Dropped off at GIG Logistics)" },
      { "value": "canceled", "label": "❌ Canceled" }
    ],
    "buyer": {
      "name": "Somtochukwu Ifejika",
      "phone": "07031525786"
    },
    "product": {
      "name": "iPhone 15 Pro 128GB",
      "thumbnail_url": "https://www.marketplace.ng/uploads/.../thumbnail.webp",
      "ad_url": "https://www.marketplace.ng/ikeja/iphone-15-pro-128gb/8058"
    },
    "shipping_company": {
      "name": "GIG Logistics",
      "logo": "https://www.marketplace.ng/images/gig-logo.png"
    },
    "delivery_location": {
      "address": "No. 1 P & T Quarters, Market Road, Opposite Osisatech Polytechnic, Enugu",
      "city": "Enugu",
      "state": "Enugu"
    },
    "instructions": {
      "step_1": "Ensure the item is well-packaged and clearly label it with the buyer's name, phone number, and delivery address.",
      "step_2": "Take it to your nearest GIG Logistics office.",
      "step_3": "Present the 10-digit shipping code at the counter: BVXIGSBBTO",
      "step_4": "No payment is required at the shipping office. All logistics fees have been covered."
    },
    "note": "This transaction is secured by our Buy Direct service. Your payment will be released as soon as the buyer confirms delivery."
  }
}
```

### Screen layout → JSON field mapping

| Screen section | Source |
|---|---|
| Page title "Shipping Details" + status pill | static title + `status_badge` (same badge reference table as the buyer screen above) |
| "Buyer Information" card | `buyer.name`, `buyer.phone` (tappable `tel:` link) |
| "Item Sold" card | `product.thumbnail_url`, `product.name` |
| "Selected Shipping" card | `shipping_company.name` / `.logo`, badge = `shipping_badge`, `tracking_id` (hide row if null) |
| "Selected Delivery / Pickup Location" card | `delivery_location.*` — hide if all null |
| "It's Time to Ship" instructions card | `instructions.step_1..4` + the shipping code (`ship_code`, with copy-to-clipboard). **Show this card only when `status_badge.stage === "pending"`** — hide it once the seller has already shipped/canceled, same as web. |
| "Update Shipping Status" card | see **Update Shipping Status** below |
| Blue note footer | `note` |

### Update Shipping Status

If `actions.can_update_status === false`, don't show the picker — show this static locked message instead: *"This order is already with {shipping_company.name}, so it can no longer be updated from here. Contact support if you need to make a change."*

If `true`, show a picker populated from `seller_status_options` (already has the correct labels, including the dynamic company name — don't hardcode them). On submit:

```
PATCH /api/user/payments/{paymentId}/shipping-status
Authorization: Bearer {token}
Content-Type: application/json

{ "seller_status": "shipped" }   // one of: pending | shipped | canceled
```
- `200` `{"success": true, "message": "Shipping status updated successfully"}`
- `404` `{"success": false, "message": "Order not found"}`
- `422` `{"success": false, "message": "This order is already with the shipping company and can no longer be updated from here."}` (race condition: shipper updated between page load and submit)
- `422` `{"success": false, "errors": {"seller_status": ["The selected seller status is invalid."]}}` (bad value)

### Error responses

| Code | Body | Meaning |
|---|---|---|
| `403` | `{"success": false, "message": "Unauthorised."}` | You don't own this ad |
| `404` | `{"success": false, "message": "No shipping record found for this ad."}` | No payment record for this ad (not sold, or not Buy Direct) |

---

## Push notifications that open these screens

| `type` | `click_action` | Sent to | Open screen with |
|---|---|---|---|
| `ad_sold` | `OPEN_AD_SHIPPING` | Seller, when their item sells | `shipping_api_url` → `GET /api/user/adverts/{advertId}/shipping-details` |
| `shipping_update` | `OPEN_ORDER_DETAILS` | Buyer, when `shipping_status` changes | `order_details_api_url` → `GET /api/user/payments/{paymentId}/details` |

Both payloads include a ready-to-use API URL — no need to construct it. For general push setup (FCM registration, token handling), see `docs/FLUTTER_PUSH_NOTIFICATIONS.md` and `docs/QUICK_START_PUSH_NOTIFICATIONS.md`.

---

## Quick reference: all endpoints in this flow

| Method | Endpoint | Who | Purpose |
|---|---|---|---|
| GET | `/api/user/payments/{paymentId}/details` | Buyer | Order details screen |
| POST | `/api/user/payments/{paymentId}/confirm-delivery` | Buyer | Confirm receipt, release payment |
| POST | `/api/user/payments/{paymentId}/cancel-order` | Buyer | Cancel before shipped |
| GET | `/api/user/adverts/{advertId}/shipping-details` | Seller | Shipping details screen |
| PATCH | `/api/user/payments/{paymentId}/shipping-status` | Seller | Mark shipped/canceled |

Full Postman collection: `postman/Marketplace-API-Complete.postman_collection.json` → **User Payments** folder.

---

## Source of truth

If web behavior and this doc ever disagree, the code wins — this doc should be updated to match, not the other way round:

- `App\Support\ShippingStatusBadge` — badge + banner logic (shared by web and API)
- `App\Http\Controllers\Api\UserController@orderDetails`, `@adShippingDetails`, `@confirmDelivery`, `@cancelOrder`, `@updateShippingStatus`
- `resources/views/user/order-details.blade.php`, `resources/views/user/ad-shipping.blade.php` (web equivalents)

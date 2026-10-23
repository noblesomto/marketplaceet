# Marketplace Ethiopia - Complete API Endpoints Reference

## 📋 Quick Reference Guide

All endpoints listed with method, path, authentication requirement, and description.

---

## 1️⃣ Authentication Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register` | No | Register new user account |
| POST | `/api/login` | No | Login with email & password |
| POST | `/api/verify-otp` | No | Verify OTP code |
| POST | `/api/resend-otp` | No | Resend OTP verification code |
| GET | `/api/verify/{email}/{token}` | No | Verify email via link |
| POST | `/api/resend-verification` | No | Resend verification email |
| POST | `/api/forgot-password` | No | Send password reset link |
| POST | `/api/reset-password/{user_id}/{token}` | No | Reset password |
| POST | `/api/auth/social` | No | Social authentication (Google, Facebook) |
| GET | `/api/user` | Yes | Get current authenticated user |
| POST | `/api/logout` | Yes | Logout and invalidate token |
| DELETE | `/api/trusted-device/{device_id}` | Yes | Remove trusted device |

---

## 2️⃣ Adverts (Public)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/adverts` | No | Get paginated list of adverts |
| GET | `/api/adverts/{id}` | No | Get single advert details |
| GET | `/api/adverts/seller/{seller_id}` | No | Get adverts by seller |
| GET | `/api/adverts/featured` | No | Get featured/promoted adverts |
| GET | `/api/adverts/load-more` | No | Load more adverts (pagination) |
| POST | `/api/adverts/{id}/report` | Yes | Report inappropriate advert |
| POST | `/api/adverts/{id}/apply` | Yes | Apply for job posting |

---

## 3️⃣ Categories & Browse

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/categories` | No | Get all categories |
| GET | `/api/categories/{category_slug}` | No | Get adverts in category |
| GET | `/api/categories/{category_slug}/{subcat_slug}` | No | Get adverts in subcategory |
| GET | `/api/brands/{category_slug}/{subcat_slug}/{brand_slug}` | No | Get adverts by brand |
| GET | `/api/location/{state_slug}` | No | Get adverts by location |
| GET | `/api/adverts/categories/{categoryId}/subcategories` | No | Get subcategories for category |
| GET | `/api/adverts/subcategories/{subcategoryId}/brands` | No | Get brands for subcategory |
| GET | `/api/adverts/brands/{brandId}/models` | No | Get models for brand |

---

## 4️⃣ Location & Shipping

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/locations/states` | No | Get all states |
| GET | `/api/locations/states/{state_id}/cities` | No | Get cities in state |
| GET | `/api/locations/states/{state_id}/details` | No | Get state with all cities |
| GET | `/api/locations/cities` | No | Search cities |
| GET | `/api/locations/cities/{city_id}` | No | Get specific city |
| GET | `/api/adverts/{id}/buy-direct` | Yes | Buy Direct checkout bootstrap (quote, regions, escrow terms) |
| POST | `/api/adverts/{id}/buy-direct-payment` | Yes | Buy Direct price breakdown |
| POST | `/api/shipping/calculate/{advertId}` | Yes | Validate delivery details, return server-side quote + `checkout` |

---

## 5️⃣ Search & Filter

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/search` | No | Basic search adverts |
| POST | `/api/search/filter` | No | Advanced filter search |
| POST | `/api/search/filter-by-seller` | No | Filter by seller |
| POST | `/api/search/filter-by-buydirect` | No | Filter buy direct items |
| GET | `/api/search/location/{location}/{slug}` | No | Location-based search |
| GET | `/api/search/filters` | No | Get available filters |
| GET | `/api/search/suggestions` | No | Get search suggestions |
| GET | `/api/search/load-more` | No | Load more search results |

---

## 6️⃣ User Dashboard

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/user/dashboard` | Yes | Get user dashboard data |
| GET | `/api/user/categories` | Yes | Get categories for user |
| GET | `/api/user/messages/conversations` | Yes | Get user conversations |
| GET | `/api/user/ads` | Yes | Get user's adverts |
| PATCH | `/api/user/ads/{adId}/status` | Yes | Update advert status (enable/disable) |
| PATCH | `/api/user/ads/{adId}/mark-sold` | Yes | Mark advert as sold |
| GET | `/api/user/wishlist` | Yes | Get wishlist items |
| POST | `/api/user/wishlist/{adId}` | Yes | Add to wishlist |
| DELETE | `/api/user/wishlist/{adId}` | Yes | Remove from wishlist |
| POST | `/api/user/wishlist/{adId}` | Yes | Toggle wishlist |
| GET | `/api/user/payments` | Yes | Get user payments |
| GET | `/api/user/payments/{paymentId}/details` | Yes | Order details (buyer) — status, actions, escrow, delivery |
| POST | `/api/user/payments/{paymentId}/confirm-delivery` | Yes | Buyer confirms receipt — releases escrowed payment |
| POST | `/api/user/payments/{paymentId}/cancel-order` | Yes | Buyer cancels before shipping — full refund |
| POST | `/api/user/payments/{paymentId}/report-problem` | Yes | Buyer opens a dispute — freezes payment |
| PATCH | `/api/user/payments/{paymentId}/shipping-status` | Yes | Seller: `shipped` or `canceled` |
| GET | `/api/user/adverts/{advertId}/shipping-details` | Yes | Order details (seller) |
| GET/PUT | `/api/user/payment-info` | Yes | Seller payout bank details (required for Buy Direct) |
| GET | `/api/user/feedbacks` | Yes | Get user feedbacks |
| POST | `/api/user/feedbacks/{sellerId}` | Yes | Submit feedback/rating |
| GET | `/api/user/following/check/{userId}` | Yes | Check if following user |
| POST | `/api/user/following/toggle` | Yes | Follow/unfollow user |

---

## 7️⃣ Advert Management

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/adverts/create/data` | Yes | Get data for creating advert (categories, states, etc.) |
| GET | `/api/adverts/{advertId}/edit` | Yes | Get advert data for editing |
| POST | `/api/adverts` | Yes | Create new advert |
| PUT | `/api/adverts/{advertId}` | Yes | Update existing advert |
| DELETE | `/api/adverts/{advertId}` | Yes | Delete advert |

---

## 8️⃣ Messages

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/messages` | Yes | Send message |
| GET | `/api/messages/conversation/{advertId}/{receiverId}` | Yes | Get conversation messages |
| GET | `/api/messages/advert/{advertId}` | Yes | Get all messages for advert |
| GET | `/api/messages/conversations` | Yes | Get all user conversations |
| GET | `/api/messages/unread/count` | Yes | Get unread messages count |
| PUT | `/api/messages/{messageId}/read` | Yes | Mark message as read |
| PUT | `/api/messages/conversation/{advertId}/{userId}/read` | Yes | Mark conversation as read |
| POST | `/api/messages/archive` | Yes | Archive conversation |
| POST | `/api/messages/unarchive` | Yes | Unarchive conversation |
| GET | `/api/messages/archived` | Yes | Get archived conversations |
| POST | `/api/payments/{paymentId}/mark-delivered` | Yes | Mark payment as delivered |

---

## 9️⃣ Payments

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/payments/initialize` | Yes | Start Buy Direct payment (body = `checkout` from shipping/calculate) |
| POST | `/api/payments/callback` | No | Confirm payment after Flutterwave checkout |
| POST | `/api/flutterwave/webhook` | verif-hash | Flutterwave → server (charges + payout transfers) |
| POST | `/api/payments/initialize-boost` | Yes | Initialize boost payment |
| GET | `/api/payments/{paymentId}` | Yes | Get payment details |
| GET | `/api/payments/user/{userId}` | Yes | Get user payments |
| GET | `/api/boosts/user/{userId}` | Yes | Get user boosts |
| POST | `/api/payments/callback` | No | Payment gateway callback (webhook) |

---

## 🔟 Boost Management

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/boost/options` | No | Get available boost types & durations |
| POST | `/api/boost/calculate` | No | Calculate boost price |
| GET | `/api/user/boosts` | Yes | Get user's boost history |
| GET | `/api/boosts/active` | Yes | Get active boosts |
| GET | `/api/boosts/pending` | Yes | Get pending boosts |
| GET | `/api/adverts/{advertId}/boost-info` | Yes | Get boost info for advert |
| POST | `/api/adverts/{advertId}/boost` | Yes | Create boost for advert |
| GET | `/api/adverts/{advertId}/boost-status` | Yes | Check boost status |
| GET | `/api/boosts/{boostId}` | Yes | Get boost details |
| POST | `/api/boosts/{boostId}/upload-proof` | Yes | Upload payment proof |
| DELETE | `/api/boosts/{boostId}` | Yes | Cancel boost |

---

## 1️⃣1️⃣ User Profile

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/user/profile` | Yes | Get user profile |
| GET | `/api/user/about-account` | Yes | Get account details |
| GET | `/api/user/profile-info` | Yes | Get profile information |
| PUT | `/api/user/profile/address` | Yes | Update address |
| POST | `/api/user/profile/address` | Yes | Update address (form-data) |
| PUT | `/api/user/profile/phone` | Yes | Update phone number |
| GET | `/api/user/verification` | Yes | Get verification status |
| POST | `/api/user/verification` | Yes | Submit verification documents |
| GET | `/api/user/payment-info` | Yes | Get payment information |
| PUT | `/api/user/payment-info` | Yes | Update payment information |
| PUT | `/api/user/password` | Yes | Change password |
| PUT | `/api/user/notifications` | Yes | Update notification preferences |
| GET | `/api/user/settings` | Yes | Get user settings |
| DELETE | `/api/user/account` | Yes | Disable account |
| POST | `/api/user/logout` | Yes | Logout user |

---

## 1️⃣2️⃣ Block Users

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/users/block` | Yes | Block a user |
| POST | `/api/users/unblock` | Yes | Unblock a user |
| GET | `/api/users/blocked` | Yes | Get list of blocked users |
| GET | `/api/users/check-blocked/{userId}` | Yes | Check if user is blocked |
| POST | `/api/users/block-all/{userId}` | Yes | Block user globally |

---

## 1️⃣3️⃣ Statistics & Analytics

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/user/stats` | Yes | Get user statistics |
| GET | `/api/user/unread-messages` | Yes | Get unread messages count |
| GET | `/api/user/notifications` | Yes | Get notifications |
| GET | `/api/user/{userId}/followers` | Yes | Get user followers |
| GET | `/api/user/{userId}/feedback` | Yes | Get user feedback |
| GET | `/api/adverts/count` | Yes | Get total adverts count |
| GET | `/api/adverts/by-state` | Yes | Get adverts grouped by state |
| GET | `/api/adverts/count-filtered` | Yes | Get filtered advert count |
| GET | `/api/adverts/brands` | Yes | Get brands with advert counts |

---

## 1️⃣4️⃣ Device Tokens & Notifications

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/device-tokens` | Yes | Get user's device tokens |
| POST | `/api/device-tokens` | Yes | Register device token (FCM) |
| DELETE | `/api/device-tokens/{id}` | Yes | Delete device token |
| GET | `/api/notification-settings` | Yes | Get notification settings |
| PUT | `/api/notification-settings` | Yes | Update notification settings |
| POST | `/api/test/notification` | Yes | Send test push notification |
| GET | `/api/test/my-tokens` | Yes | Get my FCM tokens (testing) |

---

## 1️⃣5️⃣ Utility Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/user` | Yes | Get authenticated user |
| GET | `/api/unread-messages-count` | Yes | Get unread messages count |

---

## 📊 Summary Statistics

| Category | Total Endpoints |
|----------|----------------|
| Authentication | 12 |
| Adverts (Public) | 7 |
| Categories & Browse | 8 |
| Location & Shipping | 6 |
| Search & Filter | 8 |
| User Dashboard | 16 |
| Advert Management | 5 |
| Messages | 10 |
| Payments | 6 |
| Boost Management | 11 |
| User Profile | 15 |
| Block Users | 5 |
| Statistics | 9 |
| Device & Notifications | 7 |
| Utility | 2 |
| **TOTAL** | **127 Endpoints** |

---

## 🔑 Authentication Header

For all protected endpoints (marked "Yes" in Auth column):

```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

In Postman:
- Use `{{access_token}}` variable
- Auto-set after login
- Collection handles it automatically

---

## 📝 Common Request Bodies

### Register
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "08012345678",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

### Login
```json
{
  "email": "john@example.com",
  "password": "Password123!"
}
```

### Create Advert
```json
{
  "ad_title": "iPhone 13 Pro Max",
  "category": 1,
  "subcategory": 6,
  "brand": 15,
  "price": 450000,
  "price_type": "fixed",
  "description": "Brand new in box",
  "state": 25,
  "lga": "Bole",
  "phone": "08012345678",
  "item_condition": "new",
  "negotiable": true
}
```

### Search Filter
```json
{
  "category": 1,
  "subcategory": 6,
  "min_price": 100000,
  "max_price": 500000,
  "state": "addis-ababa",
  "condition": "new",
  "brand": 15
}
```

### Send Message
```json
{
  "advert_id": 123,
  "receiver_id": "USR456",
  "message": "Is this item still available?"
}
```

### Create Boost
```json
{
  "boost_type_id": 1,
  "duration_id": 3,
  "payment_method": "card"
}
```

### Initialize Payment (Buy Direct)
Amounts are always computed on the server — do not send them.
```json
{
  "advert_id": 123,
  "first_name": "Abebe",
  "last_name": "Kebede",
  "phone": "0911234567",
  "state": 1,
  "lga": "Bole",
  "address": "Near Edna Mall, House 12"
}
```

### Submit Feedback
```json
{
  "rating": 5,
  "comment": "Great seller, fast delivery!",
  "transaction_id": "TXN123456"
}
```

---

## 🎯 Response Formats

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Operation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Paginated Response
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 10,
  "per_page": 20,
  "total": 195
}
```

---

## 🚀 Quick Start Workflow

### For Mobile App Developers:

1. **Authentication:**
   ```
   POST /api/register → Get access_token
   POST /api/login → Get access_token
   ```

2. **Browse Adverts:**
   ```
   GET /api/adverts
   GET /api/adverts/{id}
   GET /api/categories
   ```

3. **Create Advert:**
   ```
   GET /api/adverts/create/data
   POST /api/adverts (with images)
   POST /api/adverts/{id}/boost
   ```

4. **Messaging:**
   ```
   GET /api/messages/conversations
   POST /api/messages
   GET /api/messages/unread/count
   ```

5. **Buy Direct (escrow):**
   ```
   GET   /api/adverts/{id}/buy-direct
   POST  /api/shipping/calculate/{id}          -> data.checkout
   POST  /api/payments/initialize              (body = checkout) -> authorization_url
   POST  /api/payments/callback                (reference) -> order paid, held in escrow
   PATCH /api/user/payments/{id}/shipping-status   seller: shipped (within 3 days, admin-configurable)
   POST  /api/user/payments/{id}/confirm-delivery  buyer: releases payment (auto after 7 days)
   POST  /api/user/payments/{id}/report-problem    buyer: dispute, freezes payment
   ```

---

*Complete API Reference for Marketplace Ethiopia*
*Version 1.0 - January 2026*
*Total: 127 Endpoints*

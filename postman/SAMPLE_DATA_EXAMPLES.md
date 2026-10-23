# Sample Data Examples in Postman Collection

All POST, PUT, and PATCH requests in the collection include ready-to-use sample data. Just import and test!

## 🎯 Authentication Endpoints

### Register New Account
```json
{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "0911234567",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login
```json
{
    "email": "john.doe@example.com",
    "password": "password123"
}
```
**✨ Token automatically saved after login!**

### Verify OTP
```json
{
    "email": "john.doe@example.com",
    "otp": "123456"
}
```

### Forgot Password
```json
{
    "email": "john.doe@example.com"
}
```

### Reset Password
```json
{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### Social Authentication
```json
{
    "provider": "google",
    "token": "social_auth_token_here",
    "name": "John Doe",
    "email": "john.doe@example.com"
}
```

---

## 📱 Device & Notifications

### Register Device Token
```json
{
    "token": "device_fcm_token_here",
    "platform": "android"
}
```
**Platforms:** `android` or `ios`

### Update Notification Settings
```json
{
    "push_notifications": true,
    "email_notifications": true,
    "message_notifications": true,
    "advert_updates": true
}
```

### Send Test Notification (Local Only)
```json
{
    "title": "Test Notification",
    "message": "This is a test push notification",
    "data": {
        "test": true
    }
}
```

---

## 📦 Advert Management

### Create New Advert
```json
{
    "title": "Samsung Galaxy S24",
    "description": "Brand new Samsung Galaxy S24 in perfect condition with original box and accessories",
    "price": 500000,
    "price_type": "Negotiable",
    "category": "4",
    "sub_category": "6",
    "brand": "13",
    "item_condition": "New",
    "quantity": 1,
    "buy_direct": "Yes",
    "state": "Addis Ababa",
    "lga": "Bole",
    "shipment": "Pickup",
    "show_contact": "No"
}
```

**Price Types:**
- `Negotiable`
- `Fixed`

**Item Conditions** (anything else is rejected with 422):
- `New`
- `Foreign Used`
- `Locally Used`

**Buy Direct:**
- `Yes` - Buyers can pay online; payment is held in escrow until they confirm receipt. Requires the seller's bank details (`PUT /api/user/payment-info`).
- `No` - Contact seller only

**Shipment:**
- `Ship` - Seller can deliver
- `Pickup` - Pickup only (default)

Courier/shipping companies are disabled in Ethiopia — do not send `shipping[]`.

### Update Advert Status
```json
{
    "status": "active"
}
```

**Statuses:**
- `active`
- `inactive`
- `pending`

### Mark Advert as Sold
```json
{
    "sold": "Yes",
    "sold_date": "2026-01-13"
}
```

### Report Advert
```json
{
    "reason": "spam",
    "description": "This advert appears to be spam or fraudulent"
}
```

**Report Reasons:**
- `spam`
- `fraud`
- `inappropriate`
- `duplicate`
- `other`

### Apply for Job Posting
```json
{
    "cover_letter": "I am interested in this position and would like to apply...",
    "cv": "base64_encoded_cv_or_file_path",
    "phone": "0911234567"
}
```

---

## 💬 Messages

### Send Message
```json
{
    "receiver_id": "12345",
    "advert_id": "67890",
    "body": "Hello, is this item still available?",
    "type": "text"
}
```

**Message Types:**
- `text`
- `image`
- `offer`

### Archive Conversation
```json
{
    "advert_id": "67890",
    "user_id": "12345"
}
```

### Unarchive Conversation
```json
{
    "advert_id": "67890",
    "user_id": "12345"
}
```

---

## 💳 Payments

### Buy Direct: get quote (`POST /api/shipping/calculate/{advertId}`)
```json
{
    "first_name": "Abebe",
    "last_name": "Kebede",
    "phone": "0911234567",
    "state": 1,
    "lga": "Bole",
    "address": "Near Edna Mall, House 12"
}
```
`state` = region id, `lga` = zone name from `GET /api/locations/states/{id}/lgas`. The response contains `data.checkout`.

### Buy Direct: initialize payment (`POST /api/payments/initialize`)
Send `data.checkout` from the quote unchanged. Amounts are computed on the server (item price + admin-set commission).
```json
{
    "advert_id": 67890,
    "first_name": "Abebe",
    "last_name": "Kebede",
    "phone": "0911234567",
    "state": 1,
    "lga": "Bole",
    "address": "Near Edna Mall, House 12"
}
```

### Initialize Advert Boost
```json
{
    "advert_id": "67890",
    "boost_type": "featured",
    "duration": 7,
    "amount": 5000
}
```

**Boost Types:**
- `featured` - Show in featured section
- `urgent` - Mark as urgent
- `top_ad` - Show at top

**Duration:** Number of days (typically 7, 14, or 30)

### Confirm Receipt (`POST /api/user/payments/{paymentId}/confirm-delivery`)
No body. Releases the escrowed payment to the seller.

### Report a Problem (`POST /api/user/payments/{paymentId}/report-problem`)
```json
{
    "reason": "The item arrived damaged and does not power on."
}
```

### Seller ships / cancels (`PATCH /api/user/payments/{paymentId}/shipping-status`)
```json
{
    "seller_status": "shipped"
}
```

---

## 🔍 Search & Filter

### Advanced Search
```json
{
    "keyword": "samsung",
    "category": "4",
    "min_price": 10000,
    "max_price": 1000000,
    "state": "Addis Ababa",
    "condition": "New"
}
```

**Filter Options:**
- `keyword` - Search term
- `category` - Category ID
- `sub_category` - Subcategory ID
- `brand` - Brand ID
- `min_price` / `max_price` - Price range
- `state` - State/Location
- `condition` - Item condition
- `sort` - Sort by (`newest`, `oldest`, `price_low`, `price_high`)

---

## 📍 Locations & Shipping

### Delivery
Courier shipping is disabled in Ethiopia — buyer and seller arrange delivery directly. See the Buy Direct quote above (`shipping_cost` is always 0).

---

## 👤 User Profile Management

### Update Address
```json
{
    "state": "Addis Ababa",
    "lga": "Bole",
    "address": "Near Edna Mall, House 12, Bole"
}
```

### Update Phone Number
```json
{
    "phone": "0911234567"
}
```

### Change Password
```json
{
    "current_password": "oldpassword123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

### Update Notification Preferences
```json
{
    "push_notifications": true,
    "email_notifications": true,
    "sms_notifications": false
}
```

### Submit Verification Documents
```json
{
    "id_type": "national_id",
    "id_number": "12345678901",
    "id_document": "base64_encoded_image_or_file_path"
}
```

**ID Types:**
- `national_id`
- `drivers_license`
- `passport`
- `voters_card`

### Update Payment Information
```json
{
    "bank_name": "GTBank",
    "account_number": "0123456789",
    "account_name": "John Doe"
}
```

---

## ⭐ Wishlist & Following

### Add to Wishlist
```json
{
    "note": "Interested in this item"
}
```

### Follow/Unfollow User
```json
{
    "following_id": "12345"
}
```
**Note:** This toggles - if following, will unfollow; if not following, will follow

---

## 📝 Feedbacks & Ratings

### Submit Feedback
```json
{
    "rating": 5,
    "comment": "Great seller! Fast delivery and excellent communication.",
    "advert_id": "67890"
}
```

**Rating:** 1-5 stars

---

## 🚫 Block Users

### Block User
```json
{
    "blocked_user_id": "12345",
    "reason": "spam"
}
```

**Block Reasons:**
- `spam`
- `harassment`
- `fraud`
- `inappropriate`
- `other`

### Unblock User
```json
{
    "blocked_user_id": "12345"
}
```

---

## 🚀 Ad Boost Management

### Create Boost Request
```json
{
    "boost_type": "featured",
    "duration": 7,
    "payment_proof": "base64_encoded_image_or_file_path"
}
```

### Upload Payment Proof
```json
{
    "payment_proof": "base64_encoded_image_or_file_path"
}
```

---

## 💡 Tips for Using Sample Data

### 1. Update Email & Phone
Replace sample email and phone with your test account:
```json
{
    "email": "your-test-email@example.com",
    "phone": "09XXXXXXXX"
}
```

### 2. Use Variables for IDs
After creating resources, use Postman variables:
```json
{
    "advert_id": "{{advert_id}}",
    "user_id": "{{user_id}}"
}
```

### 3. File Uploads
For image/document uploads, you have two options:

**Option A:** Base64 encoded string
```json
{
    "image": "data:image/png;base64,iVBORw0KGgoAAAANS..."
}
```

**Option B:** Use form-data in Postman
1. Change body type to "form-data"
2. Add key as "image"
3. Select file from your computer

### 4. Test Sequence
For best results, test in this order:
1. **Register** → Get account
2. **Login** → Get auth token
3. **Create Advert** → Get advert ID
4. **Test other endpoints** → Using created resources

### 5. Environment Variables
Pre-filled variables that auto-update:
- `{{auth_token}}` - Auto-saved after login
- `{{user_id}}` - Auto-saved after register/login
- `{{advert_id}}` - Auto-saved after creating advert
- `{{base_url}}` - Automatically uses selected environment

---

## 🔄 Common Field Values

### Regions (Ethiopia)
- `Addis Ababa`
- `Oromia`
- `Amhara`
- `Tigray`
- `Sidama`
- `Dire Dawa`
- etc. (full list: `GET /api/locations/states`)

### Categories (Sample IDs)
- `1` - Vehicles
- `2` - Property
- `3` - Electronics
- `4` - Mobile Phones
- `5` - Fashion
- `6` - Home & Garden
- `7` - Jobs
- etc.

### Phone Format
- 10 digits starting with `09` or `07` (no spaces or `+251`)
- Example: `0911234567`

### Date Format
- `YYYY-MM-DD`
- Example: `2026-01-13`

---

## ✅ Ready-to-Test Features

All these endpoints include sample data:
- ✅ Authentication (Register, Login, OTP, Password Reset)
- ✅ Advert Management (Create, Update, Delete)
- ✅ Messages (Send, Archive, Read)
- ✅ Payments (Initialize, Confirm)
- ✅ Profile Updates (Address, Phone, Password)
- ✅ Notifications (Device Tokens, Settings)
- ✅ Search & Filters
- ✅ User Management
- ✅ Feedbacks & Ratings
- ✅ Wishlist & Following
- ✅ Block Users
- ✅ Ad Boosts

**Just import the collection and start testing!** 🚀

---

## 📞 Need Help?

- **Collection has no data?** Re-import the latest collection file
- **Wrong data format?** Check this guide for correct format
- **Test failing?** Verify you're logged in and have valid token
- **Need different data?** Edit the request body directly in Postman

---

**All sample data is production-safe** - designed for testing without causing issues! 🛡️

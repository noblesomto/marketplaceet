# Marketplace Ethiopia - Postman API Collection

## 📦 Complete API Documentation for Mobile App Integration

This directory contains a production-ready Postman collection for the Marketplace Ethiopia API, designed for seamless mobile app integration.

---

## 🚀 Quick Start

### 1. Import Collection & Environments

1. **Open Postman**
2. **Import Files:**
   - `Marketplace-API-Complete.postman_collection.json`
   - `environments/Local.postman_environment.json`
   - `environments/Production.postman_environment.json`

3. **Select Environment:**
   - Click the environment dropdown (top right)
   - Select "Marketplace Ethiopia - Local" or "Production"

### 2. Authenticate

1. Open the collection
2. Navigate to: **1. Authentication > Login**
3. Update credentials in the request body
4. Click **Send**
5. ✅ Token is **automatically saved** to environment
6. All other requests will now work!

---

## 📁 Complete API Endpoints (100+ endpoints)

### 1. Authentication (8 endpoints)
- Register, Login, OTP Verification
- Password Reset, Social Login
- Auto-token management

### 2. Adverts (Public & Protected)
- Browse, Search, Filter
- Create, Update, Delete
- Report, Featured listings

### 3. Categories & Locations
- States, Cities, Categories
- Dynamic filtering

### 4. Messages (10 endpoints)
- Conversations, Unread count
- Archive, Mark as read

### 5. Payments (6 endpoints)
- Initialize, Callback
- Payment tracking

### 6. Boost Management (9 endpoints)
- Dynamic pricing
- Calculate costs
- Upload proof

### 7. User Profile (12 endpoints)
- Update profile
- Verification
- Settings

### 8. Statistics & Analytics
- User stats
- Advert metrics

---

## 🔐 Automatic Authentication

**No manual token management needed!**

```javascript
// After login, automatically runs:
pm.environment.set("access_token", jsonData.access_token);
pm.environment.set("user_id", jsonData.user.user_id);
```

All protected endpoints automatically use `{{access_token}}` from environment.

---

## 🌍 Environment Variables

### Auto-Saved Variables:
- `access_token` - Bearer token (saved on login)
- `user_id` - Current user ID
- `advert_id` - Last viewed advert
- `seller_id` - Last viewed seller
- And 10+ more...

### Manual Variables:
- `base_url` - API endpoint URL
- `category_id`, `brand_id`, etc.

---

## ✅ Built-in Tests

Every request includes validation:
- HTTP status codes
- Response structure
- Required fields
- Auto-variable extraction

---

## 📝 Sample Requests

### Create Advert
\`\`\`json
{
  "ad_title": "iPhone 13 Pro",
  "category": 1,
  "price": 450000,
  "description": "Brand new",
  "state": 25
}
\`\`\`

### Send Message
\`\`\`json
{
  "advert_id": 123,
  "receiver_id": "USR456",
  "message": "Is this available?"
}
\`\`\`

---

## 🔧 Troubleshooting

### 401 Unauthorized
→ Run Login request again (token auto-updates)

### 422 Validation Error
→ Check error messages in response

### 500 Server Error
→ Verify environment selection

---

## 📱 Mobile Integration

### Recommended:
- **React Native:** axios + AsyncStorage
- **Flutter:** dio + shared_preferences
- **Native:** URLSession (iOS), Retrofit (Android)

### Token Storage Example:
\`\`\`javascript
// Save after login
await AsyncStorage.setItem('access_token', token);

// Use in headers
headers: {
  'Authorization': \`Bearer \${token}\`,
  'Accept': 'application/json'
}
\`\`\`

---

## 🎯 Common Workflows

### Browse & Buy (Buy Direct escrow):
1. GET /api/adverts/{id}
2. GET /api/adverts/{id}/buy-direct
3. POST /api/shipping/calculate/{id}  (delivery details -> `checkout`)
4. POST /api/payments/initialize  (body = `checkout`) -> open `authorization_url`
5. POST /api/payments/callback  (`reference`)
6. Seller: PATCH /api/user/payments/{id}/shipping-status `{seller_status: shipped}`
7. Buyer: POST /api/user/payments/{id}/confirm-delivery (or report-problem)

### Post Advert:
1. GET /api/adverts/create/data
2. (Buy Direct only) PUT /api/user/payment-info — bank details required first
3. POST /api/adverts
3. POST /api/adverts/{id}/boost

---

## 📊 Quick API Reference

| Category | Endpoints | Auth Required |
|----------|-----------|---------------|
| Authentication | 8 | No (except logout) |
| Adverts (Public) | 6 | No |
| Adverts (Manage) | 8 | Yes |
| Messages | 10 | Yes |
| Payments | 6 | Yes |
| User Profile | 12 | Yes |
| Boost | 9 | Mixed |
| Search | 7 | No |
| Statistics | 7 | Yes |
| **Total** | **100+** | Mixed |

---

## 🎉 Ready to Use!

✅ Collection with 100+ endpoints
✅ Two environments (Local & Production)
✅ Automatic authentication
✅ Built-in validation tests
✅ Sample data included
✅ Mobile-app ready

**Import and start testing immediately!**

---

*Marketplace Ethiopia API v1.0*
*Complete Documentation*

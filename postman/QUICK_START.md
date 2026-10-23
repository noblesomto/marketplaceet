# 🚀 Quick Start Guide - Postman Collection

Get started with the Marketplace Ethiopia API in 3 minutes!

## 📦 Files You Need

All files are in the `postman/` folder:

1. ✅ **Marketplace-API-Complete.postman_collection.json** - Main collection (135 endpoints)
2. ✅ **Local-Environment.postman_environment.json** - Local testing environment
3. ✅ **Production-Environment.postman_environment.json** - Production environment
4. 📖 **README.md** - Complete documentation

## ⚡ 3-Minute Setup

### Step 1: Import into Postman (30 seconds)

1. Open Postman
2. Click **Import** button (top left)
3. Drag and drop ALL 3 files:
   - `Marketplace-API-Complete.postman_collection.json`
   - `Local-Environment.postman_environment.json`
   - `Production-Environment.postman_environment.json`
4. Click **Import**

### Step 2: Select Environment (10 seconds)

- Click the environment dropdown (top right)
- Select **"Marketplace Ethiopia - Local"** for local testing
- OR **"Marketplace Ethiopia - Production"** for production

### Step 3: Test Your First Request (2 minutes)

1. **Expand** `01. Authentication` folder
2. **Click** on `Login` request
3. **Click** the **Body** tab
4. **Update** the JSON:
   ```json
   {
     "email": "your@email.com",
     "password": "your_password"
   }
   ```
5. **Click** the blue **Send** button
6. 🎉 **Success!** Your auth token is automatically saved!

### Step 4: Test Protected Endpoint (30 seconds)

1. **Expand** `11. User Management` folder
2. **Click** on `User  Profile` request
3. **Click** the blue **Send** button
4. 🎉 You'll see your user profile data!

**That's it!** You're ready to test all 135 endpoints!

---

## 🎯 What You Can Do Now

### Test Public Endpoints (No Auth)
- ✅ Browse adverts: `GET /api/adverts`
- ✅ Search: `GET /api/search`
- ✅ View categories: `GET /api/categories`
- ✅ Get locations: `GET /api/locations/states`

### Test Protected Endpoints (With Auth)
- ✅ Get your profile: `GET /api/user/profile`
- ✅ Create advert: `POST /api/user/adverts`
- ✅ Send messages: `POST /api/messages`
- ✅ Manage payments: `GET /api/user/payments`

### Test Notifications (Local Only)
- ✅ Register device: `POST /api/device-tokens`
- ✅ Send test notification: `POST /api/test/notification`
- ✅ View your tokens: `GET /api/test/my-tokens`

---

## 💡 Key Features

### ✨ Automatic Token Management
- Login once, token auto-saved
- No copy-paste needed
- Works for all protected endpoints

### 📁 Organized Structure
- 37 folders organized by feature
- 135 endpoints total
- Easy to find what you need

### 🌍 Dual Environment Support
- **Local**: `http://127.0.0.1:8030`
- **Production**: `https://www.marketplace.ng`
- Switch with one click

### 🔒 Authentication Indicators
- 🌐 Public endpoints (no auth)
- 🔒 Protected endpoints (requires auth)
- Clear descriptions for each

---

## 📱 Collection Overview

```
├── 01. Authentication (10 endpoints)
│   Register, Login, Verify, Password Reset, etc.
│
├── 02. Adverts (17 endpoints)
│   Browse, View, Search, Featured, etc.
│
├── 03. Categories (4 endpoints)
│   Categories, Subcategories, Brands
│
├── 04. Search (8 endpoints)
│   Search, Filter, Suggestions
│
├── 05. Locations (6 endpoints)
│   States, Cities, Shipping
│
├── 06. Messages (11 endpoints)
│   Send, View, Archive, Mark Read
│
├── 07. Payments (6 endpoints)
│   Initialize, Callback, History
│
├── 08. Ad Boosts (6 endpoints)
│   Create, View, Upload Proof
│
├── 09. Notifications (5 endpoints)
│   Device Tokens, Settings
│
├── 10. Testing - Local Only (2 endpoints)
│   Test Notifications
│
├── 11. User Management (48 endpoints)
│   Profile, Dashboard, Adverts, Settings, etc.
│
└── 12. Real-time (1 endpoint)
    Broadcasting Auth
```

---

## 🎓 Common Workflows

### Workflow 1: Browse and View Adverts
```
1. GET /api/adverts           → Get all adverts
2. GET /api/adverts/{id}      → View specific advert
3. GET /api/categories        → Browse categories
```

### Workflow 2: Create and Manage Advert
```
1. POST /api/login            → Get auth token (auto-saved)
2. POST /api/user/adverts     → Create new advert
3. GET /api/user/adverts      → View my adverts
4. PUT /api/user/adverts/{id} → Update advert
```

### Workflow 3: Messaging
```
1. POST /api/login                → Login
2. POST /api/messages             → Send message
3. GET /api/messages/conversations → View conversations
4. PUT /api/messages/{id}/read    → Mark as read
```

### Workflow 4: Test Notifications (Local)
```
1. POST /api/login                → Login
2. POST /api/device-tokens        → Register device
3. POST /api/test/notification    → Send test
4. GET /api/test/my-tokens        → View tokens
```

---

## 🔧 Environment Variables

### Pre-configured Variables

| Variable | Local | Production | Auto-Set? |
|----------|-------|------------|-----------|
| `base_url` | http://127.0.0.1:8030 | https://www.marketplace.ng | ✅ |
| `auth_token` | (empty) | (empty) | ✅ After login |
| `test_email` | test@example.com | your-email@example.com | ❌ Update |
| `test_password` | password | your-password | ❌ Update |

### How to Update Variables

1. Click environment dropdown (top right)
2. Click the eye icon 👁️
3. Click **Edit**
4. Update values
5. Click **Save**

---

## 🐛 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| **401 Unauthorized** | Login first to get token |
| **404 Not Found** | Check base_url in environment |
| **Token not saving** | Check Tests tab in Login request |
| **Wrong environment** | Select correct environment (Local/Prod) |
| **Server not running** | Start: `php artisan serve --port=8030` |

---

## 📚 Next Steps

1. ✅ **Explore the collection** - Browse through all folders
2. ✅ **Read full docs** - Check `README.md` for details
3. ✅ **Test workflows** - Try the common workflows above
4. ✅ **Customize** - Save your own test data to variables

---

## 🎉 You're All Set!

**Collection Stats:**
- ✅ 135 API endpoints
- ✅ 37 organized folders
- ✅ 2 environments (Local & Production)
- ✅ Automatic token management
- ✅ Complete documentation

**Happy Testing! 🚀**

For detailed documentation, see: `postman/README.md`

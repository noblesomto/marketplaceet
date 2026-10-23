# Quick Start: Test Push Notifications Locally

## 🚀 Fastest Way to Test (3 Steps)

### Step 1: Open Test Interface
Open in your browser:
```
http://127.0.0.1:8030/test-notifications.html
```

### Step 2: Get API Token
Login to get your bearer token:
```bash
curl -X POST http://127.0.0.1:8030/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"your@email.com","password":"password"}'
```

### Step 3: Register & Test
1. Paste your API token in the web interface
2. Click "Register Token"
3. Click "Send Notification"
4. See the notification appear instantly! 🎉

---

## Alternative: Command Line Testing

```bash
# Quick test - sends to first active token
php artisan notification:test

# Test specific user
php artisan notification:test --user=123

# Custom message
php artisan notification:test --message="Hello from terminal!"
```

---

## What Was Added

### 1. Web Test Interface
**File**: `public/test-notifications.html`
- Beautiful UI for testing notifications
- No mobile app needed
- Real-time notification display
- Token management

### 2. Artisan Command
**Command**: `php artisan notification:test`
- Test from command line
- Multiple targeting options
- Detailed feedback

### 3. Test API Endpoints
- `POST /api/test/notification` - Send test notification
- `GET /api/test/my-tokens` - View your registered tokens

### 4. Documentation
- `docs/PUSH_NOTIFICATION_TESTING.md` - Complete guide
- `docs/QUICK_START_PUSH_NOTIFICATIONS.md` - This file

---

## API Examples

### Register Device Token
```bash
curl -X POST http://127.0.0.1:8030/api/device-tokens \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "token": "test-device-token-123",
    "platform": "android"
  }'
```

### Send Test Notification
```bash
curl -X POST http://127.0.0.1:8030/api/test/notification \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Notification",
    "message": "Hello from API!",
    "data": {"test": true}
  }'
```

---

## Troubleshooting

### FCM Key Not Set?
Add to your `.env`:
```env
FCM_SERVER_KEY=your_firebase_server_key
```

### No Tokens Found?
Register a token first using the web interface or API.

### More Help?
Read the complete guide: `docs/PUSH_NOTIFICATION_TESTING.md`

---

## Production Ready?

Before deploying:
1. ✅ Test with real devices
2. ✅ Verify FCM key for production
3. ✅ Test endpoints auto-disable in production
4. ✅ Monitor logs after deployment

---

**🎉 Happy Testing!**

For detailed documentation, see: `docs/PUSH_NOTIFICATION_TESTING.md`

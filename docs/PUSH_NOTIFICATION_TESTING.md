# Push Notification Testing Guide

This guide explains how to test push notifications locally before your mobile app goes live.

## Overview

The push notification system uses Firebase Cloud Messaging (FCM) to deliver notifications to mobile devices. For local testing, we've created several tools that allow you to test without needing the actual mobile app.

## Prerequisites

1. **FCM Server Key**: Make sure you have your Firebase Cloud Messaging Server Key configured:
   ```env
   FCM_SERVER_KEY=your_firebase_server_key_here
   ```

2. **User Account**: You need a registered user account to test notifications

3. **API Token**: Login to get a bearer token:
   ```bash
   curl -X POST http://127.0.0.1:8030/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"your@email.com","password":"password"}'
   ```

## Testing Methods

### Method 1: Web-Based Test Interface (Recommended for Quick Testing)

The easiest way to test push notifications locally.

#### Step 1: Open the Test Interface

Navigate to: http://127.0.0.1:8030/test-notifications.html

#### Step 2: Copy Your Device Token

The page will automatically generate a test device token. Click "Copy Token" to copy it to your clipboard.

#### Step 3: Register the Token

1. Paste your API bearer token in the "Your API Bearer Token" field
2. Select platform (Android or iOS)
3. Click "Register Token"

#### Step 4: Send a Test Notification

1. Enter a title and message
2. Click "Send Notification"
3. You'll see the notification appear in the "Received Notifications" section

**Features:**
- ✅ No mobile app required
- ✅ Instant feedback
- ✅ Visual notification display
- ✅ See notification data/payload
- ✅ Browser notifications (if permitted)

---

### Method 2: Artisan Command (For Backend Testing)

Use this method to test notifications from the command line.

#### Basic Usage

```bash
# Send to the first active device token in database
php artisan notification:test

# Send to specific user's devices
php artisan notification:test --user=123

# Send to specific device token
php artisan notification:test --token="cDNfP3RreWVyLUF..."

# Send to all active devices
php artisan notification:test --all

# Custom notification
php artisan notification:test \
  --user=123 \
  --title="Hello User!" \
  --message="Custom test message"
```

#### Output Example

```
🔔 Testing Push Notification System

✅ FCM Server Key: Configured

📱 Found 2 device token(s)

📤 Sending notification...
   Title: Test Notification from Marketplace
   Body: This is a test push notification sent at 15:30:45

✅ Notification sent successfully!
🔍 Check the device(s) to see the notification
📊 Check logs for detailed results: storage/logs/laravel.log
```

---

### Method 3: API Endpoint (For Integration Testing)

Use the REST API to send test notifications. Perfect for testing with Postman or curl.

#### Register Device Token

```bash
POST /api/device-tokens
Authorization: Bearer {your-token}
Content-Type: application/json

{
  "token": "cDNfP3RreWVyLUF...",
  "platform": "android"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Device token registered successfully",
  "data": {
    "id": 1,
    "platform": "android"
  }
}
```

#### Send Test Notification

```bash
POST /api/test/notification
Authorization: Bearer {your-token}
Content-Type: application/json

{
  "title": "Test Notification",
  "message": "Hello from API!",
  "data": {
    "custom_field": "value"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Test notification sent successfully",
  "data": {
    "tokens_count": 1,
    "notification": {
      "title": "Test Notification",
      "body": "Hello from API!"
    },
    "data": {
      "type": "test_notification",
      "timestamp": "2026-01-13T10:30:00+00:00",
      "test_id": "test_abc123",
      "custom_field": "value"
    }
  }
}
```

#### Target Specific User

```bash
POST /api/test/notification
Authorization: Bearer {your-token}
Content-Type: application/json

{
  "message": "Hello User!",
  "target_user_id": 123
}
```

#### Get Your Registered Tokens

```bash
GET /api/test/my-tokens
Authorization: Bearer {your-token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user_tokens": [
      {
        "id": 1,
        "platform": "android",
        "token": "cDNfP...",
        "is_active": true,
        "last_used_at": "2026-01-13T10:30:00.000000Z"
      }
    ],
    "total_count": 1,
    "active_count": 1
  }
}
```

---

## Using Postman

### Collection Setup

1. **Import the API collection**: Import `public/docs/collection.json` into Postman

2. **Set Environment Variables**:
   - `base_url`: http://127.0.0.1:8030
   - `token`: Your bearer token from login

3. **Test Sequence**:
   1. Call `POST /api/login` to get token
   2. Call `POST /api/device-tokens` to register a test token
   3. Call `POST /api/test/notification` to send notification
   4. Check `GET /api/test/my-tokens` to verify registration

---

## Testing with Mobile Simulators

### Android Emulator

If you have an Android emulator, you can test with a real FCM token:

1. **Get FCM Token from Emulator**:
   - Use the mobile app's FCM token registration
   - Or use Firebase Console to send test messages

2. **Register Token**:
   ```bash
   curl -X POST http://10.0.2.2:8030/api/device-tokens \
     -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     -d '{"token":"real-fcm-token","platform":"android"}'
   ```
   *Note: Use 10.0.2.2 to access localhost from Android emulator*

3. **Send Notification**:
   ```bash
   php artisan notification:test --token="real-fcm-token"
   ```

### iOS Simulator

Similar process for iOS:

1. Get FCM token from iOS simulator
2. Register using `http://localhost:8030/api/device-tokens`
3. Send notification via artisan command or API

---

## Notification Payload Structure

### Standard Notification Payload

```json
{
  "notification": {
    "title": "Message Title",
    "body": "Message body text",
    "sound": "default",
    "badge": 1
  },
  "data": {
    "type": "notification_type",
    "custom_field": "value"
  },
  "priority": "high",
  "content_available": true
}
```

### Message Notification Example

```json
{
  "notification": {
    "title": "John Doe",
    "body": "Hey! Are you still selling...",
    "image": "https://example.com/profile.jpg"
  },
  "data": {
    "type": "new_message",
    "message_id": "12345",
    "sender_id": "67890",
    "sender_name": "John Doe",
    "conversation_id": "conv_123",
    "timestamp": "2026-01-13T10:30:00+00:00",
    "click_action": "OPEN_CONVERSATION"
  }
}
```

---

## Troubleshooting

### Issue: "FCM_SERVER_KEY is not configured"

**Solution**: Add your Firebase Server Key to `.env`:
```env
FCM_SERVER_KEY=AAAAxxxxxxxx:APA91bHxxxxxxxxxxxxxxxxxxxxxxxx
```

Get it from: Firebase Console > Project Settings > Cloud Messaging > Server Key

---

### Issue: "No device tokens found"

**Solution**: Register a device token first:
```bash
curl -X POST http://127.0.0.1:8030/api/device-tokens \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"token":"test-token-123","platform":"android"}'
```

---

### Issue: Notification sent but not received

**Check:**
1. ✅ FCM Server Key is correct
2. ✅ Device token is valid
3. ✅ Token is marked as `is_active: true` in database
4. ✅ User has notifications enabled
5. ✅ Check Laravel logs: `tail -f storage/logs/laravel.log`

**View FCM Response in Logs:**
```bash
grep "FCM Response" storage/logs/laravel.log
```

---

### Issue: "Failed to send notification"

**Common Causes:**
- Invalid FCM Server Key
- Network connectivity issues
- Invalid device token format

**Debug:**
```bash
# Check FCM configuration
php artisan tinker
>>> config('services.fcm.server_key')

# Check recent logs
tail -20 storage/logs/laravel.log
```

---

### Issue: Test endpoint returns 403

**Reason**: Test endpoints only work in local/development environment

**Solution**: Check `APP_ENV` in `.env`:
```env
APP_ENV=local
```

---

## Monitoring Notifications

### Check Logs

```bash
# View all FCM-related logs
grep "FCM" storage/logs/laravel.log

# Follow logs in real-time
tail -f storage/logs/laravel.log | grep "FCM"

# Check for errors
grep "FCM Error" storage/logs/laravel.log
```

### Database Queries

```bash
php artisan tinker
```

```php
// Get all active device tokens
\App\Models\DeviceToken::where('is_active', true)->get();

// Get tokens for specific user
\App\Models\User::find(123)->deviceTokens;

// Count active tokens
\App\Models\DeviceToken::where('is_active', true)->count();

// Recently used tokens
\App\Models\DeviceToken::where('is_active', true)
    ->orderBy('last_used_at', 'desc')
    ->limit(10)
    ->get(['id', 'user_id', 'platform', 'last_used_at']);
```

---

## Production Deployment

### Before Going Live

1. **Test thoroughly** using all methods above
2. **Verify FCM Server Key** is correct for production
3. **Test with real devices** (at least one Android and one iOS)
4. **Disable test endpoints** in production (automatic if `APP_ENV=production`)
5. **Monitor logs** for the first few days

### Environment Variables

```env
# Production
APP_ENV=production
FCM_SERVER_KEY=your_production_firebase_key
```

### Security Notes

- ✅ Test endpoints automatically disabled in production
- ✅ All test routes require authentication
- ✅ Device tokens are user-scoped
- ✅ Invalid tokens automatically marked inactive

---

## Best Practices

1. **Clean Up Test Tokens**: Periodically remove test tokens from database
   ```sql
   DELETE FROM device_tokens WHERE token LIKE 'test-%';
   ```

2. **Monitor Invalid Tokens**: System automatically marks invalid tokens as inactive

3. **Test Different Scenarios**:
   - New message notifications
   - Multiple devices for same user
   - iOS and Android platforms
   - Long notification messages
   - Notifications with images

4. **Use Meaningful Test Data**: Use realistic titles and messages during testing

---

## Quick Reference

| Method | Use Case | Pros | Cons |
|--------|----------|------|------|
| Web Interface | Quick testing, demos | Fast, visual, no setup | Not real FCM |
| Artisan Command | Backend testing, automation | Real FCM, scriptable | Needs CLI access |
| API Endpoint | Integration testing | Real FCM, API-like | Needs API client |
| Mobile Simulator | Final testing | Most realistic | Requires simulator |

---

## Additional Resources

- [Firebase Cloud Messaging Documentation](https://firebase.google.com/docs/cloud-messaging)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [API Documentation](http://127.0.0.1:8030/docs)

---

## Support

If you encounter issues:
1. Check this guide first
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check FCM Response logs
4. Verify environment configuration
5. Test with a fresh device token

---

**Happy Testing! 🎉**

# 📱 Mobile Developer Integration Checklist

**Quick reference guide for integrating push notifications into iOS/Android apps**

---

## 📋 Pre-Integration Requirements

### 1. Firebase Setup

**You Need**:
- [ ] Firebase Project ID: `_______________`
- [ ] Android: `google-services.json` file
- [ ] iOS: `GoogleService-Info.plist` file
- [ ] Firebase Console access (optional)

**Where to Get**:
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select project
3. Add Android/iOS app
4. Download config files

---

### 2. API Access

**Base URLs**:
```
Production: https://www.marketplace.ng/api
Local Dev:  http://127.0.0.1:8030/api
```

**Authentication**:
All endpoints require: `Authorization: Bearer {token}`

Get token from login:
```bash
POST /api/login
{
  "email": "user@example.com",
  "password": "password"
}
```

---

## 🔧 Integration Steps (Flutter/React Native)

### Step 1: Add Dependencies

**Flutter** (`pubspec.yaml`):
```yaml
dependencies:
  firebase_core: ^2.24.0
  firebase_messaging: ^14.7.0
  flutter_local_notifications: ^16.3.0
  flutter_app_badger: ^1.5.0
```

**React Native**:
```bash
npm install @react-native-firebase/app
npm install @react-native-firebase/messaging
```

---

### Step 2: Configure Platform

**Android** (`android/app/build.gradle`):
```gradle
android {
    defaultConfig {
        minSdkVersion 21  // Required for FCM
    }
}

dependencies {
    implementation platform('com.google.firebase:firebase-bom:32.7.0')
    implementation 'com.google.firebase:firebase-messaging'
}
```

Add `google-services.json` to `android/app/`

**iOS** (`ios/Podfile`):
```ruby
platform :ios, '12.0'
```

Add `GoogleService-Info.plist` to `ios/Runner/`

Enable in Xcode:
- Push Notifications capability
- Background Modes → Remote notifications

---

### Step 3: API Integration

#### A. Register Device Token (After Login)

```dart
// Flutter Example
import 'package:firebase_messaging/firebase_messaging.dart';

Future<void> registerDeviceToken() async {
  // Get FCM token
  String? token = await FirebaseMessaging.instance.getToken();

  if (token != null) {
    // Register with backend
    final response = await http.post(
      Uri.parse('https://www.marketplace.ng/api/device-tokens'),
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'token': token,
        'platform': Platform.isIOS ? 'ios' : 'android',
      }),
    );

    if (response.statusCode == 201) {
      print('Device registered for notifications');
    }
  }
}
```

#### B. Handle Incoming Notifications

```dart
// Listen to notifications
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  print('Notification received: ${message.notification?.title}');

  // Show local notification
  _showLocalNotification(message);
});

// Handle notification tap
FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
  // Navigate based on notification type
  _handleNotificationTap(message);
});
```

#### C. Unregister on Logout

```dart
Future<void> unregisterDevice() async {
  // Get your device token ID (stored during registration)
  final deviceTokenId = await storage.getDeviceTokenId();

  // Unregister from backend
  await http.delete(
    Uri.parse('https://www.marketplace.ng/api/device-tokens/$deviceTokenId'),
    headers: {
      'Authorization': 'Bearer $authToken',
    },
  );

  // Delete local FCM token
  await FirebaseMessaging.instance.deleteToken();
}
```

---

## 📤 API Endpoints Reference

### 1. Register Device Token

```http
POST /api/device-tokens
Authorization: Bearer {token}
Content-Type: application/json

{
  "token": "fcm_device_token_here",
  "platform": "android"
}
```

**Response**:
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

---

### 2. Get User's Device Tokens

```http
GET /api/device-tokens
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "platform": "android",
      "is_active": true,
      "last_used_at": "2026-01-13T10:30:00.000000Z"
    }
  ]
}
```

---

### 3. Delete Device Token

```http
DELETE /api/device-tokens/{id}
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "Device token removed successfully"
}
```

---

### 4. Get Notification Settings

```http
GET /api/notification-settings
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "push_notifications": true,
    "email_notifications": true,
    "message_notifications": true,
    "order_notifications": true
  }
}
```

---

### 5. Update Notification Settings

```http
PUT /api/notification-settings
Authorization: Bearer {token}
Content-Type: application/json

{
  "push_notifications": true,
  "message_notifications": true,
  "order_notifications": false
}
```

---

### 6. Get Unread Messages Count (For Badge)

```http
GET /api/unread-messages-count
Authorization: Bearer {token}
```

**Response**:
```json
{
  "count": 5
}
```

---

## 📬 Notification Payload Structures

### Message Notification

```json
{
  "notification": {
    "title": "John Doe",
    "body": "Hey! Are you still selling the iPhone?",
    "image": "https://example.com/profile.jpg"
  },
  "data": {
    "type": "new_message",
    "message_id": "12345",
    "sender_id": "67890",
    "sender_name": "John Doe",
    "conversation_id": "conv_123",
    "click_action": "OPEN_CONVERSATION"
  }
}
```

**Handle in App**:
```dart
if (data['type'] == 'new_message') {
  final conversationId = data['conversation_id'];
  Navigator.pushNamed(context, '/conversation',
    arguments: conversationId);
}
```

---

### Order Update

```json
{
  "notification": {
    "title": "Order Update",
    "body": "Your order has been shipped!"
  },
  "data": {
    "type": "order_update",
    "order_id": "12345",
    "status": "shipped"
  }
}
```

---

### Follower Notification (New Ad)

```json
{
  "notification": {
    "title": "New Ad from Seller",
    "body": "Check out: Samsung Galaxy S24"
  },
  "data": {
    "type": "follower_notification",
    "ad_id": "123",
    "title_slug": "samsung-galaxy-s24",
    "state_slug": "lagos"
  }
}
```

---

## 🧪 Testing

### Method 1: Use Test Endpoint

```bash
curl -X POST https://www.marketplace.ng/api/test/notification \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Notification",
    "message": "Testing from API"
  }'
```

### Method 2: Firebase Console

1. Go to Firebase Console
2. Cloud Messaging → Send test message
3. Paste your FCM token
4. Send notification

### Method 3: Get Your Test Token

```dart
// Print FCM token for testing
final token = await FirebaseMessaging.instance.getToken();
print('Test Token: $token');
```

Copy and share with backend team for testing.

---

## ✅ Integration Checklist

### Development Phase

- [ ] Firebase dependencies installed
- [ ] Config files added (google-services.json / GoogleService-Info.plist)
- [ ] FCM token retrieval working
- [ ] Device token registration API working
- [ ] Notification permission requested
- [ ] Foreground notifications displaying
- [ ] Background notifications working
- [ ] Notification tap navigation working
- [ ] Badge count updating
- [ ] Logout unregistration working

### Testing Phase

- [ ] Tested on Android device
- [ ] Tested on iOS device (physical device, not simulator)
- [ ] Tested with app in foreground
- [ ] Tested with app in background
- [ ] Tested with app closed
- [ ] Tested notification click actions
- [ ] Tested badge count
- [ ] Tested multiple devices for same user
- [ ] Tested notification settings toggle
- [ ] Tested logout token cleanup

### Production Phase

- [ ] Production Firebase config
- [ ] Production API base URL
- [ ] APNs certificate uploaded (iOS)
- [ ] SHA-256 fingerprint added (Android)
- [ ] Release build tested
- [ ] App Store listing shows notification permission
- [ ] Privacy policy mentions notifications

---

## 🐛 Common Issues & Solutions

### Issue: FCM Token is null

**Solution**:
```dart
// Ensure Firebase is initialized first
await Firebase.initializeApp();

// Then get token
final token = await FirebaseMessaging.instance.getToken();
```

---

### Issue: Notifications not appearing (iOS)

**Checklist**:
- [ ] Physical device (not simulator)
- [ ] Push Notifications enabled in Xcode
- [ ] APNs certificate uploaded to Firebase
- [ ] App signed with correct provisioning profile
- [ ] Notification permissions granted

---

### Issue: Background notifications not working (Android)

**Solution**: Check battery optimization settings
```dart
// Request to disable battery optimization
// Add to AndroidManifest.xml:
<uses-permission android:name="android.permission.REQUEST_IGNORE_BATTERY_OPTIMIZATIONS"/>
```

---

## 📚 Documentation Files

**Comprehensive Guides** (Share these with your team):
1. `docs/FLUTTER_PUSH_NOTIFICATIONS.md` - Complete integration guide
2. `docs/PUSH_NOTIFICATION_TESTING.md` - Testing procedures
3. `postman/Marketplace-API-Complete.postman_collection.json` - API collection

---

## 📞 Support

**For Questions**:
- Check `docs/FLUTTER_PUSH_NOTIFICATIONS.md` first
- Review `docs/PUSH_NOTIFICATION_TESTING.md` for debugging
- Contact backend team with logs if issues persist

**Useful Logs**:
```dart
// Enable debug logging
FirebaseMessaging.instance.setAutoInitEnabled(true);

// Check token
print('FCM Token: ${await FirebaseMessaging.instance.getToken()}');

// Log all messages
FirebaseMessaging.onMessage.listen((message) {
  print('Message data: ${message.data}');
  print('Message notification: ${message.notification}');
});
```

---

## 🎯 Quick Start Timeline

**Estimated Integration Time**: 10-17 hours

| Day | Task | Hours |
|-----|------|-------|
| Day 1 | Firebase setup, config files | 2-4h |
| Day 2 | API integration, token registration | 4-6h |
| Day 3 | Notification handling, testing | 2-3h |
| Day 4 | UI polish, edge cases | 2-4h |

---

**Document Version**: 1.0
**API Version**: 3.0
**Last Updated**: 2026-02-16

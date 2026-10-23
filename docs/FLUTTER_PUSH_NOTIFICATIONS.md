# Flutter Push Notifications Integration Guide

Complete guide for integrating push notifications in the Marketplace Nigeria Flutter app using Firebase Cloud Messaging (FCM).

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Flutter Setup](#flutter-setup)
4. [API Endpoints](#api-endpoints)
5. [Implementation Guide](#implementation-guide)
6. [Code Examples](#code-examples)
7. [Notification Handling](#notification-handling)
8. [Testing](#testing)
9. [Troubleshooting](#troubleshooting)

---

## Overview

The push notification system uses **Firebase Cloud Messaging (FCM)** to deliver real-time notifications to users for:
- New messages
- Order updates
- Payment confirmations
- Advert status changes
- Follow notifications
- System alerts

### Architecture

```
Flutter App → FCM Token → Backend API → FCM Server → User Device
```

1. Flutter app gets FCM token
2. App registers token with backend API
3. Backend sends notifications via FCM
4. FCM delivers to user's device
5. App handles and displays notification

---

## Prerequisites

### Backend Requirements
- ✅ Firebase project configured
- ✅ FCM Server Key configured in backend
- ✅ API endpoints ready (documented below)

### Flutter Requirements
- Flutter SDK 3.0+
- Dart 2.17+
- Firebase project with FCM enabled

### Firebase Console Setup

1. **Create/Open Firebase Project**
   - Go to [Firebase Console](https://console.firebase.google.com/)
   - Select your project

2. **Add Android App**
   - Click "Add app" → Android
   - Package name: `com.marketplace.nigeria` (your package)
   - Download `google-services.json`
   - Place in `android/app/`

3. **Add iOS App**
   - Click "Add app" → iOS
   - Bundle ID: `com.marketplace.nigeria` (your bundle)
   - Download `GoogleService-Info.plist`
   - Place in `ios/Runner/`

4. **Enable Cloud Messaging**
   - Go to Project Settings → Cloud Messaging
   - Note your Server Key (already configured in backend)

---

## Flutter Setup

### Step 1: Add Dependencies

Add to `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter

  # Firebase Core (required)
  firebase_core: ^2.24.0

  # Firebase Messaging for push notifications
  firebase_messaging: ^14.7.0

  # Local notifications (for foreground notifications)
  flutter_local_notifications: ^16.3.0

  # Optional: For notification badges
  flutter_app_badger: ^1.5.0

  # HTTP client (for API calls)
  http: ^1.1.0
  dio: ^5.4.0  # or use http package
```

Run:
```bash
flutter pub get
```

### Step 2: Android Configuration

**File**: `android/app/build.gradle`

```gradle
android {
    defaultConfig {
        // ... other configs
        minSdkVersion 21  // FCM requires min 21
    }
}

dependencies {
    // ... other dependencies
    implementation platform('com.google.firebase:firebase-bom:32.7.0')
    implementation 'com.google.firebase:firebase-messaging'
}
```

**File**: `android/build.gradle`

```gradle
buildscript {
    dependencies {
        // ... other dependencies
        classpath 'com.google.gms:google-services:4.4.0'
    }
}
```

**File**: `android/app/build.gradle` (at the bottom)

```gradle
apply plugin: 'com.google.gms.google-services'
```

**File**: `android/app/src/main/AndroidManifest.xml`

```xml
<manifest>
    <application>
        <!-- ... other configs -->

        <!-- FCM Channel -->
        <meta-data
            android:name="com.google.firebase.messaging.default_notification_channel_id"
            android:value="marketplace_notifications" />

        <!-- FCM Service -->
        <service
            android:name="com.google.firebase.messaging.FirebaseMessagingService"
            android:exported="false">
            <intent-filter>
                <action android:name="com.google.firebase.MESSAGING_EVENT" />
            </intent-filter>
        </service>
    </application>

    <!-- Permissions -->
    <uses-permission android:name="android.permission.INTERNET"/>
    <uses-permission android:name="android.permission.VIBRATE" />
    <uses-permission android:name="android.permission.RECEIVE_BOOT_COMPLETED"/>
    <uses-permission android:name="android.permission.POST_NOTIFICATIONS"/> <!-- Android 13+ -->
</manifest>
```

### Step 3: iOS Configuration

**File**: `ios/Runner/AppDelegate.swift`

```swift
import UIKit
import Flutter
import FirebaseCore
import FirebaseMessaging

@UIApplicationMain
@objc class AppDelegate: FlutterAppDelegate {
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    FirebaseApp.configure()

    if #available(iOS 10.0, *) {
      UNUserNotificationCenter.current().delegate = self as UNUserNotificationCenterDelegate
    }

    GeneratedPluginRegistrant.register(with: self)
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  override func application(_ application: UIApplication,
                           didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data) {
    Messaging.messaging().apnsToken = deviceToken
  }
}
```

**File**: `ios/Podfile`

```ruby
platform :ios, '12.0'

target 'Runner' do
  use_frameworks!
  use_modular_headers!

  flutter_install_all_ios_pods File.dirname(File.realpath(__FILE__))
end
```

Run:
```bash
cd ios
pod install
```

**Enable Push Notifications in Xcode:**
1. Open `ios/Runner.xcworkspace` in Xcode
2. Select Runner target
3. Go to "Signing & Capabilities"
4. Click "+ Capability"
5. Add "Push Notifications"
6. Add "Background Modes" → Check "Remote notifications"

---

## API Endpoints

### Base URLs

- **Local**: `http://127.0.0.1:8030/api`
- **Production**: `https://www.marketplace.ng/api`

### Authentication

All notification endpoints require Bearer token authentication:

```
Authorization: Bearer {your_auth_token}
```

Get token from login:
```
POST /api/login
```

---

### 1. Register Device Token

Register a device to receive push notifications.

**Endpoint**: `POST /api/device-tokens`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body**:
```json
{
  "token": "fcm_device_token_here",
  "platform": "android"
}
```

**Parameters**:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `token` | string | Yes | FCM device token from Firebase |
| `platform` | string | Yes | `android` or `ios` |

**Response (201)**:
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

**Error Response (401)**:
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

**Error Response (422)**:
```json
{
  "success": false,
  "message": "The token field is required.",
  "errors": {
    "token": ["The token field is required."]
  }
}
```

---

### 2. Get User's Device Tokens

Retrieve all device tokens registered for the current user.

**Endpoint**: `GET /api/device-tokens`

**Headers**:
```
Authorization: Bearer {token}
Accept: application/json
```

**Response (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "platform": "android",
      "is_active": true,
      "last_used_at": "2026-01-13T10:30:00.000000Z",
      "created_at": "2026-01-10T08:00:00.000000Z"
    },
    {
      "id": 2,
      "platform": "ios",
      "is_active": true,
      "last_used_at": "2026-01-12T15:20:00.000000Z",
      "created_at": "2026-01-11T09:00:00.000000Z"
    }
  ]
}
```

---

### 3. Delete Device Token

Remove a device token (e.g., on logout).

**Endpoint**: `DELETE /api/device-tokens/{id}`

**Headers**:
```
Authorization: Bearer {token}
Accept: application/json
```

**Response (200)**:
```json
{
  "success": true,
  "message": "Device token removed successfully"
}
```

**Error Response (404)**:
```json
{
  "success": false,
  "message": "Device token not found"
}
```

---

### 4. Get Notification Settings

Get user's notification preferences.

**Endpoint**: `GET /api/notification-settings`

**Headers**:
```
Authorization: Bearer {token}
Accept: application/json
```

**Response (200)**:
```json
{
  "success": true,
  "data": {
    "push_notifications": true,
    "email_notifications": true,
    "message_notifications": true,
    "order_notifications": true,
    "advert_notifications": true,
    "marketing_notifications": false
  }
}
```

---

### 5. Update Notification Settings

Update user's notification preferences.

**Endpoint**: `PUT /api/notification-settings`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body**:
```json
{
  "push_notifications": true,
  "email_notifications": false,
  "message_notifications": true,
  "order_notifications": true,
  "advert_notifications": false,
  "marketing_notifications": false
}
```

**Response (200)**:
```json
{
  "success": true,
  "message": "Notification settings updated successfully",
  "data": {
    "push_notifications": true,
    "email_notifications": false,
    "message_notifications": true,
    "order_notifications": true,
    "advert_notifications": false,
    "marketing_notifications": false
  }
}
```

---

### 6. Get Unread Messages Count

Get count of unread messages (useful for badge count).

**Endpoint**: `GET /api/unread-messages-count`

**Headers**:
```
Authorization: Bearer {token}
Accept: application/json
```

**Response (200)**:
```json
{
  "count": 5
}
```

---

## Implementation Guide

### Project Structure

```
lib/
├── main.dart
├── services/
│   ├── notification_service.dart
│   ├── api_service.dart
│   └── auth_service.dart
├── models/
│   └── notification_model.dart
└── screens/
    └── notifications_settings_screen.dart
```

---

## Code Examples

### 1. Notification Service

**File**: `lib/services/notification_service.dart`

```dart
import 'dart:async';
import 'dart:io';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_app_badger/flutter_app_badger.dart';
import 'api_service.dart';

// Top-level function for background message handling
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('Handling background message: ${message.messageId}');
  // Handle the message
  await NotificationService.instance.handleBackgroundMessage(message);
}

class NotificationService {
  static final NotificationService instance = NotificationService._internal();
  factory NotificationService() => instance;
  NotificationService._internal();

  final FirebaseMessaging _fcm = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  String? _fcmToken;
  final StreamController<RemoteMessage> _messageStreamController =
      StreamController<RemoteMessage>.broadcast();

  Stream<RemoteMessage> get onMessage => _messageStreamController.stream;

  /// Initialize notification service
  Future<void> initialize() async {
    // Initialize Firebase
    await Firebase.initializeApp();

    // Request permissions (iOS)
    await _requestPermissions();

    // Setup background message handler
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    // Initialize local notifications
    await _initializeLocalNotifications();

    // Get FCM token
    await _getFCMToken();

    // Listen to token refresh
    _fcm.onTokenRefresh.listen((newToken) {
      _fcmToken = newToken;
      _registerTokenWithBackend(newToken);
    });

    // Handle foreground messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('Foreground message received: ${message.messageId}');
      _showLocalNotification(message);
      _messageStreamController.add(message);
    });

    // Handle notification taps (app opened from notification)
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('Notification tapped: ${message.messageId}');
      _handleNotificationTap(message);
    });

    // Check if app was opened from a notification
    RemoteMessage? initialMessage = await _fcm.getInitialMessage();
    if (initialMessage != null) {
      _handleNotificationTap(initialMessage);
    }
  }

  /// Request notification permissions (iOS)
  Future<void> _requestPermissions() async {
    if (Platform.isIOS) {
      NotificationSettings settings = await _fcm.requestPermission(
        alert: true,
        badge: true,
        sound: true,
        provisional: false,
      );
      print('User granted permission: ${settings.authorizationStatus}');
    } else if (Platform.isAndroid) {
      // Android 13+ requires runtime permission
      await _localNotifications
          .resolvePlatformSpecificImplementation<
              AndroidFlutterLocalNotificationsPlugin>()
          ?.requestPermission();
    }
  }

  /// Initialize local notifications for foreground display
  Future<void> _initializeLocalNotifications() async {
    const AndroidInitializationSettings androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    final DarwinInitializationSettings iOSSettings =
        DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
      onDidReceiveLocalNotification: (id, title, body, payload) async {
        // Handle iOS foreground notification
      },
    );

    final InitializationSettings initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iOSSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (NotificationResponse response) {
        // Handle notification tap
        if (response.payload != null) {
          print('Notification tapped with payload: ${response.payload}');
          _handleNotificationPayload(response.payload!);
        }
      },
    );

    // Create Android notification channel
    if (Platform.isAndroid) {
      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        'marketplace_notifications',
        'Marketplace Notifications',
        description: 'Notifications for Marketplace Nigeria',
        importance: Importance.high,
        playSound: true,
      );

      await _localNotifications
          .resolvePlatformSpecificImplementation<
              AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(channel);
    }
  }

  /// Get FCM token
  Future<String?> _getFCMToken() async {
    try {
      _fcmToken = await _fcm.getToken();
      print('FCM Token: $_fcmToken');

      if (_fcmToken != null) {
        await _registerTokenWithBackend(_fcmToken!);
      }

      return _fcmToken;
    } catch (e) {
      print('Error getting FCM token: $e');
      return null;
    }
  }

  /// Register token with backend
  Future<void> _registerTokenWithBackend(String token) async {
    try {
      final platform = Platform.isIOS ? 'ios' : 'android';
      await ApiService.instance.registerDeviceToken(token, platform);
      print('Token registered with backend');
    } catch (e) {
      print('Error registering token with backend: $e');
    }
  }

  /// Show local notification (foreground)
  Future<void> _showLocalNotification(RemoteMessage message) async {
    final notification = message.notification;
    final data = message.data;

    if (notification != null) {
      const AndroidNotificationDetails androidDetails =
          AndroidNotificationDetails(
        'marketplace_notifications',
        'Marketplace Notifications',
        channelDescription: 'Notifications for Marketplace Nigeria',
        importance: Importance.high,
        priority: Priority.high,
        playSound: true,
        icon: '@mipmap/ic_launcher',
      );

      const DarwinNotificationDetails iOSDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      );

      const NotificationDetails notificationDetails = NotificationDetails(
        android: androidDetails,
        iOS: iOSDetails,
      );

      await _localNotifications.show(
        message.hashCode,
        notification.title,
        notification.body,
        notificationDetails,
        payload: data.toString(),
      );

      // Update badge count
      await _updateBadgeCount();
    }
  }

  /// Handle background message
  Future<void> handleBackgroundMessage(RemoteMessage message) async {
    print('Background message: ${message.messageId}');
    // Update badge count
    await _updateBadgeCount();
  }

  /// Handle notification tap
  void _handleNotificationTap(RemoteMessage message) {
    final data = message.data;
    print('Notification tapped with data: $data');

    // Navigate based on notification type
    final type = data['type'] as String?;

    switch (type) {
      case 'new_message':
        // Navigate to conversation
        final conversationId = data['conversation_id'] as String?;
        if (conversationId != null) {
          // Navigator.pushNamed(context, '/conversation', arguments: conversationId);
        }
        break;

      case 'order_update':
        // Navigate to order details
        final orderId = data['order_id'] as String?;
        if (orderId != null) {
          // Navigator.pushNamed(context, '/order', arguments: orderId);
        }
        break;

      case 'advert_update':
        // Navigate to advert details
        final advertId = data['advert_id'] as String?;
        if (advertId != null) {
          // Navigator.pushNamed(context, '/advert', arguments: advertId);
        }
        break;

      default:
        // Navigate to notifications list
        // Navigator.pushNamed(context, '/notifications');
        break;
    }
  }

  /// Handle notification payload
  void _handleNotificationPayload(String payload) {
    print('Handling payload: $payload');
    // Parse payload and navigate
  }

  /// Update app badge count
  Future<void> _updateBadgeCount() async {
    try {
      // Get unread count from backend
      final count = await ApiService.instance.getUnreadMessagesCount();

      // Update badge
      if (count > 0) {
        FlutterAppBadger.updateBadgeCount(count);
      } else {
        FlutterAppBadger.removeBadge();
      }
    } catch (e) {
      print('Error updating badge: $e');
    }
  }

  /// Clear all notifications
  Future<void> clearAllNotifications() async {
    await _localNotifications.cancelAll();
    await FlutterAppBadger.removeBadge();
  }

  /// Get current FCM token
  String? get fcmToken => _fcmToken;

  /// Unregister device (on logout)
  Future<void> unregisterDevice() async {
    try {
      if (_fcmToken != null) {
        // Call backend to remove token
        await ApiService.instance.deleteDeviceToken();
      }
      await _fcm.deleteToken();
      await clearAllNotifications();
      _fcmToken = null;
    } catch (e) {
      print('Error unregistering device: $e');
    }
  }

  void dispose() {
    _messageStreamController.close();
  }
}
```

---

### 2. API Service

**File**: `lib/services/api_service.dart`

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static final ApiService instance = ApiService._internal();
  factory ApiService() => instance;
  ApiService._internal();

  // Base URL - change for production
  static const String baseUrl = 'http://127.0.0.1:8030/api';
  // static const String baseUrl = 'https://www.marketplace.ng/api';

  String? _authToken;

  void setAuthToken(String token) {
    _authToken = token;
  }

  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (_authToken != null) 'Authorization': 'Bearer $_authToken',
      };

  /// Register device token
  Future<Map<String, dynamic>> registerDeviceToken(
    String token,
    String platform,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/device-tokens'),
      headers: _headers,
      body: json.encode({
        'token': token,
        'platform': platform,
      }),
    );

    if (response.statusCode == 201 || response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to register device token: ${response.body}');
    }
  }

  /// Get user's device tokens
  Future<List<dynamic>> getDeviceTokens() async {
    final response = await http.get(
      Uri.parse('$baseUrl/device-tokens'),
      headers: _headers,
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'] as List<dynamic>;
    } else {
      throw Exception('Failed to get device tokens: ${response.body}');
    }
  }

  /// Delete device token
  Future<void> deleteDeviceToken([int? tokenId]) async {
    // If tokenId not provided, get first token and delete it
    if (tokenId == null) {
      final tokens = await getDeviceTokens();
      if (tokens.isNotEmpty) {
        tokenId = tokens.first['id'];
      } else {
        return; // No tokens to delete
      }
    }

    final response = await http.delete(
      Uri.parse('$baseUrl/device-tokens/$tokenId'),
      headers: _headers,
    );

    if (response.statusCode != 200) {
      throw Exception('Failed to delete device token: ${response.body}');
    }
  }

  /// Get notification settings
  Future<Map<String, dynamic>> getNotificationSettings() async {
    final response = await http.get(
      Uri.parse('$baseUrl/notification-settings'),
      headers: _headers,
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'] as Map<String, dynamic>;
    } else {
      throw Exception('Failed to get notification settings: ${response.body}');
    }
  }

  /// Update notification settings
  Future<Map<String, dynamic>> updateNotificationSettings(
    Map<String, bool> settings,
  ) async {
    final response = await http.put(
      Uri.parse('$baseUrl/notification-settings'),
      headers: _headers,
      body: json.encode(settings),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception(
          'Failed to update notification settings: ${response.body}');
    }
  }

  /// Get unread messages count
  Future<int> getUnreadMessagesCount() async {
    final response = await http.get(
      Uri.parse('$baseUrl/unread-messages-count'),
      headers: _headers,
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['count'] as int;
    } else {
      return 0;
    }
  }
}
```

---

### 3. Main App Integration

**File**: `lib/main.dart`

```dart
import 'package:flutter/material.dart';
import 'services/notification_service.dart';
import 'services/api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize notifications
  await NotificationService.instance.initialize();

  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  @override
  void initState() {
    super.initState();

    // Listen to notification messages
    NotificationService.instance.onMessage.listen((message) {
      print('New message received: ${message.notification?.title}');

      // Show in-app notification or update UI
      _handleInAppNotification(message);
    });
  }

  void _handleInAppNotification(dynamic message) {
    // Show snackbar or dialog for in-app notification
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message.notification?.body ?? 'New notification'),
          action: SnackBarAction(
            label: 'View',
            onPressed: () {
              // Navigate to relevant screen
            },
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Marketplace Nigeria',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: const HomeScreen(),
    );
  }
}
```

---

### 4. Login Integration

```dart
// After successful login
Future<void> onLoginSuccess(String authToken) async {
  // Save token
  ApiService.instance.setAuthToken(authToken);

  // Register device for notifications
  final fcmToken = NotificationService.instance.fcmToken;
  if (fcmToken != null) {
    try {
      await ApiService.instance.registerDeviceToken(
        fcmToken,
        Platform.isIOS ? 'ios' : 'android',
      );
      print('Device registered for notifications');
    } catch (e) {
      print('Error registering device: $e');
    }
  }
}
```

---

### 5. Logout Integration

```dart
// On logout
Future<void> onLogout() async {
  try {
    // Unregister device
    await NotificationService.instance.unregisterDevice();

    // Clear auth token
    ApiService.instance.setAuthToken('');

    // Navigate to login
    // Navigator.pushReplacementNamed(context, '/login');
  } catch (e) {
    print('Error during logout: $e');
  }
}
```

---

### 6. Notification Settings Screen

```dart
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class NotificationSettingsScreen extends StatefulWidget {
  const NotificationSettingsScreen({Key? key}) : super(key: key);

  @override
  State<NotificationSettingsScreen> createState() =>
      _NotificationSettingsScreenState();
}

class _NotificationSettingsScreenState
    extends State<NotificationSettingsScreen> {
  bool _isLoading = true;
  Map<String, bool> _settings = {};

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    try {
      final settings = await ApiService.instance.getNotificationSettings();
      setState(() {
        _settings = settings.map((key, value) => MapEntry(key, value as bool));
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading settings: $e')),
      );
    }
  }

  Future<void> _updateSetting(String key, bool value) async {
    setState(() => _settings[key] = value);

    try {
      await ApiService.instance.updateNotificationSettings(_settings);
    } catch (e) {
      // Revert on error
      setState(() => _settings[key] = !value);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error updating settings: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notification Settings'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              children: [
                SwitchListTile(
                  title: const Text('Push Notifications'),
                  subtitle: const Text('Receive push notifications'),
                  value: _settings['push_notifications'] ?? true,
                  onChanged: (value) =>
                      _updateSetting('push_notifications', value),
                ),
                SwitchListTile(
                  title: const Text('Message Notifications'),
                  subtitle: const Text('Get notified of new messages'),
                  value: _settings['message_notifications'] ?? true,
                  onChanged: (value) =>
                      _updateSetting('message_notifications', value),
                ),
                SwitchListTile(
                  title: const Text('Order Notifications'),
                  subtitle: const Text('Updates on your orders'),
                  value: _settings['order_notifications'] ?? true,
                  onChanged: (value) =>
                      _updateSetting('order_notifications', value),
                ),
                SwitchListTile(
                  title: const Text('Advert Notifications'),
                  subtitle: const Text('Updates on your adverts'),
                  value: _settings['advert_notifications'] ?? true,
                  onChanged: (value) =>
                      _updateSetting('advert_notifications', value),
                ),
                SwitchListTile(
                  title: const Text('Email Notifications'),
                  subtitle: const Text('Receive email notifications'),
                  value: _settings['email_notifications'] ?? false,
                  onChanged: (value) =>
                      _updateSetting('email_notifications', value),
                ),
              ],
            ),
    );
  }
}
```

---

## Notification Handling

### Notification Types

The backend sends different types of notifications:

#### 1. New Message Notification

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

**Handle in Flutter**:
```dart
if (data['type'] == 'new_message') {
  final conversationId = data['conversation_id'];
  Navigator.pushNamed(context, '/conversation', arguments: conversationId);
}
```

#### 2. Order Update Notification

```json
{
  "notification": {
    "title": "Order Update",
    "body": "Your order has been shipped!"
  },
  "data": {
    "type": "order_update",
    "order_id": "12345",
    "status": "shipped",
    "click_action": "OPEN_ORDER"
  }
}
```

#### 3. Advert Update Notification

```json
{
  "notification": {
    "title": "Advert Approved",
    "body": "Your advert 'Samsung Galaxy S24' is now live!"
  },
  "data": {
    "type": "advert_update",
    "advert_id": "67890",
    "status": "approved",
    "click_action": "OPEN_ADVERT"
  }
}
```

#### 4. Follow Notification

```json
{
  "notification": {
    "title": "New Follower",
    "body": "Jane Doe started following you"
  },
  "data": {
    "type": "new_follower",
    "follower_id": "11111",
    "follower_name": "Jane Doe",
    "click_action": "OPEN_PROFILE"
  }
}
```

---

## Testing

### 1. Test with Backend API (Local)

```bash
# Start local server
php artisan serve --port=8030

# Test notification endpoint
curl -X POST http://127.0.0.1:8030/api/test/notification \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test from Backend",
    "message": "Testing Flutter integration",
    "device_token": "YOUR_FCM_TOKEN"
  }'
```

### 2. Test with Firebase Console

1. Go to Firebase Console
2. Select your project
3. Go to Cloud Messaging
4. Click "Send test message"
5. Enter your FCM token
6. Add title and body
7. Click "Test"

### 3. Test in Flutter

```dart
// Test button in your app
ElevatedButton(
  onPressed: () async {
    final token = NotificationService.instance.fcmToken;
    print('Your FCM Token: $token');
    // Copy and use for testing
  },
  child: const Text('Get FCM Token'),
)
```

### 4. Test Notification Scenarios

**Scenario 1: App in Foreground**
- Send notification
- Should show as local notification
- Should trigger onMessage stream

**Scenario 2: App in Background**
- Send notification
- Should appear in notification tray
- Tap should open app and trigger navigation

**Scenario 3: App Closed**
- Send notification
- Should appear in notification tray
- Tap should open app with initial message

**Scenario 4: Badge Count**
- Send multiple messages
- Badge should update to show unread count

---

## Troubleshooting

### Issue: FCM Token is null

**Causes**:
- Firebase not initialized
- google-services.json/GoogleService-Info.plist missing
- Network connection issues

**Solution**:
```dart
// Check Firebase initialization
try {
  await Firebase.initializeApp();
  print('Firebase initialized');
} catch (e) {
  print('Firebase initialization error: $e');
}

// Verify token retrieval
final token = await FirebaseMessaging.instance.getToken();
print('FCM Token: $token');
```

---

### Issue: Notifications not received

**Checklist**:
- ✅ FCM token registered with backend?
- ✅ User has notifications enabled?
- ✅ App has notification permissions?
- ✅ FCM Server Key correct in backend?
- ✅ Device has internet connection?

**Debug**:
```dart
// Check token status
final token = await FirebaseMessaging.instance.getToken();
if (token != null) {
  print('Token exists: ${token.substring(0, 20)}...');

  // Check if registered
  final tokens = await ApiService.instance.getDeviceTokens();
  print('Registered tokens: ${tokens.length}');
} else {
  print('No FCM token available');
}
```

---

### Issue: Notifications work on Android but not iOS

**iOS Requirements**:
- ✅ Push Notifications capability enabled in Xcode
- ✅ APNs certificate configured in Firebase
- ✅ Physical device (push doesn't work on simulator)
- ✅ App signed with proper provisioning profile

**Check**:
```swift
// In AppDelegate.swift
Messaging.messaging().apnsToken = deviceToken
print("APNs token: \(deviceToken)")
```

---

### Issue: Background notifications not working

**Android**:
- Check background execution permissions
- Verify notification channel created
- Test with device unlocked first

**iOS**:
- Background Modes capability enabled?
- "Remote notifications" checked?

---

### Issue: Badge count not updating

**Solution**:
```dart
// Manually update badge
import 'package:flutter_app_badger/flutter_app_badger.dart';

Future<void> updateBadge() async {
  final count = await ApiService.instance.getUnreadMessagesCount();
  if (count > 0) {
    FlutterAppBadger.updateBadgeCount(count);
  } else {
    FlutterAppBadger.removeBadge();
  }
}
```

---

## Best Practices

### 1. Token Management
- ✅ Register token after login
- ✅ Unregister on logout
- ✅ Handle token refresh automatically
- ✅ Store token status locally

### 2. User Experience
- ✅ Request permissions at appropriate time
- ✅ Explain why notifications are needed
- ✅ Provide settings to customize notifications
- ✅ Clear notifications when viewed

### 3. Error Handling
- ✅ Handle network errors gracefully
- ✅ Retry failed registrations
- ✅ Log errors for debugging
- ✅ Provide user feedback

### 4. Testing
- ✅ Test on both Android and iOS
- ✅ Test all notification scenarios
- ✅ Test with app in different states
- ✅ Test notification navigation

### 5. Performance
- ✅ Initialize notifications early
- ✅ Handle notifications asynchronously
- ✅ Don't block UI thread
- ✅ Clean up resources properly

---

## API Endpoints Summary

| Endpoint | Method | Auth | Purpose |
|----------|--------|------|---------|
| `/api/device-tokens` | POST | ✅ | Register device token |
| `/api/device-tokens` | GET | ✅ | Get user's tokens |
| `/api/device-tokens/{id}` | DELETE | ✅ | Remove token |
| `/api/notification-settings` | GET | ✅ | Get preferences |
| `/api/notification-settings` | PUT | ✅ | Update preferences |
| `/api/unread-messages-count` | GET | ✅ | Get unread count |
| `/api/test/notification` | POST | ✅ | Test notification (local only) |

---

## Support

### Documentation
- **API Docs**: http://127.0.0.1:8030/docs
- **Backend Guide**: `docs/PUSH_NOTIFICATION_TESTING.md`
- **Postman Collection**: `postman/Marketplace-API-Complete.postman_collection.json`

### Firebase Resources
- [Firebase Console](https://console.firebase.google.com/)
- [FCM Documentation](https://firebase.google.com/docs/cloud-messaging)
- [FlutterFire Documentation](https://firebase.flutter.dev/)

### Flutter Packages
- [firebase_messaging](https://pub.dev/packages/firebase_messaging)
- [flutter_local_notifications](https://pub.dev/packages/flutter_local_notifications)
- [flutter_app_badger](https://pub.dev/packages/flutter_app_badger)

---

**Version**: 1.0
**Last Updated**: 2026-01-13
**Backend API Version**: 3.0

🚀 **Ready to integrate!**

<?php
// app/Console/Commands/TestPushNotification.php

namespace App\Console\Commands;

use App\Services\FirebaseCloudMessagingService;
use App\Models\User;
use App\Models\DeviceToken;
use Illuminate\Console\Command;

class TestPushNotification extends Command
{
    protected $signature = 'push:test {user_id?}';
    protected $description = 'Send a test push notification to a user';

    public function handle(FirebaseCloudMessagingService $fcmService)
    {
        $userId = $this->argument('user_id');

        if (!$userId) {
            $userId = $this->ask('Enter user ID (bigint auto-increment ID, not user_id)');
        }

        // IMPORTANT: Use where() instead of find() because User model uses custom primary key
        $user = User::where('id', $userId)->first();

        if (!$user) {
            $this->error("❌ User not found with ID: {$userId}");
            $this->newLine();

            // Show available users
            $this->info('Available users:');
            $users = User::select('id', 'user_id', 'name', 'email')->take(5)->get();

            $this->table(
                ['ID (bigint)', 'User ID (varchar)', 'Name', 'Email'],
                $users->map(fn($u) => [$u->id, $u->user_id, $u->name, $u->email])
            );

            return 1;
        }

        $this->info("Testing push notification for: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // Check if user has push enabled
        if (!$user->push_notifications_enabled) {
            $this->warn("⚠️  User has push notifications disabled");
            if (!$this->confirm('Continue anyway?')) {
                return 0;
            }
        }

        // Get device tokens
        $tokens = $user->deviceTokens()->where('is_active', true)->get();

        if ($tokens->isEmpty()) {
            $this->error('❌ No active device tokens found for this user');
            $this->newLine();
            $this->info('💡 To add a token, run these commands in Tinker:');
            $this->line('');
            $this->line('php artisan tinker');
            $this->line('');
            $this->line("\$user = \\App\\Models\\User::where('id', {$user->id})->first();");
            $this->line("\\App\\Models\\DeviceToken::create([");
            $this->line("    'user_id' => \$user->id,");
            $this->line("    'token' => 'YOUR_FCM_TOKEN_HERE',");
            $this->line("    'platform' => 'android',");
            $this->line("    'is_active' => true,");
            $this->line("]);");
            $this->newLine();

            return 1;
        }

        $tokenArray = $tokens->pluck('token')->toArray();

        $this->info("📱 Found {$tokens->count()} active device token(s):");
        foreach ($tokens as $token) {
            $this->line("  - {$token->platform}: " . substr($token->token, 0, 30) . '...');
        }
        $this->newLine();

        // Prepare test notification
        $notification = [
            'title' => 'Test Notification 🧪',
            'body' => 'This is a test push notification from Marketplace Ethiopia! If you see this, it works! 🎉',
        ];

        $data = [
            'type' => 'test',
            'test_id' => (string) time(),
            'timestamp' => now()->toIso8601String(),
            'message' => 'Testing push notification system',
        ];

        $this->info('📤 Sending test notification...');

        // Send notification
        $result = $fcmService->sendToTokens($tokenArray, $notification, $data);

        $this->newLine();

        if ($result) {
            $this->info('✅ Notification sent successfully!');
            $this->newLine();
            $this->info('Check the following:');
            $this->line('  1. Your browser/device should show the notification');
            $this->line('  2. Check logs: tail -f storage/logs/laravel.log');
            $this->newLine();
        } else {
            $this->error('❌ Failed to send notification');
            $this->newLine();
            $this->warn('Check the logs for details:');
            $this->line('  tail -20 storage/logs/laravel.log');
            $this->newLine();
        }

        return $result ? 0 : 1;
    }
}

<?php
// app/Console/Commands/CheckPushNotificationHealth.php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use App\Models\User;
use App\Models\Followers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPushNotificationHealth extends Command
{
    protected $signature = 'push:health';
    protected $description = 'Check push notification system health';

    public function handle()
    {
        $this->info('🏥 Push Notification System Health Check');
        $this->newLine();

        // Check Firebase configuration
        $this->checkFirebaseConfig();
        $this->newLine();

        // Check database
        $this->checkDatabase();
        $this->newLine();

        // Check users
        $this->checkUsers();
        $this->newLine();

        // Check followers
        $this->checkFollowers();
        $this->newLine();

        $this->info('✅ Health check complete!');

        return 0;
    }

    protected function checkFirebaseConfig()
    {
        $this->line('📋 Firebase Configuration:');

        $credentialsPath = config('services.fcm.credentials');
        $projectId = config('services.fcm.project_id');

        if ($credentialsPath && file_exists($credentialsPath)) {
            $this->line("  ✓ Credentials file exists: {$credentialsPath}");
        } else {
            $this->error("  ✗ Credentials file not found: {$credentialsPath}");
        }

        if ($projectId) {
            $this->line("  ✓ Project ID configured: {$projectId}");
        } else {
            $this->error("  ✗ Project ID not configured");
        }
    }

    protected function checkDatabase()
    {
        $this->line('💾 Database Status:');

        try {
            // Check device_tokens table
            $totalTokens = DeviceToken::count();
            $activeTokens = DeviceToken::where('is_active', true)->count();
            $inactiveTokens = DeviceToken::where('is_active', false)->count();

            $this->line("  Total Device Tokens: {$totalTokens}");
            $this->line("  Active Tokens: {$activeTokens}");

            if ($inactiveTokens > 0) {
                $this->warn("  Inactive Tokens: {$inactiveTokens} (consider cleanup)");
            }

            // Platform breakdown
            $android = DeviceToken::where('is_active', true)->where('platform', 'android')->count();
            $ios = DeviceToken::where('is_active', true)->where('platform', 'ios')->count();

            $this->line("  Android Devices: {$android}");
            $this->line("  iOS Devices: {$ios}");

        } catch (\Exception $e) {
            $this->error("  ✗ Database error: " . $e->getMessage());
        }
    }

    protected function checkUsers()
{
    $this->line('👥 Users Status:');

    try {
        $totalUsers = User::count();
        $usersWithPush = User::where('push_notifications_enabled', true)->count();

        // IMPORTANT: Use whereIn with id column, not primary key
        $userIds = User::select('id')->pluck('id');
        $usersWithTokens = DeviceToken::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->distinct('user_id')
            ->count('user_id');

        $this->line("  Total Users: {$totalUsers}");
        $this->line("  Users with Push Enabled: {$usersWithPush}");
        $this->line("  Users with Active Tokens: {$usersWithTokens}");

        if ($usersWithTokens > 0 && $totalUsers > 0) {
            $percentage = round(($usersWithTokens / $totalUsers) * 100, 2);
            $this->line("  Token Coverage: {$percentage}%");
        }

    } catch (\Exception $e) {
        $this->error("  ✗ Error: " . $e->getMessage());
    }
}

    protected function checkFollowers()
    {
        $this->line('👤 Followers Status:');

        try {
            $totalFollowers = Followers::count();
            $this->line("  Total Follower Relationships: {$totalFollowers}");

            if ($totalFollowers > 0) {
                // Followers with push enabled
                $followerUserIds = Followers::distinct('id')->pluck('id');
                $followersWithPush = User::whereIn('id', $followerUserIds)
                    ->where('push_notifications_enabled', true)
                    ->count();

                // Followers with device tokens
                $followersWithTokens = DeviceToken::whereIn('user_id', $followerUserIds)
                    ->where('is_active', true)
                    ->distinct('user_id')
                    ->count('user_id');

                $this->line("  Followers with Push Enabled: {$followersWithPush}");
                $this->line("  Followers with Active Tokens: {$followersWithTokens}");

                if ($totalFollowers > 0) {
                    $readyPercentage = round(($followersWithTokens / $totalFollowers) * 100, 2);
                    $this->line("  Ready for Notifications: {$readyPercentage}%");
                }
            }

        } catch (\Exception $e) {
            $this->error("  ✗ Error: " . $e->getMessage());
        }
    }
}

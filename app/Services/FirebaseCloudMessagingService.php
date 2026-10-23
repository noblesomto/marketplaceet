<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class FirebaseCloudMessagingService
{
    protected Client $client;
    protected ?string $credentialsPath;
    protected ?string $projectId;

    public function __construct()
    {
        $this->client = new Client();
        $this->credentialsPath = config('services.fcm.credentials');
        $this->projectId = config('services.fcm.project_id');
    }

    /**
     * Get OAuth 2.0 access token
     */
    protected function getAccessToken(): ?string
    {
        try {
            if (!file_exists($this->credentialsPath)) {
                Log::error('FCM: Credentials file not found', [
                    'path' => $this->credentialsPath
                ]);
                return null;
            }

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                json_decode(file_get_contents($this->credentialsPath), true)
            );

            $token = $credentials->fetchAuthToken();

            return $token['access_token'] ?? null;

        } catch (\Exception $e) {
            Log::error('FCM: Failed to get access token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send notification to multiple device tokens
     */
    public function sendToTokens(array $tokens, array $notification, array $data = []): bool
    {
        if (empty($tokens)) {
            Log::warning('FCM: No tokens provided');
            return false;
        }

        if (empty($this->projectId)) {
            Log::error('FCM: Project ID not configured');
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('FCM: Could not get access token');
            return false;
        }

        $successCount = 0;
        $failureCount = 0;

        // FCM v1 API requires individual messages per token
        foreach ($tokens as $token) {
            try {
                $result = $this->sendToSingleToken($token, $notification, $data, $accessToken);

                if ($result) {
                    $successCount++;
                } else {
                    $failureCount++;
                }

            } catch (\Exception $e) {
                Log::error('FCM: Error sending to token', [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $e->getMessage(),
                ]);
                $failureCount++;
            }
        }

        Log::info('FCM Batch Complete', [
            'total' => count($tokens),
            'success' => $successCount,
            'failure' => $failureCount,
        ]);

        return $successCount > 0;
    }

    /**
     * Send notification to a single token (HTTP v1 API)
     */
    protected function sendToSingleToken(
        string $token,
        array $notification,
        array $data,
        string $accessToken
    ): bool {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        // Build the message payload for FCM v1
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $notification['title'] ?? '',
                'body' => $notification['body'] ?? '',
            ],
            'data' => $this->convertDataToStrings($data),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $notification['title'] ?? '',
                            'body'  => $notification['body'] ?? '',
                        ],
                        'sound' => 'default',
                        'badge' => $notification['badge'] ?? 0,
                    ],
                ],
            ],
        ];

        // Add image if provided
        if (!empty($notification['image'])) {
            $message['notification']['image'] = $notification['image'];
        }

        $payload = [
            'message' => $message,
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                Log::debug('FCM: Message sent successfully', [
                    'token' => substr($token, 0, 20) . '...',
                ]);
                return true;
            }

            return false;

        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();

            // Handle specific errors
            if ($statusCode === 404) {
                Log::warning('FCM: Token not found (404) - marking as inactive', [
                    'token' => substr($token, 0, 20) . '...',
                ]);
                $this->markTokenAsInactive($token);
            } elseif ($statusCode === 400) {
                Log::warning('FCM: Invalid token (400) - marking as inactive', [
                    'token' => substr($token, 0, 20) . '...',
                ]);
                $this->markTokenAsInactive($token);
            } else {
                Log::error('FCM Guzzle Error', [
                    'message' => $e->getMessage(),
                    'code' => $statusCode,
                    'token' => substr($token, 0, 20) . '...',
                ]);
            }

            return false;
        }
    }

    /**
     * Convert all data values to strings (FCM v1 requirement)
     */
    protected function convertDataToStrings(array $data): array
    {
        $stringData = [];

        foreach ($data as $key => $value) {
            $stringData[$key] = is_string($value) ? $value : (string) $value;
        }

        return $stringData;
    }

    /**
     * Mark token as inactive in database
     */
    protected function markTokenAsInactive(string $token): void
    {
        try {
            \App\Models\DeviceToken::where('token', $token)
                ->update(['is_active' => false]);
        } catch (\Exception $e) {
            Log::error('Failed to mark token as inactive', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification to a single token (public method)
     */
    public function sendToToken(string $token, array $notification, array $data = []): bool
    {
        return $this->sendToTokens([$token], $notification, $data);
    }
}

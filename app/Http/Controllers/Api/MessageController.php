<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageImage;
use App\Models\User;
use App\Models\Advert;
use App\Models\Payment;
use App\Models\ArchivedMessage;
use App\Models\BlockedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;
use App\Events\NewMessageNotification;
use Carbon\Carbon;
use App\Jobs\SendPushNotification;
use App\Helpers\ContentHelper;
use App\Support\ActivityLog;

/**
 * @group Messages
 *
 * APIs for managing messages and conversations between users
 */
class MessageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="Send a new message with optional images",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"advert_id", "receiver_id"},
     *                 @OA\Property(property="advert_id", type="integer"),
     *                 @OA\Property(property="receiver_id", type="string"),
     *                 @OA\Property(property="message_content", type="string", maxLength=1000),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Message sent successfully"),
     *     @OA\Response(response=462, description="Blocked by receiver")
     * )
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advert_id' => 'required|integer|exists:adverts,id',
            'receiver_id' => 'required|exists:users,user_id',
            'message_content' => 'nullable|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:12048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($reason = ContentHelper::detectBannedContact($request->input('message_content'))) {
            return response()->json([
                'success' => false,
                'errors'  => ['message_content' => ["Your message appears to contain {$reason}. For your safety, keep contact details out of chat — buyers and sellers should coordinate delivery and payment through the app."]],
            ], 422);
        }

        $user = auth()->user();

        // Check if sender is blocked
        $isBlocked = BlockedUser::where('blocker_id', $request->receiver_id)
            ->where('blocked_id', $user->user_id)
            ->where(function($query) use ($request) {
                $query->where('advert_id', $request->advert_id)
                      ->orWhereNull('advert_id');
            })
            ->exists();

        if ($isBlocked) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot send messages to this user'
            ], 462);
        }

        // Create message
        $message = Message::create([
            'advert_id' => $request->advert_id,
            'sender_id' => $user->user_id,
            'receiver_id' => $request->receiver_id,
            'message_content' => strip_tags($request->message_content),
            'is_read' => false
        ]);

        // Handle image uploads
        // Normalise to an array: mobile apps may send a single file as 'images'
        // (not 'images[]'), in which case Laravel returns an UploadedFile, not an array.
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $imageFiles = is_array($files) ? $files : [$files];

            foreach ($imageFiles as $imageFile) {
                $messageImage = MessageImage::create([
                    'message_id' => $message->id,
                ]);

                $messageImage->addMedia($imageFile)
                    ->toMediaCollection('message_images');
            }
        }

        // Eager-load images with their media so appended URL attributes
        // (optimized_image_url / large_image_url) resolve correctly.
        $message->load('images.media');

        // Broadcast events
        event(new NewMessageNotification($message));
        try {
            $receiver = User::where('user_id', $request->receiver_id)->first();
            if ($receiver) {
                SendPushNotification::dispatch($receiver, $message, auth()->user());
            }
        } catch (\Exception $e) {
            \Log::error('Failed to dispatch push notification: ' . $e->getMessage());
        }

        // Intentionally not logging the message body — this is an audit trail,
        // not a second copy of private chats.
        ActivityLog::record('message', 'Sent a message', $user, null, [
            'advert_id' => $request->advert_id,
            'receiver_id' => $request->receiver_id,
            'source' => 'app',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }


    /**
     * @OA\Get(
     *     path="/api/messages/conversation/{advertId}/{receiverId}",
     *     summary="Get conversation between users for an advert",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="advertId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="receiverId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Conversation messages")
     * )
     */
    public function getConversation($advertId, $receiverId)
    {
        $user = auth()->user();

        $advert = Advert::with('owner')->find($advertId);
        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $receiver = User::where('user_id', $receiverId)->first();
        if (!$receiver) {
            return response()->json([
                'success' => false,
                'message' => 'Receiver not found'
            ], 404);
        }

        $messages = Message::with(['images.media', 'sender', 'receiver'])
            ->where(function ($query) use ($user, $receiver, $advertId) {
                $query->where('sender_id', $user->user_id)
                      ->where('receiver_id', $receiver->user_id)
                      ->where('advert_id', $advertId);
            })
            ->orWhere(function ($query) use ($user, $receiver, $advertId) {
                $query->where('sender_id', $receiver->user_id)
                      ->where('receiver_id', $user->user_id)
                      ->where('advert_id', $advertId);
            })
            ->orderBy('created_at')
            ->get();

        // Mark unread messages as read
        Message::where('receiver_id', $user->user_id)
            ->where('sender_id', $receiver->user_id)
            ->where('advert_id', $advertId)
            ->update(['is_read' => true]);

        // Check if blocked
        $isBlocked = $user->hasBlocked($receiver->user_id, $advertId);

        // Check if archived
        $isArchived = ArchivedMessage::where('user_id', $user->user_id)
            ->where('advert_id', $advertId)
            ->where('other_user_id', $receiver->user_id)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages,
                'advert' => $advert,
                'receiver' => $receiver,
                'is_blocked' => $isBlocked,
                'is_archived' => $isArchived
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/advert/{advertId}",
     *     summary="Get all messages for an advert",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="advertId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Messages for the advert")
     * )
     */
    public function getAdvertMessages($advertId)
    {
        $user = auth()->user();

        $messages = Message::with(['sender', 'receiver', 'images.media'])
            ->where('advert_id', $advertId)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->user_id)
                      ->orWhere('receiver_id', $user->user_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/conversations",
     *     summary="Get all user conversations",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="include_archived", in="query", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="List of conversations")
     * )
     */
    public function getUserConversations(Request $request)
    {
        $user = auth()->user();
        $userId = $user->user_id;
        $includeArchived = $request->boolean('include_archived', false);

        $query = Message::select('advert_id', 'sender_id', 'receiver_id')
            ->selectRaw('MAX(created_at) as last_message_date')
            ->selectRaw('MAX(id) as last_message_id')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->groupBy('advert_id', 'sender_id', 'receiver_id')
            ->orderBy('last_message_date', 'desc');

        // Exclude archived conversations if not requested
        if (!$includeArchived) {
            $archivedIds = ArchivedMessage::where('user_id', $userId)
                ->pluck('advert_id')
                ->toArray();

            if (!empty($archivedIds)) {
                $query->whereNotIn('advert_id', $archivedIds);
            }
        }

        // groupBy(advert_id, sender_id, receiver_id) produces two rows per conversation
        // (one per direction). De-duplicate: keep the first row per (advert_id, other_user)
        // pair — the query is ordered by last_message_date DESC so the first occurrence
        // already holds the most recent message for that conversation.
        $seen = [];
        $rawConversations = $query->get()->filter(function ($conv) use ($userId, &$seen) {
            $otherUserId = $conv->sender_id === $userId ? $conv->receiver_id : $conv->sender_id;
            $key = $conv->advert_id . '_' . $otherUserId;
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        });

        // Load relationships
        $conversations = $rawConversations->map(function ($conversation) use ($user) {
            $otherUserId = $conversation->sender_id === $user->user_id
                ? $conversation->receiver_id
                : $conversation->sender_id;

            $lastMessage = Message::with('images')
                ->find($conversation->last_message_id);

            $unreadCount = Message::where('advert_id', $conversation->advert_id)
                ->where('receiver_id', $user->user_id)
                ->where('sender_id', $otherUserId)
                ->where('is_read', false)
                ->count();

            $isArchived = ArchivedMessage::where('user_id', $user->user_id)
                ->where('advert_id', $conversation->advert_id)
                ->where('other_user_id', $otherUserId)
                ->exists();

            $advert = Advert::with('firstImage')->find($conversation->advert_id);

            return [
                'advert_id' => $conversation->advert_id,
                'advert' => $advert,
                'advert_thumbnail_url' => $advert ? $advert->getFirstImageUrl('thumbnail') : null,
                'other_user' => User::where('user_id', $otherUserId)->first(),
                'last_message' => $lastMessage,
                'last_message_date' => $conversation->last_message_date,
                'unread_count' => $unreadCount,
                'is_archived' => $isArchived
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $conversations->values(),
            'total' => $conversations->count(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/unread/count",
     *     summary="Get count of unread messages",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Unread message count")
     * )
     */
    public function getUnreadCount()
    {
        $user = auth()->user();

        $archivedIds = ArchivedMessage::where('user_id', $user->user_id)
            ->pluck('advert_id')
            ->toArray();

        $unreadCount = Message::where('receiver_id', $user->user_id)
            ->where('is_read', false)
            ->when(!empty($archivedIds), fn($q) => $q->whereNotIn('advert_id', $archivedIds))
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/messages/{messageId}/read",
     *     summary="Mark message as read",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="messageId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Message marked as read")
     * )
     */
    public function markAsRead($messageId)
    {
        $user = auth()->user();

        $message = Message::where('id', $messageId)
            ->where('receiver_id', $user->user_id)
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/messages/conversation/{advertId}/{userId}/read",
     *     summary="Mark all messages in conversation as read",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="advertId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Messages marked as read")
     * )
     */
    public function markConversationAsRead($advertId, $userId)
    {
        $user = auth()->user();

        Message::where('advert_id', $advertId)
            ->where('sender_id', $userId)
            ->where('receiver_id', $user->user_id)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation marked as read'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/archive",
     *     summary="Archive a conversation",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"advert_id", "other_user_id"},
     *             @OA\Property(property="advert_id", type="integer"),
     *             @OA\Property(property="other_user_id", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Conversation archived")
     * )
     */
    public function archive(Request $request)
    {
        //dd($request);
        $validator = Validator::make($request->all(), [
            'advert_id' => 'required|exists:adverts,id',
            'other_user_id' => 'required|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        // Prevent self-archiving
        if ($user->user_id == $request->other_user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid operation.'
            ], 400);
        }

        $archived = ArchivedMessage::updateOrCreate(
            [
                'user_id' => $user->user_id,
                'advert_id' => $request->advert_id,
                'other_user_id' => $request->other_user_id,
            ],
            [
                'archived_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived successfully',
            'data' => $archived
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/unarchive",
     *     summary="Unarchive a conversation",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"advert_id", "other_user_id"},
     *             @OA\Property(property="advert_id", type="integer"),
     *             @OA\Property(property="other_user_id", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Conversation unarchived")
     * )
     */
    public function unarchive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advert_id' => 'required|exists:adverts,id',
            'other_user_id' => 'required|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        $deleted = ArchivedMessage::where('user_id', $user->user_id)
            ->where('advert_id', $request->advert_id)
            ->where('other_user_id', $request->other_user_id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Conversation unarchived successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Archive not found'
        ], 404);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/archived",
     *     summary="Get archived conversations",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Archived conversations")
     * )
     */
    public function getArchivedConversations(Request $request)
    {
        $user = auth()->user();

        $archived = ArchivedMessage::where('user_id', $user->user_id)
            ->with(['advert.firstImage'])
            ->orderBy('archived_at', 'desc')
            ->paginate($request->get('per_page', 20));

        $archived->getCollection()->transform(function ($item) {
            $item->other_user = User::where('user_id', $item->other_user_id)->first();
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $archived->items(),
            'pagination' => [
                'current_page' => $archived->currentPage(),
                'last_page' => $archived->lastPage(),
                'per_page' => $archived->perPage(),
                'total' => $archived->total()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/{paymentId}/mark-delivered",
     *     summary="Mark payment as delivered",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="paymentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Payment marked as delivered")
     * )
     */
    public function markAsDelivered($paymentId)
    {
        $payment = Payment::find($paymentId);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        // Releases escrowed funds — only the buyer can do this (same as confirm-delivery).
        [$ok, $message] = \App\Services\BuyDirectEscrow::make()->confirmReceipt($payment, auth()->user()->user_id);

        return response()->json([
            'success' => $ok,
            'message' => $message,
            'data' => $ok ? $payment->fresh() : null
        ], $ok ? 200 : 422);
    }
}

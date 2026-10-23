<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivedMessage extends Model
{
    use HasFactory;

    protected $table = 'archived_messages';

    protected $fillable = [
        'user_id',
        'advert_id',
        'other_user_id',
        'archived_at'
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who archived the conversation
     * Uses user_id (custom string identifier) to match Message model pattern
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the other user in the conversation
     * Uses user_id (custom string identifier) to match Message model pattern
     */
    public function otherUser()
    {
        return $this->belongsTo(User::class, 'other_user_id', 'user_id');
    }

    /**
     * Get the advert associated with the archived conversation
     */
    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    /**
     * Scope to get archives for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get archives for a specific advert
     */
    public function scopeForAdvert($query, $advertId)
    {
        return $query->where('advert_id', $advertId);
    }

    /**
     * Check if a conversation is archived
     */
    public static function isArchived($userId, $advertId, $otherUserId)
    {
        return self::where('user_id', $userId)
            ->where('advert_id', $advertId)
            ->where('other_user_id', $otherUserId)
            ->exists();
    }

    /**
     * Archive a conversation
     */
    public static function archiveConversation($userId, $advertId, $otherUserId)
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'advert_id' => $advertId,
                'other_user_id' => $otherUserId,
            ],
            [
                'archived_at' => now(),
            ]
        );
    }

    /**
     * Unarchive a conversation
     */
    public static function unarchiveConversation($userId, $advertId, $otherUserId)
    {
        return self::where('user_id', $userId)
            ->where('advert_id', $advertId)
            ->where('other_user_id', $otherUserId)
            ->delete();
    }
}

<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewMessageNotification implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->message->receiver_id);
    }

    public function broadcastAs()
    {
        return 'new.message';
    }

    // Optional: format message payload
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->message_content,
            'sender_id' => $this->message->sender_id,
            'advert_id' => $this->message->advert_id,
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
}


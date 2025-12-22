<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast to a private channel for the user
        return [
            new PrivateChannel('user.' . $this->notification->user_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id' => $this->notification->notification_id ?? $this->notification->id,
                'user_id' => $this->notification->user_id,
                'order_id' => $this->notification->order_id,
                'message' => $this->notification->message,
                'notif_type' => $this->notification->notif_type,
                'is_read' => $this->notification->is_read,
                'created_at' => $this->notification->created_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * Get the name of the event (for client-side listening).
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}

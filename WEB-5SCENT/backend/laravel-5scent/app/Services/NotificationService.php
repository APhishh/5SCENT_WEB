<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Helpers\OrderCodeHelper;
use App\Events\NotificationCreated;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a ProfileReminder notification for incomplete profile
     * This will only create ONCE per user - never recreate even if read
     */
    public static function createProfileReminderNotification($userId)
    {
        // Check if user already has a ProfileReminder notification (read or unread)
        $existingNotification = Notification::where('user_id', $userId)
            ->where('notif_type', 'ProfileReminder')
            ->first();

        if ($existingNotification) {
            return $existingNotification; // Don't create duplicate - reuse existing one
        }

        return Notification::create([
            'user_id' => $userId,
            'order_id' => null,
            'message' => 'Complete your profile to enjoy a faster checkout experience. Add your shipping address and phone number.',
            'notif_type' => 'ProfileReminder',
            'is_read' => false,
        ]);
    }

    /**
     * Create a Delivery notification when order is marked as delivered
     * Allows multiple notifications - each delivery event creates a new notification
     */
    public static function createDeliveryNotification($orderId, $message = null)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return null;
        }

        $orderCode = OrderCodeHelper::formatOrderCode($order);
        $notification = Notification::create([
            'user_id' => $order->user_id,
            'order_id' => $orderId,
            'message' => $message ?? "Your order {$orderCode} has been delivered. We'd love to hear your thoughts.",
            'notif_type' => 'Delivery',
            'is_read' => false,
        ]);

        // Broadcast the notification
        static::broadcastNotification($notification);

        return $notification;
    }

    /**
     * Create a Payment notification with de-duplication
     * Prevents duplicate notifications for the same message/order within 5 minutes
     */
    public static function createPaymentNotification($userId, $orderId, $message, $notifType = 'Payment')
    {
        $message = $message ?? 'Your payment has been processed.';

        // Check for duplicate notifications within 5 minutes
        $existingNotification = Notification::where('order_id', $orderId)
            ->where('notif_type', $notifType)
            ->where('message', $message)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();

        if ($existingNotification) {
            Log::debug('Duplicate payment notification prevented', [
                'order_id' => $orderId,
                'message' => $message,
            ]);
            return $existingNotification;
        }

        $notification = Notification::create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'message' => $message,
            'notif_type' => $notifType,
            'is_read' => false,
        ]);

        // Broadcast the notification
        static::broadcastNotification($notification);

        return $notification;
    }

    /**
     * Create an OrderUpdate notification with de-duplication
     * Prevents duplicate notifications for the same message/order within 5 minutes
     */
    public static function createOrderUpdateNotification($orderId, $message = null)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return null;
        }

        $message = $message ?? 'Your order status has been updated.';

        // Check for duplicate notifications within 5 minutes
        $existingNotification = Notification::where('order_id', $orderId)
            ->where('notif_type', 'OrderUpdate')
            ->where('message', $message)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();

        if ($existingNotification) {
            Log::debug('Duplicate order update notification prevented', [
                'order_id' => $orderId,
                'message' => $message,
            ]);
            return $existingNotification;
        }

        $notification = Notification::create([
            'user_id' => $order->user_id,
            'order_id' => $orderId,
            'message' => $message,
            'notif_type' => 'OrderUpdate',
            'is_read' => false,
        ]);

        // Broadcast the notification
        static::broadcastNotification($notification);

        return $notification;
    }

    /**
     * Broadcast notification to connected clients (via Laravel Broadcasting)
     */
    private static function broadcastNotification(Notification $notification)
    {
        try {
            broadcast(new NotificationCreated($notification));
        } catch (\Exception $e) {
            Log::debug('Broadcasting notification (queue may not be configured)', [
                'notification_id' => $notification->notification_id ?? $notification->id,
            ]);
        }
    }

    /**
     * Mark a notification as read
     */
    public static function markAsRead($notificationId)
    {
        return Notification::find($notificationId)?->update(['is_read' => true]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->update(['is_read' => true]);
    }

    /**
     * Get unread notification count for user
     */
    public static function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->count();
    }
}

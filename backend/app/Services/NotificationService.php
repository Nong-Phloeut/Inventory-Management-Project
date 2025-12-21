<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a new notification for a user.
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param array $options Optional keys: icon, color, type, action_url, channel
     * @return Notification
     */
    public function create(
        User $user,
        string $title,
        string $message,
        array $options = []
    ): Notification {
        return Notification::create([
            'user_id'    => $user->id,
            'title'      => $title,
            'message'    => $message,
            'icon'       => $options['icon'] ?? 'mdi-bell-outline',
            'color'      => $options['color'] ?? 'primary',
            'type'       => $options['type'] ?? 'info',
            'action_url' => $options['action_url'] ?? null,
            'channel'    => $options['channel'] ?? 'system', // web, telegram, email...
            'is_read'    => false,
            'data'       => $options['data'] ?? null,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user)
    {
        $user->notifications()->where('is_read', false)->update(['is_read' => true]);
    }
}

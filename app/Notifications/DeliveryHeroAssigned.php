<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class DeliveryHeroAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function via($notifiable)
    {
        return ['database', 'fcm'];
    }

    public function toFcm($notifiable)
    {
        return CloudMessage::withTarget('token', $notifiable->fcm_token)
            ->withNotification(FirebaseNotification::create(
                'New Order Received!',
                "Tap to view the order details. "
            ))
            ->withData([
                'order_id' => (string) $this->orderId,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Important for click handling
            ]);
    }
} 
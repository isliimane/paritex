<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class DeliveryHeroOrderUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    protected $orderId;
    protected $notifId;

    public function __construct($orderId,$notifId)
    {
        $this->orderId = $orderId;
        $this->notifId = $notifId;
    }

    public function via($notifiable)
    {
        return ['fcm'];
    }

    public function toFcm($notifiable)
    {
        return CloudMessage::withTarget('token', $notifiable->fcm_token)
            ->withNotification(FirebaseNotification::create(
                'Mise à jour d\'une commande',
                "Appuyez pour voir les détails"
            ))
            ->withData([
                'order_id' => (int) $this->orderId,
                'notification_id' => (int) $this->notifId,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Important for click handling
            ]);
    }
} 
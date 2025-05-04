<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Factory;

class FcmChannel
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'))
            ->createMessaging();
    }

    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable->fcm_token) {
            return;
        }

        $message = $notification->toFcm($notifiable);
        
        try {
            $this->firebase->send($message);
        } catch (\Exception $e) {
            \Log::error('FCM notification failed: ' . $e->getMessage());
        }
    }
} 
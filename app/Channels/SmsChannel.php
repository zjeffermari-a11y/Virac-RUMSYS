<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class SmsChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);

        $response = Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => config('services.semaphore.api_key'),
            'number' => $message['number'],
            'message' => $message['message'],
        ]);

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('Semaphore API request failed', [
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
        }
    }
}

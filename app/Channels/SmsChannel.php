<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            Log::error('Semaphore API request failed', [
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
        } else {
            // Store SMS message in notifications table for tracking
            try {
                $responseData = $response->json();
                if (isset($responseData[0]) && isset($responseData[0]['message_id'])) {
                    $adminUser = \App\Models\User::whereHas('role', function($query) {
                        $query->where('name', 'Admin');
                    })->first();
                    
                    DB::table('notifications')->insert([
                        'recipient_id' => $notifiable->id ?? 1,
                        'sender_id' => $adminUser ? $adminUser->id : null,
                        'channel' => 'sms',
                        'title' => 'SMS Notification',
                        'message' => json_encode([
                            'text' => $message['message'],
                            'type' => 'notification',
                        ]),
                        'status' => 'sent',
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to store SMS notification from SmsChannel: " . $e->getMessage());
            }
        }
    }
}

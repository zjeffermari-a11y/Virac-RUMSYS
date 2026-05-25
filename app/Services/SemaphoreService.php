<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreService
{
    protected $apiKey;
    protected $senderName;

    public function __construct()
    {
        $this->apiKey = env('SEMAPHORE_API_KEY');
        $this->senderName = env('SEMAPHORE_SENDER_NAME', 'SEMAPHORE');
    }

    /**
     * Send SMS via Semaphore
     *
     * @param string $phoneNumber
     * @param string $message
     * @return array
     */
    public function send($phoneNumber, $message)
    {
        try {
            $response = Http::post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => $this->apiKey,
                'number' => $phoneNumber,
                'message' => $message,
                'sendername' => $this->senderName
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                Log::error('Semaphore SMS Error: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'Failed to send SMS via Semaphore'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Semaphore Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check Credit Balance
     *
     * @return int|null
     */
    public function checkBalance()
    {
        try {
            $response = Http::get('https://api.semaphore.co/api/v4/account', [
                'apikey' => $this->apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['credit_balance'] ?? null;
            }

            Log::error('Semaphore Balance Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Semaphore Balance Exception: ' . $e->getMessage());
            return null;
        }
    }
}

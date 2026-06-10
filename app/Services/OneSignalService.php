<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OneSignalService
{
    private string $appId;
    private string $apiKey;

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->apiKey = config('services.onesignal.api_key');
    }

    public function sendToPlayer(string $playerId, string $title, string $message, array $data = [])
    {
        return Http::withHeaders([
            'Authorization' => 'Basic ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $this->appId,

            'include_player_ids' => [$playerId],

            'headings' => [
                'en' => $title
            ],

            'contents' => [
                'en' => $message
            ],

            'data' => $data,
        ]);
    }
}
<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FcmService
{
    public function __construct(private Messaging $messaging) {}

    /**
     * Envoie une notification à un token unique
     */
    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): bool {
        return $this->sendToTokens([$token], $title, $body, $data);
    }

    /**
     * Envoie une notification à plusieurs tokens
     */
    public function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = []
    ): bool {
        $tokens = array_values(array_filter($tokens));
        if (empty($tokens)) return false;

        // FCM V1 n'accepte qu'un token à la fois par message
        // On envoie en batch via sendAll
        $messages = array_map(function (string $token) use ($title, $body, $data) {
            return CloudMessage::withTarget('token', $token)
                ->withNotification(
                    Notification::create($title, $body)
                )
                ->withData(array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]))
                ->withAndroidConfig([
                    'priority' => 'high',
                    'notification' => [
                        'sound'        => 'default',
                        'channel_id'   => 'ccm_tickets',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ]);
        }, $tokens);

        try {
            $report = $this->messaging->sendAll($messages);

            $successCount = $report->successes()->count();
            $failureCount = $report->failures()->count();

            if ($failureCount > 0) {
                foreach ($report->failures()->getItems() as $failure) {
                    Log::warning('FCM failure: ' . $failure->error()?->getMessage());
                }
            }

            Log::info("FCM sent: {$successCount} success, {$failureCount} failures");
            return $successCount > 0;

        } catch (\Throwable $e) {
            Log::error('FCM exception: ' . $e->getMessage());
            return false;
        }
    }
}
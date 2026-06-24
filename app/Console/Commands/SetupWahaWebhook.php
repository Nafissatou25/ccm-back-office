<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetupWahaWebhook extends Command
{
    protected $signature = 'waha:setup-webhook';
    protected $description = 'Configure le webhook Waha pour pointer vers Laravel';

    public function handle()
{
    $wahaUrl = env('WAHA_URL', 'http://localhost:3000');
    $webhookUrl = env('APP_URL') . '/api/whatsapp/webhook';
    $apiKey = env('WAHA_API_KEY');

    $payload = [
        'url'    => $webhookUrl,
        'events' => ['message'],
    ];

    // Si WAHA_API_KEY est définie, on l'utilise, sinon on envoie sans.
    $http = Http::contentType('application/json');
    if (!empty($apiKey)) {
        $http = $http->withToken($apiKey);
    }

    $response = $http->post($wahaUrl . '/api/webhooks', $payload);

    if ($response->successful()) {
        $this->info('✅ Webhook Waha configuré avec succès.');
        $this->line('URL : ' . $webhookUrl);
        return 0;
    }

    $this->error('❌ Échec de la configuration du webhook :');
    $this->error($response->body());
    return 1;
}
}
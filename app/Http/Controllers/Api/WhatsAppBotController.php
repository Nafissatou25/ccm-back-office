<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WhatsAppBotController extends Controller
{
    // ── Vérification webhook (inutile pour Node.js, mais gardée) ──
    public function verify(Request $request)
    {
        return response('OK', 200);
    }

    // ── Réception des messages (webhook) ─────────────────────
    public function handle(Request $request)
    {
        $payload = $request->json()->all() ?: $request->all();

        $from = $payload['from'] ?? $payload['From'] ?? null;
        $text = strtolower(trim($payload['body'] ?? $payload['Body'] ?? ''));

        if (!$from) {
            \Log::warning('Aucun expéditeur trouvé dans le webhook');
            return response()->json(['status' => 'no_from'], 400);
        }

        // Ignorer les messages de groupes (si vous ne voulez que les privés)
        if (str_contains($from, '@g.us')) {
            \Log::info('Message de groupe ignoré', ['from' => $from]);
            return response('', 200);
        }

        // Récupérer l'état depuis le cache (clé = from complet)
        $state = Cache::get("wa_bot_{$from}", ['step' => 'start']);

        // Mot-clé de réinitialisation
        if (in_array($text, ['bonjour', 'salut', 'hello', 'start', 'menu', 'hi'])) {
            $state = ['step' => 'start'];
        }

        $this->processMessage($from, $text, $state);

        return response('', 200);
    }

    // ── Machine à états ──────────────────────────────────────
    private function processMessage(string $from, string $text, array $state): void
    {
        \Log::info('Process message', ['from' => $from, 'text' => $text, 'state' => $state]);

        match ($state['step']) {
            'start'              => $this->stepStart($from),
            'await_name'         => $this->stepAwaitName($from, $text, $state),
            'await_firstname'    => $this->stepAwaitFirstname($from, $text, $state),
            'await_contract'     => $this->stepAwaitContract($from, $text, $state),
            'await_phone'        => $this->stepAwaitPhone($from, $text, $state),
            'await_location'     => $this->stepAwaitLocation($from, $text, $state),
            'await_description'  => $this->stepAwaitDescription($from, $text, $state),
            'confirm'            => $this->stepConfirm($from, $text, $state),
            default              => $this->stepStart($from),
        };
    }

    // ── Étapes ───────────────────────────────────────────────
    private function stepStart(string $from): void
    {
        $this->sendText($from,
            "👋 Bonjour ! Bienvenue sur le service de réclamations de la *SOCADEL — Délégation de l'Extrême-Nord*.\n\n" .
            "Je vais enregistrer votre demande en quelques étapes.\n\n" .
            "Veuillez saisir votre *nom de famille* :"
        );
        Cache::put("wa_bot_{$from}", ['step' => 'await_name'], now()->addHours(2));
    }

    private function stepAwaitName(string $from, string $text, array $state): void
    {
        if (strlen(trim($text)) < 2) {
            $this->sendText($from, "⚠️ Veuillez saisir un nom valide.");
            return;
        }
        $state['client_name'] = trim(ucwords($text));
        $state['step'] = 'await_firstname';
        $this->sendText($from, "Votre *prénom* :");
        Cache::put("wa_bot_{$from}", $state, now()->addHours(2));
    }

    private function stepAwaitFirstname(string $from, string $text, array $state): void
    {
        $state['client_firstname'] = trim(ucwords($text));
        $state['step'] = 'await_contract';
        $this->sendText($from,
            "Votre *numéro de contrat* SOCADEL :\n\n" .
            "_Si vous ne le connaissez pas, tapez *0* pour passer._"
        );
        Cache::put("wa_bot_{$from}", $state, now()->addHours(2));
    }

    private function stepAwaitContract(string $from, string $text, array $state): void
    {
        $state['contract_number'] = ($text === '0') ? null : trim($text);
        $state['step'] = 'await_phone';
        $this->sendText($from, "Votre *numéro de téléphone* de contact :");
        Cache::put("wa_bot_{$from}", $state, now()->addHours(2));
    }

    private function stepAwaitPhone(string $from, string $text, array $state): void
    {
        $phone = in_array($text, ['meme', 'même', 'idem', 'ce numéro'])
            ? $from
            : trim($text);
        $state['contact_phone'] = $phone;
        $state['step'] = 'await_location';
        $this->sendText($from,
            "Votre *point de repère* (quartier, rue, village) :\n\n" .
            "_Ex : Quartier Djarengol, près du marché central_"
        );
        Cache::put("wa_bot_{$from}", $state, now()->addHours(2));
    }

    private function stepAwaitLocation(string $from, string $text, array $state): void
    {
        if (strlen(trim($text)) < 3) {
            $this->sendText($from, "⚠️ Veuillez préciser votre localisation.");
            return;
        }
        $state['location_hint'] = trim($text);
        $state['step'] = 'await_description';
        $this->sendText($from,
            "Décrivez votre *problème* :\n\n" .
            "_Ex : Mon compteur ne fonctionne plus depuis 3 jours, pas d'électricité_"
        );
        Cache::put("wa_bot_{$from}", $state, now()->addHours(2));
    }

    private function stepAwaitDescription(string $from, string $text, array $state): void
    {
        if (strlen(trim($text)) < 10) {
            $this->sendText($from, "⚠️ Veuillez décrire votre problème plus précisément.");
            return;
        }
        $state['description'] = trim($text);
        $state['step'] = 'confirm';

        $contract = $state['contract_number'] ?? '_Non fourni_';

        $this->sendText($from,
            "📋 *Récapitulatif de votre demande :*\n\n" .
            "👤 Nom : {$state['client_name']} {$state['client_firstname']}\n" .
            "📄 N° contrat : {$contract}\n" .
            "📞 Téléphone : {$state['contact_phone']}\n" .
            "📍 Localisation : {$state['location_hint']}\n" .
            "📝 Problème : {$state['description']}\n\n" .
            "Confirmez-vous l'envoi de cette demande ?\n" .
            "Répondez *oui* pour confirmer, *non* pour recommencer."
        );
        Cache::put("wa_bot_{$from}", $state, now()->addHours(2));
    }

    private function stepConfirm(string $from, string $text, array $state): void
    {
        if (strtolower($text) === 'non' || strtolower($text) === 'n') {
            Cache::forget("wa_bot_{$from}");
            $this->stepStart($from);
            return;
        }

        if (!in_array(strtolower($text), ['oui', 'o', 'yes', 'y'])) {
            $this->sendText($from, "⚠️ Veuillez répondre *oui* pour confirmer ou *non* pour recommencer.");
            return;
        }

        // Sauvegarder la demande WhatsApp
        $waRequest = WhatsappRequest::create([
            'wa_phone'         => $from,
            'client_name'      => $state['client_name'],
            'client_firstname' => $state['client_firstname'],
            'contract_number'  => $state['contract_number'],
            'contact_phone'    => $state['contact_phone'],
            'location_hint'    => $state['location_hint'],
            'description'      => $state['description'],
            'conversation'     => $state,
            'status'           => 'COMPLETED',
        ]);

        Cache::forget("wa_bot_{$from}");

        $ref = '#WA-' . str_pad($waRequest->id, 4, '0', STR_PAD_LEFT);

        $this->sendText($from,
            "✅ *Demande enregistrée !*\n\n" .
            "📌 Référence : *{$ref}*\n\n" .
            "Votre demande a bien été reçue. Un agent de la SOCADEL va l'examiner " .
            "et créer un ticket de suivi.\n\n" .
            "Vous serez informé(e) dès la prise en charge.\n\n" .
            "_Merci de votre confiance — SOCADEL Extrême-Nord_"
        );
    }

    // ── Envoi de message via le serveur Node.js ──────────────
    private function sendText(string $to, string $body): void
    {
        \Log::info('Envoi message WhatsApp', ['to' => $to, 'text' => $body]);

        $response = Http::post('http://localhost:3000/send', [
            'to'   => $to,   // chatId complet
            'text' => $body,
        ]);

        \Log::info('Réponse de Node.js', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
    }
}
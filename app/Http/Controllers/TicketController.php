<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Sla;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\Unit;
use App\Models\TicketComment;
use App\Models\Company;
use App\Models\Client;
use App\Models\User;
use App\Models\TicketActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\TicketActionService;
use App\Notifications\TicketStatusNotification;
use Illuminate\Support\Facades\Http;

class TicketController extends Controller
{
    /**
     * Liste des tickets
     */
    public function index(Request $request)
{
    $user = auth()->user();
    $user->load('role');
    $role = strtoupper($user->role?->name);

    // ----- Déterminer si l'utilisateur peut créer un ticket -----
    $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;
    $canCreateTicket = false;

    if (in_array($role, ['ADMIN', 'MANAGER', 'CUSTOMER_SERVICE'])) {
        $canCreateTicket = true;
    } elseif ($role === 'SUPERVISOR' && $user->company_id == $eneoCompanyId) {
        $canCreateTicket = true;
    }

    // ----- Filtres -----
    $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
    $endDate   = $request->filled('end_date')   ? Carbon::parse($request->end_date)->endOfDay()   : null;
    $selectedUnitId = $request->filled('unit_id') ? (int)$request->unit_id : null;
    $selectedTypeId = $request->filled('type_id') ? (int)$request->type_id : null;

    // ----- Requête de base -----
    $query = Ticket::with(['type', 'unit']);

    // Filtre par statut
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    // Filtre "en retard" (tickets non résolus/clos avec SLA dépassé)
    if ($request->has('late')) {
        $query->whereNotIn('status', ['RESOLVED', 'CLOSED'])
              ->where('resolution_due_at', '<', now());
    }

    // ==== RESTRICTION PAR RÔLE =====
    if (in_array($role, ['ADMIN', 'MANAGER', 'CUSTOMER_SERVICE'])) {
        // Accès complet à tous les tickets (aucune restriction)
    } elseif ($role === 'SUPERVISOR') {
        $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhereHas('supervisors', fn($sq) => $sq->where('user_id', $user->id));
        });
    } elseif ($role === 'TECHNICIAN') {
        $query->whereHas('technicians', fn($q) => $q->where('users.id', $user->id));
    } else {
        // Par défaut (client, etc.) : seulement ses propres tickets
        $query->where('created_by', $user->id);
    }

    // Application des filtres (unité, type, dates)
    if ($selectedUnitId && !in_array($role, ['MANAGER', 'ADMIN', 'CUSTOMER_SERVICE'])) {
        $query->where('unit_id', $selectedUnitId);
    }
    if ($selectedTypeId) {
        $query->where('type_id', $selectedTypeId);
    }
    if ($startDate) {
        $query->where('created_at', '>=', $startDate);
    }
    if ($endDate) {
        $query->where('created_at', '<=', $endDate);
    }

    // Récupération des tickets
    $tickets = $query->latest()->get();

    // Données pour les filtres (unités et types) – pour l'affichage
    if (in_array($role, ['ADMIN', 'MANAGER', 'CUSTOMER_SERVICE'])) {
        $units = Unit::orderBy('name')->get();
    } else {
        $units = Unit::where('id', $selectedUnitId)->get();
    }

    $unitIdForTypes = $selectedUnitId ?? null;
    if ($unitIdForTypes) {
        $eligibleTypes = Type::where('unit_id', $unitIdForTypes)->orderBy('name')->get();
    } else {
        $eligibleTypes = Type::orderBy('name')->get();
    }

    $statusColors = [
        'OPEN' => 'warning',
        'IN_PROGRESS' => 'primary',
        'ON_HOLD' => 'secondary',
        'RESOLVED' => 'success',
        'CLOSED' => 'dark',
        'TRANSFERRED' => 'secondary',
        'REOPENED' => 'danger'
    ];

    // ----- Comptes par statut (basés sur la requête filtrée) -----
    $stats = [
        'total'      => (clone $query)->count(),
        'open'       => (clone $query)->where('status', 'OPEN')->count(),
        'inProgress' => (clone $query)->where('status', 'IN_PROGRESS')->count(),
        'transferred'=> (clone $query)->where('status', 'TRANSFERRED')->count(),
        'onHold'     => (clone $query)->where('status', 'ON_HOLD')->count(),
        'reopened'   => (clone $query)->where('status', 'REOPENED')->count(),
        'resolved'   => (clone $query)->where('status', 'RESOLVED')->count(),
        'closed'     => (clone $query)->where('status', 'CLOSED')->count(),
        'late'       => (clone $query)
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where('resolution_due_at', '<', now())
            ->count(),
    ];

    return view('tickets.index', compact(
        'tickets', 'statusColors', 'units', 'eligibleTypes',
        'selectedUnitId', 'selectedTypeId', 'startDate', 'endDate', 'role', 'stats', 'canCreateTicket'
    ));
}

    /**
     * Formulaire création ticket
     */
    public function create()
{
    $user = auth()->user();
    $role = strtolower($user->role?->name ?? '');

    // Vérifier le rôle autorisé
    if (!in_array($role, ['manager', 'customer_service', 'supervisor'])) {
        abort(403);
    }

    // Récupérer l'ID de l'entreprise ENEO
    $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;

    // Interdire la création aux superviseurs qui ne sont pas ENEO
    if ($role === 'supervisor' && $user->company_id != $eneoCompanyId) {
        abort(403, 'Seuls les superviseurs ENEO peuvent créer des tickets.');
    }

    $units = Unit::all();
    $types = Type::all();
    $agencies = Agency::all();

    $users = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))
        ->with(['agency', 'unit', 'company'])
        ->orderBy('name')
        ->get();

    return view('tickets.create', compact('units', 'types', 'agencies', 'users', 'eneoCompanyId'));
}

    /**
     * Enregistrement ticket
     */
    public function store(Request $request)
{
    $user = auth()->user();
    $role = strtolower($user->role?->name ?? '');

    if (!in_array($role, ['manager', 'customer_service', 'supervisor'])) {
        abort(403, 'Accès interdit');
    }

      // Vérification supplémentaire pour superviseur ENEO uniquement
    $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;
    if ($role === 'supervisor' && $user->company_id != $eneoCompanyId) {
        abort(403, 'Seuls les superviseurs ENEO peuvent créer des tickets.');
    }

    $data = $request->validate([
        'unit_id'                  => 'required|exists:units,id',
        'type_id'                  => 'required|exists:types,id',
        'agency_id'                => 'required|exists:agencies,id',
        'assigned_to'              => 'nullable|exists:users,id',
        'description'              => 'required|string',
        'priority'                 => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
        'contract_number'          => 'nullable|string',
        'attachment_path'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'client_contract_number'   => 'nullable|string|max:50',
        'client_name'              => 'required|string|max:255',
        'client_firstname'         => 'nullable|string|max:255',
        'client_phone'             => 'required|string|max:20',
        'client_delivery_point'    => 'nullable|string',
    ]);

    // Gestion du client (existant ou création)
    $client = Client::where('contract_number', $data['client_contract_number'])
                    ->orWhere('phone', $data['client_phone'])
                    ->first();

    if (!$client) {
        $client = Client::create([
            'contract_number' => $data['client_contract_number'],
            'name'            => $data['client_name'],
            'firstname'       => $data['client_firstname'],
            'phone'           => $data['client_phone'],
            'delivery_point'  => $data['client_delivery_point'],
        ]);
    }

    // Upload du fichier
    $attachmentPath = null;
    if ($request->hasFile('attachment_path')) {
        $attachmentPath = $request->file('attachment_path')->store('tickets', 'public');
    }

    // SLA
    $sla = Sla::where('priority', $data['priority'])
              ->where('is_active', 1)
              ->first();

    $resolutionDeadline = $sla ? Carbon::now()->addHours($sla->resolution_time) : null;

    // Création du ticket
    $ticket = Ticket::create([
        'unit_id'             => $data['unit_id'],
        'type_id'             => $data['type_id'],
        'agency_id'           => $data['agency_id'],
        'assigned_to'         => $data['assigned_to'] ?? null,
        'description'         => $data['description'],
        'priority'            => $data['priority'],
        'contract_number'     => $data['contract_number'] ?? null,
        'attachment_path'     => $attachmentPath,
        'client_id'           => $client->id,
        'status'              => 'OPEN',
        'sla_id'              => $sla?->id,
        'resolution_deadline' => $resolutionDeadline,
        'created_by' => auth()->id(),
    ]);

    // 🔁 Ajouter le superviseur assigné dans la table pivot (si fourni)
    if ($ticket->assigned_to) {
        $supervisor = User::find($ticket->assigned_to);
        if ($supervisor) {
            $ticket->addSupervisor($supervisor);
        }
    }

    return redirect()
        ->route('tickets.index')
        ->with('success', 'Ticket créé avec succès');
}

    private function buildActivities($ticket)
{
    $comments = $ticket->comments->map(function ($c) {
        return [
            'type' => 'comment',
            'date' => $c->created_at,
            'data' => $c
        ];
    });

    $activities = $ticket->activities->map(function ($a) {
        return [
            'type' => $a->type,
            'date' => $a->created_at,
            'data' => $a
        ];
    });

    $documents = $ticket->documents->map(function ($d) {
        return [
            'type' => 'document',
            'date' => $d->created_at,
            'data' => $d
        ];
    });

    return $comments
        ->merge($activities)
        ->merge($documents)
        ->sortBy('date')
        ->values();
}

public function transfer(Request $request, Ticket $ticket)
{
    $role = strtoupper(auth()->user()->role?->name);
    if ($role === 'TECHNICIAN') {
        abort(403, 'Action non autorisée');
    }

    $request->validate([
        'target_type' => 'required|in:user,company',
        'user_id' => 'required_if:target_type,user|exists:users,id',
        'company_id' => 'required_if:target_type,company|exists:companies,id',
        'unit_id' => 'nullable|integer|exists:units,id',
        'agency_id' => 'nullable|integer|exists:agencies,id',
        'reason' => 'required|string|max:500',
        'attachment1' => 'required_if:target_type,company|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'attachment2' => 'required_if:target_type,company|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);

    $reason = $request->reason ? " Motif : {$request->reason}" : '';

    // Récupérer l'ancien superviseur (s'il existe)
    $oldSupervisor = $ticket->assigned_to ? User::find($ticket->assigned_to) : null;

    if ($request->target_type === 'user') {
        // Transfert vers un superviseur ENEO
        $supervisor = User::findOrFail($request->user_id);
        if (!$supervisor->role || $supervisor->role->name !== 'SUPERVISOR') {
            return back()->withErrors(['user_id' => 'La cible doit être un superviseur']);
        }

        // Ajouter l'ancien superviseur à l'historique (s'il existe)
        if ($oldSupervisor) {
            $ticket->addSupervisor($oldSupervisor);
        }

        $ticket->update([
            'assigned_to' => $supervisor->id,
            'company_id' => null,
            'status' => 'TRANSFERRED',
        ]);

        // Ajouter le nouveau superviseur à l'historique
        $ticket->addSupervisor($supervisor);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'type' => 'transfer',
            'message' => "Ticket transféré à {$supervisor->name}",
        ]);
    } else {
        // Transfert vers une entreprise externe
        $company = Company::findOrFail($request->company_id);
        $supervisor = User::findOrFail($request->user_id);

        // Vérifier que le superviseur appartient bien à l'entreprise
        if ($supervisor->company_id != $company->id) {
            return back()->withErrors(['user_id' => 'Ce superviseur n\'appartient pas à l\'entreprise sélectionnée.']);
        }

        // Ajouter l'ancien superviseur à l'historique (s'il existe)
        if ($oldSupervisor) {
            $ticket->addSupervisor($oldSupervisor);
        }

        $path1 = $request->file('attachment1')->store('transfers', 'public');
        $path2 = $request->file('attachment2')->store('transfers', 'public');

        $ticket->update([
            'assigned_to' => $supervisor->id,
            'company_id' => $company->id,
            'status' => 'TRANSFERRED',
        ]);

        // Ajouter le nouveau superviseur à l'historique
        $ticket->addSupervisor($supervisor);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'type' => 'transfer',
            'message' => "Ticket transféré à l'entreprise {$company->name} (superviseur: {$supervisor->name})",
            'attachment_path' => $path1,
            'attachment2_path' => $path2,
        ]);
    }

    return back()->with('success', 'Ticket transféré avec succès');
}  

public function reopen(Request $request, Ticket $ticket)
{
    $request->validate([
        'reason' => 'required|string|min:5',
        'attachment' => 'nullable|file|max:5120'
    ]);

    // if ($ticket->status === 'CLOSED') {
    //     abort(403, 'Impossible de rouvrir un ticket clôturé');
    // }

    if (!in_array(strtoupper(auth()->user()->role?->name), ['CUSTOMER_SERVICE', 'SUPERVISOR'])) {
        abort(403);
    }

    $path = null;

    if ($request->hasFile('attachment')) {
        $path = $request->file('attachment')
            ->store('ticket-reopens', 'public');
    }

    $ticket->update([
        'status' => 'REOPENED'
    ]);

    TicketActivity::create([
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
        'type' => 'reopen',
        'message' => $request->reason,
        'attachment_path' => $path,
    ]);

    return back()->with('success', 'Ticket réouvert');
}

public function show(Ticket $ticket)
{
    $ticket->load(['comments.user', 'documents.uploader', 'user', 'technicians']);

    $technicians = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))
        ->where('unit_id', $ticket->unit_id)
        ->where('agency_id', $ticket->agency_id)
        ->orderBy('name')
        ->get();

    $supervisors = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))
        ->with(['agency', 'unit'])
        ->get();

    // Grouper les superviseurs par entreprise
    $supervisorsByCompany = $supervisors->groupBy('company_id');

    $users = User::orderBy('name')->get();

    $ticket->load(['comments.user', 'documents.uploader', 'user', 'technicians']);

    $user = auth()->user();
    $role = strtoupper($user->role?->name);
    $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;

    // 🔽 Filtrage des techniciens selon le rôle
    if ($role === 'SUPERVISOR' && $user->company_id != $eneoCompanyId) {
        // Superviseur d'entreprise externe → techniciens de sa propre entreprise
        $technicians = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))
            ->where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();
    } else {
        // Autres rôles (manager, admin, customer_service, superviseur ENEO)
        $technicians = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))
            ->where('unit_id', $ticket->unit_id)
            ->where('agency_id', $ticket->agency_id)
            ->orderBy('name')
            ->get();
    }

    // ACTIVITIES
    $activities = collect();
    foreach ($ticket->activities as $activity) {
        $activities->push(['type' => $activity->type, 'date' => $activity->created_at, 'data' => $activity]);
    }
    foreach ($ticket->comments as $comment) {
        $activities->push(['type' => 'comment', 'date' => $comment->created_at, 'data' => $comment]);
    }
    foreach ($ticket->documents as $document) {
        $activities->push(['type' => 'document', 'date' => $document->created_at, 'data' => $document]);
    }
    $activities = $activities->sortBy('date')->values();

    $units = Unit::all();
    $agencies = Agency::all();
    $companies = Company::all();
    $actions = TicketActionService::allowedActions($ticket, auth()->user());
    $ticket->checkSla();

    return view('tickets.show', compact(
        'ticket', 'activities', 'technicians', 'users', 'actions',
        'supervisors', 'supervisorsByCompany', 'units', 'agencies', 'companies'
    ));
}
    /**
     * Vue assignations
     */
    public function assignments()
{
    $tickets = Ticket::with(['unit', 'type', 'agency'])
        ->whereNull('taken_by') // pas encore pris par technicien
        ->latest()
        ->get();

    $technicians = User::whereHas('role', function ($q) {
        $q->where('name', 'TECHNICIAN');
    })->get();

    return view('tickets.assignments', compact('tickets', 'technicians'));
}

    /**
     * Assigner un ticket
     */
    public function assign(Request $request, Ticket $ticket)
{
    $request->validate([
        'technicians' => 'required|array'
    ]);

    $ticket->technicians()->sync($request->technicians);

    $ticket->update([
        'status' => 'ASSIGNED_TO_TECHNICIANS'
    ]);

    // noms des techniciens
    $names = User::whereIn('id', $request->technicians)
        ->pluck('name')
        ->implode(', ');

    TicketActivity::create([
    'ticket_id' => $ticket->id,
    'user_id' => auth()->id(),
    'type' => 'assignment',
    'message' => 'Techniciens assignés',
]);

    return back()->with(
        'success',
        'Techniciens assignés avec succès'
    );
}

public function resolve(Request $request, $id)
{
    $request->validate([
        'resolution_description' => 'required|string',
        'resolution_attachment' => 'nullable|file|max:5120'
    ]);

    $ticket = Ticket::findOrFail($id);

    $attachmentPath = null;

    // upload fichier
    if ($request->hasFile('resolution_attachment')) {

        $attachmentPath = $request
            ->file('resolution_attachment')
            ->store('resolutions', 'public');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE TICKET
    |--------------------------------------------------------------------------
    */

    $ticket->update([
    'resolution_note' => $request->resolution_description,
    'resolved_at' => now(),
    // 'resolved_by' => Auth::id(),
    'status' => 'RESOLVED',
    ]);

    /*
    |--------------------------------------------------------------------------
    | CREATE COMMENT (HISTORIQUE)
    |--------------------------------------------------------------------------
    */

    TicketActivity::create([
    'ticket_id' => $ticket->id,
    'user_id' => auth()->id(),
    'type' => 'resolution',
    'message' => $request->resolution_description,
    'attachment_path' => $attachmentPath
]);
    $this->notifyUsers($ticket, "Ticket #{$ticket->id} résolu par " . auth()->user()->name, 'résolution');

    return redirect()
        ->back()
        ->with('success', 'Ticket résolu avec succès');
}

public function start(Ticket $ticket)
{
    $ticket->update([
        'status' => 'IN_PROGRESS',
        'started_at' => now(),
        'taken_by' => auth()->id()
    ]);

    // TicketComment::create([
    //     'ticket_id' => $ticket->id,
    //     'user_id' => 1,
    //     'message' => 'Ticket en cours de traitement'
    // ]);

    return back()->with(
        'success',
        'Ticket en cours de traitement'
    );
}

public function hold(Request $request, Ticket $ticket)
{
    $request->validate([
        'reason' => 'required|string|min:5',
        'attachment' => 'nullable|file|max:5120'
    ]);

    $path = null;

    if ($request->hasFile('attachment')) {
        $path = $request->file('attachment')
            ->store('ticket-holds', 'public');
    }

    $ticket->update([
        'status' => 'ON_HOLD',
        'is_sla_paused' => true,
        'sla_paused_at' => now(),
    ]);

    TicketActivity::create([
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
        'type' => 'hold',
        'message' => $request->reason,
        'attachment_path' => $path,
    ]);

    return back()->with('success', 'Ticket mis en attente');
}

public function resume(Ticket $ticket)
{
    /*
    |--------------------------------------------------------------------------
    | Vérifier pause SLA
    |--------------------------------------------------------------------------
    */

    if (!$ticket->sla_paused_at) {

        return back()->with(
            'error',
            'Aucune pause SLA active'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calcul durée pause
    |--------------------------------------------------------------------------
    */

    $pauseDuration = now()->diffInSeconds(
        $ticket->sla_paused_at
    );

    /*
    |--------------------------------------------------------------------------
    | Nouvelle deadline
    |--------------------------------------------------------------------------
    */

    $newResolutionDueAt = $ticket->resolution_due_at
        ->copy()
        ->addSeconds($pauseDuration);

    /*
    |--------------------------------------------------------------------------
    | Update ticket
    |--------------------------------------------------------------------------
    */

    $ticket->update([

        'status' => 'IN_PROGRESS',

        'resolution_due_at' => $newResolutionDueAt,

        'total_pause_duration' =>
            $ticket->total_pause_duration + $pauseDuration,

        'is_sla_paused' => false,

        'sla_paused_at' => null,

    ]);

    /*
    |--------------------------------------------------------------------------
    | Historique
    |--------------------------------------------------------------------------
    */

    TicketActivity::create([

        'ticket_id' => $ticket->id,

        'user_id' => auth()->id(),

        'type' => 'resume',

        'message' => 'Traitement repris',

    ]);

    return back()->with(
        'success',
        'Traitement repris'
    );
}

public function close(Ticket $ticket)
{
    $ticket->update([
        'status' => 'CLOSED',
        'closed_at' => now(),
        'closed_by' => auth()->id()
    ]);

    // TicketComment::create([
    //     'ticket_id' => $ticket->id,
    //     'user_id' => 1,
    //     'message' => 'Ticket clôturé'
    // ]);

    return back()->with(
        'success',
        'Ticket clôturé'
    );
}

public function checkSla()
{
    if (
        !$this->is_sla_paused
        && $this->resolution_due_at
        && now()->greaterThan($this->resolution_due_at)
    ) {

        $this->update([
            'is_sla_breached' => true
        ]);

    }
}

public function filterSupervisors(Request $request)
{
    $supervisors = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))
        ->where('unit_id', $request->unit_id)
        ->where('agency_id', $request->agency_id)
        ->where('company_id', 1) // ENEO
        ->orderBy('name')
        ->get();
    return response()->json($supervisors);
}

private function notifyUsers(Ticket $ticket, $message, $type)
{
    $userIds = collect();

    if ($ticket->created_by) $userIds->push($ticket->created_by);
    if ($ticket->assigned_to) $userIds->push($ticket->assigned_to);

    foreach ($ticket->technicians as $tech) {
        $userIds->push($tech->id);
    }

    $userIds = $userIds->unique();

    foreach ($userIds as $uid) {
        $user = User::find($uid);

        if ($user && $user->onesignal_player_id) {
            $this->sendOneSignal(
                $user->onesignal_player_id,
                "Ticket mis à jour",
                $message,
                [
                    'ticket_id' => $ticket->id,
                    'type' => $type
                ]
            );
        }
    }
}

private function sendOneSignal($playerId, $title, $message, $data = [])
{
    // 1. Vérifiez que les variables d'environnement sont chargées
    $appId = env('ONESIGNAL_APP_ID');
    $apiKey = env('ONESIGNAL_REST_API_KEY');
    \Log::info('ONESIGNAL CONFIG', ['app_id' => $appId, 'api_key_exists' => !empty($apiKey)]);

    // 2. Construisez le payload
    $payload = [
        'app_id' => $appId,
        'include_player_ids' => [$playerId],
        'headings' => ['en' => $title],
        'contents' => ['en' => $message],
        'data' => $data
    ];
    \Log::info('ONESIGNAL PAYLOAD', $payload);

    // 3. Envoyez la requête avec options SSL
    $response = Http::withOptions([
        'verify' => 'C:/wamp64/bin/php/cacert.pem', // chemin absolu
    ])->withHeaders([
        'Authorization' => 'Basic ' . $apiKey,
        'Content-Type' => 'application/json',
    ])->post('https://onesignal.com/api/v1/notifications', $payload);

    // 4. Log détaillé de la réponse
    \Log::info('ONESIGNAL RESPONSE', [
        'status' => $response->status(),
        'body' => $response->json(),
    ]);

    if ($response->failed()) {
        \Log::error('ONESIGNAL FAILED', ['error' => $response->body()]);
    }
}
}
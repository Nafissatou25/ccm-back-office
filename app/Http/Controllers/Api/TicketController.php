<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Sla;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\Type;
use App\Models\Unit;
use App\Models\Client;
use App\Models\User;
use App\Models\Company;
use App\Services\TicketActionService;
use App\Services\SlaService;
use App\Models\TicketView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\TicketNotificationService;

class TicketController extends Controller
{
    // =========================================================
    // INDEX
    // =========================================================
    public function index(Request $request)
    {
        $user = auth()->user();
        $user->load('role');
        $role = strtoupper($user->role?->name);

        $query = Ticket::with(['type', 'unit', 'client',]);

        // Mêmes règles que le web
        if (in_array($role, ['ADMIN', 'MANAGER', 'CUSTOMER_SERVICE'])) {
            // accès complet
        } elseif ($role === 'SUPERVISOR') {
            $query->where(function ($q) use ($user) {
    $q->where('assigned_to', $user->id)
      ->orWhereHas('supervisors', fn($sq) => $sq->where('user_id', $user->id));
});
        } elseif ($role === 'TECHNICIAN') {
            $query->whereHas('technicians', fn($q) => $q->where('users.id', $user->id));
        } else {
            $query->where('created_by', $user->id);
        }

        // Filtres optionnels
        if ($request->filled('unit_id'))   $query->where('unit_id', $request->unit_id);
        if ($request->filled('type_id'))   $query->where('type_id', $request->type_id);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        if ($request->filled('end_date'))   $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());

        return response()->json($query->latest()->get());
    }

    // =========================================================
    // STORE
    // =========================================================
    public function store(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->role?->name ?? '');

        if (!in_array($role, ['manager', 'customer_service', 'supervisor', 'admin'])) {
            abort(403, 'Accès interdit');
        }

        $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;
        if ($role === 'supervisor' && $user->company_id != $eneoCompanyId) {
            abort(403, 'Seuls les superviseurs ENEO peuvent créer des tickets.');
        }

        $data = $request->validate([
    'unit_id'                => 'required|exists:units,id',
    'type_id'                => 'required|exists:types,id',
    'agency_id'              => 'required|exists:agencies,id',
    'assigned_to'            => 'nullable|exists:users,id',

    'description'            => 'required|string',
    'is_urgent'              => 'nullable|boolean',

    'attachment_path'        => 'required|file',

    'client_contract_number' => 'nullable|string|max:50',
    'client_name'            => 'required|string|max:255',
    'client_firstname'       => 'nullable|string|max:255',
    'client_phone'           => 'required|string|max:20',
    'client_whatsapp'        => 'nullable|string|max:20',
    'client_delivery_point'  => 'nullable|string',
]);
        $isUrgent = $request->boolean('is_urgent');

        if ($request->hasFile('attachment_path')) {
            $data['attachment_path'] = $request->file('attachment_path')->store('tickets', 'public');
        }

$client = Client::where(
    'contract_number',
    $data['client_contract_number']
)->orWhere(
    'phone',
    $data['client_phone']
)->first();

if (!$client) {
    $client = Client::create([
        'contract_number' => $data['client_contract_number'] ?? null,
        'name'            => $data['client_name'],
        'firstname'       => $data['client_firstname'] ?? null,
        'phone'           => $data['client_phone'],
        'whatsapp'        => $data['client_whatsapp'] ?? null,
        'delivery_point'  => $data['client_delivery_point'] ?? null,
    ]);
}

        $ticket = Ticket::create([
            'unit_id'             => $data['unit_id'],
            'type_id'             => $data['type_id'],
            'agency_id'           => $data['agency_id'],
            'assigned_to'         => $data['assigned_to'] ?? null,
            'description'         => $data['description'],
            'is_urgent'           => $isUrgent,
            'contract_number'     => $data['contract_number'] ?? null,
            'attachment_path'     => $data['attachment_path'] ?? null,
            'status'              => 'OPEN',
            // 'sla_id'              => $sla?->id,
            // 'resolution_deadline' => $resolutionDeadline,
            'created_by'          => $user->id,
            'client_id' => $client->id
        ]);
        SlaService::applySla($ticket);
$ticket->save();

        return response()->json(['message' => 'Ticket créé avec succès', 'ticket' => $ticket], 201);
    }


    // =========================================================
    // SHOW
    // =========================================================
    public function show(Ticket $ticket)
    {
        $ticket->load(['client', 'type', 'unit', 'agency', 'technicians', 'comments.user', 'documents.uploader', 'activities']);
        $actions = TicketActionService::allowedActions($ticket, auth()->user());
        TicketView::firstOrCreate(
    [
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
    ],
    [
        'viewed_at' => now(),
    ]
);
        return response()->json([
            'ticket'  => $ticket,
            'actions' => $actions,
        ]);
    }

    // =========================================================
    // ASSIGN TECHNICIANS
    // =========================================================
    public function assignTechnicians(Request $request, Ticket $ticket)
    {
        $request->validate([
            'technicians'   => 'required|array',
            'technicians.*' => 'exists:users,id',
        ]);

        $user = auth()->user();
        $role = strtoupper($user->role?->name);

        if (!in_array($role, ['SUPERVISOR', 'MANAGER', 'ADMIN'])) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $ticket->technicians()->sync($request->technicians);
        // $ticket->update(['status' => 'IN_PROGRESS']);
        app(TicketNotificationService::class)->notifyAssigned($ticket);


        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'type'      => 'assignment',
            'message'   => 'Techniciens assignés',
        ]);

        return response()->json(['message' => 'Techniciens assignés', 'ticket' => $ticket->load('technicians')]);
    }

    // =========================================================
    // START
    // =========================================================
    public function start(Ticket $ticket)
{
    $actions = TicketActionService::allowedActions(
        $ticket,
        auth()->user()
    );

    if (!in_array('start', $actions)) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

    $ticket->update([
        'status' => 'IN_PROGRESS',
        'started_at' => now(),
        'taken_by' => auth()->id()
    ]);

    return response()->json([
        'message' => 'Ticket en cours de traitement',
        'ticket' => $ticket->fresh()
    ]);
}

public function getTechnicians(Ticket $ticket)
{
    $user = auth()->user();
    $role = strtoupper($user->role?->name);
    $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;

    // Superviseur entreprise externe → ses propres techniciens
    if ($role === 'SUPERVISOR' && $user->company_id != $eneoCompanyId) {
        $technicians = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))
            ->where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();
    } else {
        // Superviseur ENEO / Manager / Admin → techniciens de l'unité + agence du ticket
        $technicians = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))
            ->where('unit_id', $ticket->unit_id)
            ->where('agency_id', $ticket->agency_id)
            ->orderBy('name')
            ->get();
    }

    return response()->json($technicians);
}

    // =========================================================
    // RESOLVE
    // =========================================================
    public function resolve(Request $request, Ticket $ticket)
{
    $request->validate([
        'resolution_description' => 'required|string',
        'resolution_attachment' => 'nullable|file|max:5120'
    ]);

    $actions = TicketActionService::allowedActions(
        $ticket,
        auth()->user()
    );

    if (!in_array('resolve', $actions)) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

    $attachmentPath = null;

    if ($request->hasFile('resolution_attachment')) {
        $attachmentPath = $request
            ->file('resolution_attachment')
            ->store('resolutions', 'public');
    }

    $ticket->update([
        'resolution_note' => $request->resolution_description,
        'resolved_at' => now(),
        'status' => 'RESOLVED',
    ]);

    TicketActivity::create([
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
        'type' => 'resolution',
        'message' => $request->resolution_description,
        'attachment_path' => $attachmentPath,
    ]);

    // app(TicketNotificationService::class)->notifyResolved($ticket);

    return response()->json([
        'message' => 'Ticket résolu avec succès',
        'ticket' => $ticket->fresh()
    ]);
}

    // =========================================================
    // CLOSE
    // =========================================================
    public function closeTicket(Ticket $ticket)
{
    $actions = TicketActionService::allowedActions(
        $ticket,
        auth()->user()
    );

    if (!in_array('close', $actions)) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

    $ticket->update([
        'status' => 'CLOSED',
        'closed_at' => now(),
        'closed_by' => auth()->id()
    ]);

    app(TicketNotificationService::class)->notifyClosed($ticket);

    return response()->json([
        'message' => 'Ticket clôturé',
        'ticket' => $ticket->fresh()
    ]);
}

    // =========================================================
    // HOLD
    // =========================================================
    public function hold(Request $request, Ticket $ticket)
{
    $request->validate([
        'reason' => 'required|string',
        'attachment' => 'nullable|file|max:5120'
    ]);

    $actions = TicketActionService::allowedActions(
        $ticket,
        auth()->user()
    );

    if (!in_array('hold', $actions)) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

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

    return response()->json([
        'message' => 'Ticket mis en attente',
        'ticket' => $ticket->fresh()
    ]);
}

    // =========================================================
    // RESUME
    // =========================================================
    public function resume(Ticket $ticket)
    {
        if (!$ticket->sla_paused_at) {
            return response()->json(['message' => 'Aucune pause SLA active'], 422);
        }

        $pauseDuration     = now()->diffInSeconds($ticket->sla_paused_at);
        $newResolutionDueAt = $ticket->resolution_due_at?->copy()->addSeconds($pauseDuration);

        $ticket->update([
            'status'               => 'IN_PROGRESS',
            'resolution_due_at'    => $newResolutionDueAt,
            'total_pause_duration' => $ticket->total_pause_duration + $pauseDuration,
            'is_sla_paused'        => false,
            'sla_paused_at'        => null,
        ]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'type'      => 'resume',
            'message'   => 'Traitement repris',
        ]);

        return response()->json(['message' => 'Traitement repris', 'ticket' => $ticket]);
    }

    // =========================================================
    // TRANSFER
    // =========================================================
    public function transfer(Request $request, Ticket $ticket)
    {

        if (strtoupper(auth()->user()->role?->name) === 'TECHNICIAN') {
            abort(403);
        }

       $request->validate([
    'target_type' => 'required|in:user,company',

    'user_id' => [
        Rule::requiredIf($request->target_type === 'user'),
        'exists:users,id'
    ],

    'company_id' => [
        'nullable',
        Rule::requiredIf($request->target_type === 'company'),
        'exists:companies,id'
    ],
    

    'reason' => 'required|string|max:500',
]);

        $oldSupervisor = $ticket->assigned_to ? User::find($ticket->assigned_to) : null;

        if ($request->target_type === 'user') {
            $supervisor = User::findOrFail($request->user_id);
           
            if (
    !$supervisor->role ||
    strtoupper($supervisor->role->name) !== 'SUPERVISOR'
) {
    return back()->withErrors([
        'user_id' => 'La cible doit être un superviseur'
    ]);
}

            if ($oldSupervisor) $ticket->addSupervisor($oldSupervisor);
           
            $ticket->update(['assigned_to' => $supervisor->id, 'company_id' => null, 'status' => 'TRANSFERRED',  'unit_id'     => $supervisor->unit_id,]);
            $ticket->addSupervisor($supervisor);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id'   => auth()->id(),
                'type'      => 'transfer',
                'message'   => "Ticket transféré à {$supervisor->name}",
            ]);
        } else {
            $company    = Company::findOrFail($request->company_id);
            $supervisor = User::findOrFail($request->user_id);

            if ($supervisor->company_id != $company->id) {
                return back()->withErrors(['user_id' => 'Cet utilisateur n\'appartient pas à cette entreprise.']);
            }

            if ($oldSupervisor) $ticket->addSupervisor($oldSupervisor);

            $path1 = $request->file('attachment1')->store('transfers', 'public');
            $path2 = $request->file('attachment2')->store('transfers', 'public');

            $ticket->update(['assigned_to' => $supervisor->id, 'company_id' => $company->id, 'status' => 'TRANSFERRED',]);
            $ticket->addSupervisor($supervisor);

            TicketActivity::create([
                'ticket_id'        => $ticket->id,
                'user_id'          => auth()->id(),
                'type'             => 'transfer',
                'message'          => "Ticket transféré à {$company->name} (responsable : {$supervisor->name})",
                'attachment_path'  => $path1,
                'attachment2_path' => $path2,
            ]);
        }

        return back()->with('success', 'Ticket transféré avec succès');
    }

    // =========================================================
    // REOPEN
    // =========================================================
    public function reopen(Request $request, Ticket $ticket)
{
    $request->validate([
        'reason' => 'required|string',
        'attachment' => 'nullable|file|max:5120'
    ]);

    $actions = TicketActionService::allowedActions(
        $ticket,
        auth()->user()
    );

    if (!in_array('reopen', $actions)) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
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

    app(TicketNotificationService::class)->notifyReopened($ticket);

    return response()->json([
        'message' => 'Ticket réouvert',
        'ticket' => $ticket->fresh()
    ]);
}

public function addDocument(Request $request, Ticket $ticket)
{
    $actions = TicketActionService::allowedActions(
        $ticket,
        auth()->user()
    );

    if (!in_array('document', $actions)) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

    $request->validate([
        'file' => 'required|file|max:5120',
        'description' => 'nullable|string'
    ]);

    $path = $request->file('file')
        ->store('ticket-documents', 'public');

    $document = $ticket->documents()->create([
        'uploaded_by' => auth()->id(),
        'path' => $path,
        'description' => $request->description,
    ]);

    TicketActivity::create([
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
        'type' => 'document',
        'message' => $request->description ?? 'Document ajouté',
        'attachment_path' => $path,
    ]);

    return response()->json([
        'message' => 'Document ajouté avec succès',
        'document' => $document
    ]);
}

    // =========================================================
    // CHANGE STATUS (générique)
    // =========================================================
    public function changeStatus(Request $request, Ticket $ticket)
    {
        $request->validate(['status' => 'required|string']);

        $user = auth()->user();
        $role = strtoupper($user->role?->name);

        if (!in_array($role, ['ADMIN', 'MANAGER', 'SUPERVISOR', 'CUSTOMER_SERVICE'])) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $ticket->update(['status' => $request->status]);

        return response()->json(['message' => 'Statut mis à jour', 'ticket' => $ticket]);
    }

    public function supervisors()
{
    $supervisors = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))->get();
    return response()->json($supervisors);
}
}
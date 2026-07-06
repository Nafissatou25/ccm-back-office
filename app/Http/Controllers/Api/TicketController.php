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
use App\Models\SlaRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\TicketNotificationService;
use Illuminate\Validation\Rule;

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

        $query = Ticket::with(['type', 'unit', 'client']);

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

        $attachmentPath = $request->file('attachment_path')->store('tickets', 'public');

        // Gestion du client (identique au web)
        $client = Client::where('phone', $data['client_phone'])->first();
        if (!$client && !empty($data['client_contract_number'])) {
            $client = Client::where('contract_number', $data['client_contract_number'])->first();
        }
        if ($client) {
            $client->update([
                'name'            => $data['client_name'],
                'firstname'       => $data['client_firstname'] ?? $client->firstname,
                'contract_number' => $data['client_contract_number'] ?? $client->contract_number,
                'whatsapp'        => $data['client_whatsapp'] ?? $client->whatsapp,
                'delivery_point'  => $data['client_delivery_point'] ?? $client->delivery_point,
                'phone'           => $data['client_phone'],
            ]);
        } else {
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
            'unit_id'         => $data['unit_id'],
            'type_id'         => $data['type_id'],
            'agency_id'       => $data['agency_id'],
            'assigned_to'     => $data['assigned_to'] ?? null,
            'description'     => $data['description'],
            'is_urgent'       => $isUrgent,
            'attachment_path' => $attachmentPath,
            'client_id'       => $client->id,
            'status'          => 'OPEN',
            'created_by'      => $user->id,
        ]);

        SlaService::applySla($ticket);
        $ticket->save();

        if ($ticket->assigned_to) {
            $supervisor = User::find($ticket->assigned_to);
            if ($supervisor) {
                $ticket->addSupervisor($supervisor);
            }
        }

        return response()->json(['message' => 'Ticket créé avec succès', 'ticket' => $ticket], 201);
    }

    public function quickStore(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->role?->name ?? '');
        $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;

        $canCreate = in_array($role, ['admin', 'manager', 'customer_service'])
            || ($role === 'supervisor' && $user->company_id == $eneoCompanyId);

        if (!$canCreate) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name'    => 'required|string|max:255',
        ]);

        $exists = Type::where('unit_id', $request->unit_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Ce type existe déjà pour cette unité.'], 422);
        }

        $type = Type::create([
            'unit_id' => $request->unit_id,
            'name'    => $request->name,
        ]);

        $slaInfo = ['normal' => null, 'urgent' => null, 'has_default' => false];
        try {
            $slaDefault = SlaRule::where('unit_id', $request->unit_id)
                ->whereNull('type_id')
                ->where('is_active', true)
                ->get()
                ->keyBy('is_urgent');

            $slaInfo = [
                'normal'      => $slaDefault->get(0) ? ['tto' => $slaDefault->get(0)->tto, 'ttr' => $slaDefault->get(0)->ttr] : null,
                'urgent'      => $slaDefault->get(1) ? ['tto' => $slaDefault->get(1)->tto, 'ttr' => $slaDefault->get(1)->ttr] : null,
                'has_default' => $slaDefault->isNotEmpty(),
            ];
        } catch (\Exception $e) {
            \Log::warning('Erreur SLA dans quickStore : ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'type' => $type, 'sla_info' => $slaInfo]);
    }

    // =========================================================
    // SHOW
    // =========================================================
    public function show(Ticket $ticket)
{
    // Charger les relations
    $ticket->load([
        'client',
        'type',
        'unit',
        'agency',
        'technicians',
        'comments.user',
        'documents.uploader',
        'activities.user',  // ⬅️ Important : charger le user pour chaque activité
    ]);

    // Construire la timeline unifiée (comme dans le contrôleur web)
    $activities = collect();

    foreach ($ticket->activities as $a) {
        $activities->push([
            'type' => $a->type,
            'date' => $a->created_at,
            'data' => $a,
        ]);
    }

    foreach ($ticket->comments as $c) {
        $activities->push([
            'type' => 'comment',
            'date' => $c->created_at,
            'data' => $c,
        ]);
    }

    foreach ($ticket->documents as $d) {
        $activities->push([
            'type' => 'document',
            'date' => $d->created_at,
            'data' => $d,
        ]);
    }

    $activities = $activities->sortBy('date')->values();

    $actions = TicketActionService::allowedActions($ticket, auth()->user());

    TicketView::firstOrCreate(
        ['ticket_id' => $ticket->id, 'user_id' => auth()->id()],
        ['viewed_at' => now()]
    );

    return response()->json([
        'ticket' => $ticket,
        'actions' => $actions,
        'activities' => $activities, // ⬅️ Ajout de la timeline unifiée
    ]);
}

    // =========================================================
    // ASSIGN (identique au web)
    // =========================================================
    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate(['technicians' => 'required|array']);

        $ticket->technicians()->sync($request->technicians);

        $technicianNames = User::whereIn('id', $request->technicians)->pluck('name')->implode(', ');

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'type'      => 'assignment',
            'message'   => $technicianNames,
        ]);

        app(TicketNotificationService::class)->notifyAssigned($ticket);

        return response()->json(['message' => 'Techniciens assignés avec succès', 'ticket' => $ticket->load('technicians')]);
    }

    public function assignTechnicians(Request $request, Ticket $ticket)
{
    return $this->assign($request, $ticket);
}

    public function getTechnicians(Ticket $ticket)
    {
        $user = auth()->user();
        $role = strtoupper($user->role?->name);
        $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;

        if ($role === 'SUPERVISOR' && $user->company_id != $eneoCompanyId) {
            $technicians = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))
                ->where('company_id', $user->company_id)
                ->orderBy('name')->get();
        } else {
            $technicians = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))
                ->where('unit_id', $ticket->unit_id)
                ->where('agency_id', $ticket->agency_id)
                ->orderBy('name')->get();
        }

        return response()->json($technicians);
    }

    // =========================================================
    // START (identique au web)
    // =========================================================
    public function start(Ticket $ticket)
    {
        $actions = TicketActionService::allowedActions($ticket, auth()->user());
        if (!in_array('start', $actions)) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $ticket->update([
            'status'     => 'IN_PROGRESS',
            'started_at' => now(),
            'taken_by'   => auth()->id(),
        ]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'type'      => 'start',
            // message optionnel
        ]);

        return response()->json(['message' => 'Ticket en cours de traitement', 'ticket' => $ticket->fresh()]);
    }

    // =========================================================
    // STORE DOCUMENT (identique au web, retour JSON)
    // =========================================================
    public function storeDocument(Request $request, Ticket $ticket)
    {
        $request->validate([
            'name'     => 'required|string|max:500',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('document')->store('documents', 'public');

        $ticket->documents()->create([
            'description' => $request->name,
            'file_path'   => $path,
            'uploaded_by' => auth()->id(),
            'file_name'   => $request->file('document')->getClientOriginalName(),
            'mime_type'   => $request->file('document')->getMimeType(),
            'size'        => $request->file('document')->getSize(),
        ]);

        if (!in_array($ticket->status, ['IN_PROGRESS', 'RESOLVED', 'CLOSED'])) {
            $ticket->update([
                'status'     => 'IN_PROGRESS',
                'started_at' => now(),
                'taken_by'   => auth()->id(),
            ]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id'   => auth()->id(),
                'type'      => 'start',
                // message optionnel
            ]);
        }

        return response()->json(['message' => 'Données d\'inspection enregistrées et ticket démarré.', 'ticket' => $ticket->fresh()]);
    }

    // =========================================================
    // RESOLVE (identique au web)
    // =========================================================
    public function resolve(Request $request, Ticket $ticket)
    {
        $request->validate([
            'resolution_description' => 'required|string',
            'resolution_attachment'  => 'nullable|file|max:5120',
        ]);

        $actions = TicketActionService::allowedActions($ticket, auth()->user());
        if (!in_array('resolve', $actions)) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $attachmentPath = null;
        if ($request->hasFile('resolution_attachment')) {
            $attachmentPath = $request->file('resolution_attachment')->store('resolutions', 'public');
        }

        $ticket->update([
            'resolution_note' => $request->resolution_description,
            'resolved_at'     => now(),
            'status'          => 'RESOLVED',
        ]);

        TicketActivity::create([
            'ticket_id'       => $ticket->id,
            'user_id'         => auth()->id(),
            'type'            => 'resolution',
            'message'         => $request->resolution_description,
            'attachment_path' => $attachmentPath,
        ]);

        // Notification (optionnelle)
        // $this->notifyUsers(...)

        return response()->json(['message' => 'Ticket résolu avec succès', 'ticket' => $ticket->fresh()]);
    }

    // =========================================================
    // HOLD (identique au web)
    // =========================================================
    public function hold(Request $request, Ticket $ticket)
    {
        $request->validate([
            'reason'     => 'required|string',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $actions = TicketActionService::allowedActions($ticket, auth()->user());
        if (!in_array('hold', $actions)) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ticket-holds', 'public');
        }

        $ticket->update([
            'status'        => 'ON_HOLD',
            'is_sla_paused' => true,
            'sla_paused_at' => now(),
        ]);

        TicketActivity::create([
            'ticket_id'       => $ticket->id,
            'user_id'         => auth()->id(),
            'type'            => 'hold',
            'message'         => $request->reason,
            'attachment_path' => $path,
        ]);

        return response()->json(['message' => 'Ticket mis en attente', 'ticket' => $ticket->fresh()]);
    }

    // =========================================================
    // RESUME (identique au web avec raison et pièce jointe)
    // =========================================================
    public function resume(Request $request, Ticket $ticket)
    {
        $request->validate([
            'reason'     => 'required|string',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if (!$ticket->sla_paused_at) {
            return response()->json(['message' => 'Aucune pause SLA active'], 422);
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ticket-resumes', 'public');
        }

        $pauseDuration = now()->diffInSeconds($ticket->sla_paused_at);
        $newResolutionDueAt = $ticket->resolution_due_at->copy()->addSeconds($pauseDuration);

        $ticket->update([
            'status'               => 'IN_PROGRESS',
            'resolution_due_at'    => $newResolutionDueAt,
            'total_pause_duration' => $ticket->total_pause_duration + $pauseDuration,
            'is_sla_paused'        => false,
            'sla_paused_at'        => null,
        ]);

        TicketActivity::create([
            'ticket_id'       => $ticket->id,
            'user_id'         => auth()->id(),
            'type'            => 'resume',
            'message'         => $request->reason,
            'attachment_path' => $path,
        ]);

        return response()->json(['message' => 'Traitement repris avec succès', 'ticket' => $ticket->fresh()]);
    }

    // =========================================================
    // CLOSE (identique au web)
    // =========================================================
    public function close(Ticket $ticket)
    {
        $actions = TicketActionService::allowedActions($ticket, auth()->user());
        if (!in_array('close', $actions)) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $ticket->update([
            'status'    => 'CLOSED',
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        app(TicketNotificationService::class)->notifyClosed($ticket);

        return response()->json(['message' => 'Ticket clôturé', 'ticket' => $ticket->fresh()]);
    }

    // =========================================================
    // REOPEN (identique au web)
    // =========================================================
    public function reopen(Request $request, Ticket $ticket)
    {
        $request->validate([
            'reason'     => 'required|string',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $actions = TicketActionService::allowedActions($ticket, auth()->user());
        if (!in_array('reopen', $actions)) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ticket-reopens', 'public');
        }

        $ticket->update(['status' => 'REOPENED']);

        TicketActivity::create([
            'ticket_id'       => $ticket->id,
            'user_id'         => auth()->id(),
            'type'            => 'reopen',
            'message'         => $request->reason,
            'attachment_path' => $path,
        ]);

        app(TicketNotificationService::class)->notifyReopened($ticket);

        return response()->json(['message' => 'Ticket réouvert', 'ticket' => $ticket->fresh()]);
    }

    // =========================================================
    // TRANSFER (identique au web, retour JSON)
    // =========================================================
    public function transfer(Request $request, Ticket $ticket)
    {
        if (strtoupper(auth()->user()->role?->name) === 'TECHNICIAN') {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $request->validate([
            'target_type' => 'required|in:user,company',
            'user_id'     => [Rule::requiredIf($request->target_type === 'user'), 'exists:users,id'],
            'company_id'  => ['nullable', Rule::requiredIf($request->target_type === 'company'), 'exists:companies,id'],
            'reason'      => 'required|string|max:500',
        ]);

        $oldSupervisor = $ticket->assigned_to ? User::find($ticket->assigned_to) : null;

        if ($request->target_type === 'user') {
            $supervisor = User::findOrFail($request->user_id);
            if (strtoupper($supervisor->role->name) !== 'SUPERVISOR') {
                return response()->json(['message' => 'La cible doit être un superviseur'], 422);
            }

            if ($oldSupervisor) $ticket->addSupervisor($oldSupervisor);

            $ticket->update([
                'assigned_to' => $supervisor->id,
                'company_id'  => null,
                'status'      => 'TRANSFERRED',
                'unit_id'     => $supervisor->unit_id,
            ]);
            $ticket->addSupervisor($supervisor);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id'   => auth()->id(),
                'type'      => 'transfer',
                'message'   => "à {$supervisor->name}",
            ]);
        } else {
            $company = Company::findOrFail($request->company_id);
            $supervisor = User::findOrFail($request->user_id);

            if ($supervisor->company_id != $company->id) {
                return response()->json(['message' => 'Cet utilisateur n\'appartient pas à cette entreprise.'], 422);
            }

            if ($oldSupervisor) $ticket->addSupervisor($oldSupervisor);

            $path1 = $request->file('attachment1')->store('transfers', 'public');
            $path2 = $request->file('attachment2')->store('transfers', 'public');

            $ticket->update([
                'assigned_to' => $supervisor->id,
                'company_id'  => $company->id,
                'status'      => 'TRANSFERRED',
            ]);
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

        return response()->json(['message' => 'Ticket transféré avec succès', 'ticket' => $ticket->fresh()]);
    }

    // =========================================================
    // FILTER SUPERVISORS (ajouté)
    // =========================================================
    public function filterSupervisors(Request $request)
    {
        $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;

        $supervisors = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))
            ->where('unit_id', $request->unit_id)
            ->where('agency_id', $request->agency_id)
            ->where('company_id', $eneoCompanyId)
            ->orderBy('name')
            ->get();

        return response()->json($supervisors);
    }

    // =========================================================
    // CHANGE STATUS
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
<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Sla;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\Unit;
use App\Models\User;
use App\Models\TicketComment;
use App\Models\TicketActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\TicketActionService;

class TicketController extends Controller
{
    /**
     * Liste des tickets
     */
    public function index()
{
    $user = auth()->user();

    // sécuriser relation role
    $user->load('role');
    $role = strtoupper($user->role?->name);

    $query = Ticket::with(['type', 'unit']);

    $tickets = match ($role) {

        'MANAGER', 'CUSTOMER_SERVICE' => $query->latest()->get(),

        'SUPERVISOR' => $query->where('assigned_to', $user->id)
            ->latest()
            ->get(),

        'TECHNICIAN' => $query->whereHas('technicians', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->latest()->get(),

        default => collect()
    };

    return view('tickets.index', compact('tickets'));
}

    /**
     * Formulaire création ticket
     */
    public function create()
{
    $user = auth()->user();
    $role = strtolower($user->role?->name ?? '');

if (!in_array($role, ['manager', 'customer_service', 'supervisor'])) {
    abort(403);
}

    $units = Unit::all();
    $types = Type::all();
    $agencies = Agency::all();

    $users = User::whereHas('role', function ($q) {
        $q->where('name', 'SUPERVISOR');
    })->orderBy('name')->get();

    return view('tickets.create', compact(
        'units',
        'types',
        'agencies',
        'users'
    ));
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
        $data = $request->validate([
            'unit_id' => 'required',
            'type_id' => 'required',
            'agency_id' => 'required',
            'assigned_to' => 'nullable',
            'description' => 'required',
            'priority' => 'required',
            'contract_number' => 'nullable',
            'attachment_path' => 'nullable|file',
        ]);

        /**
         * Upload fichier
         */
        if ($request->hasFile('attachment_path')) {
            $data['attachment_path'] = $request
                ->file('attachment_path')
                ->store('tickets', 'public');
        }

        /**
         * SLA automatique selon priorité
         */
        $sla = Sla::where('priority', $data['priority'])
            ->where('is_active', 1)
            ->first();

        /**
         * Deadline de résolution
         */
        $resolutionDeadline = null;

        if ($sla) {
            $resolutionDeadline = Carbon::now()
                ->addMinutes($sla->resolution_time);
        }

        /**
         * Création ticket
         */
        Ticket::create([
            'unit_id' => $data['unit_id'],
            'type_id' => $data['type_id'],
            'agency_id' => $data['agency_id'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'description' => $data['description'],
            'priority' => $data['priority'],
            'contract_number' => $data['contract_number'] ?? null,
            'attachment_path' => $data['attachment_path'] ?? null,

            'status' => 'OPEN',

            // SLA
            'sla_id' => $sla?->id,
            'resolution_deadline' => $resolutionDeadline,
        ]);

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
    'user_id' => 'required|exists:users,id'
]);

$isSupervisor = User::where('id', $request->user_id)
    ->whereHas('role', function ($q) {
        $q->where('name', 'SUPERVISOR');
    })
    ->exists();

if (!$isSupervisor) {
    abort(403, 'Vous ne pouvez transférer qu’à un superviseur');
}
    $ticket->update([
        'assigned_to' => $request->user_id,
        'status' => 'TRANSFERRED'
    ]);

    $supervisor = User::findOrFail($request->user_id);

TicketActivity::create([
    'ticket_id' => $ticket->id,
    'user_id' => auth()->id(),
    'type' => 'transfer',
    'message' => 'Ticket transféré',
]);

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
    $ticket->load([
        'comments.user',
        'documents.uploader',
        'user',
        'technicians',
    ]);

    $technicians = User::whereHas('role', fn($q) =>
        $q->where('name', 'TECHNICIAN')
    )->get();

    $supervisors = User::whereHas('role', fn($q) =>
        $q->where('name', 'SUPERVISOR')
    )->get();

    $users = User::orderBy('name')->get();

    // ACTIVITIES
    $activities = collect();

    // 1. Ticket activities
    foreach ($ticket->activities as $activity) {
        $activities->push([
            'type' => $activity->type,
            'date' => $activity->created_at,
            'data' => $activity
        ]);
    }

    // 2. Comments
    foreach ($ticket->comments as $comment) {
        $activities->push([
            'type' => 'comment',
            'date' => $comment->created_at,
            'data' => $comment
        ]);
    }

    // 3. Documents (ticketDocument)
    foreach ($ticket->documents as $document) {
        $activities->push([
            'type' => 'document',
            'date' => $document->created_at,
            'data' => $document
        ]);
    }

    $activities = $activities
        ->sortBy('date')
        ->values();

    $actions = TicketActionService::allowedActions(
    $ticket,
    auth()->user()
);
    $ticket->checkSla();
    return view('tickets.show', compact(
        'ticket',
        'activities',
        'technicians',
        'users',
        'actions',
        'supervisors'
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
}
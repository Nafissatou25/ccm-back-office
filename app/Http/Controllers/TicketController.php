<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\Unit;
use App\Models\Company;
use App\Models\Client;
use App\Models\User;
use App\Models\TicketActivity;
use App\Models\SlaRule;
use App\Models\TicketView;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\TicketActionService;
use App\Services\SlaService;
use Illuminate\Support\Facades\Http;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $user->load('role');
        $role = strtoupper($user->role?->name);

        $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;
        $canCreateTicket = false;

        if (in_array($role, ['ADMIN', 'MANAGER', 'CUSTOMER_SERVICE'])) {
            $canCreateTicket = true;
        } elseif ($role === 'SUPERVISOR' && $user->company_id == $eneoCompanyId) {
            $canCreateTicket = true;
        }

        $startDate      = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate        = $request->filled('end_date')   ? Carbon::parse($request->end_date)->endOfDay()     : null;
        $selectedUnitId = $request->filled('unit_id')    ? (int) $request->unit_id : null;
        $selectedTypeId = $request->filled('type_id')    ? (int) $request->type_id : null;

        $query = Ticket::with(['type', 'unit', 'views' => function ($q) {
        $q->where('user_id', auth()->id());
    }]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('late')) {
            $query->whereNotIn('status', ['RESOLVED', 'CLOSED'])
                  ->where('resolution_due_at', '<', now());
        }

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

        if ($selectedUnitId && !in_array($role, ['MANAGER', 'ADMIN', 'CUSTOMER_SERVICE'])) {
            $query->where('unit_id', $selectedUnitId);
        }
        if ($selectedTypeId)  $query->where('type_id', $selectedTypeId);
        if ($startDate)       $query->where('created_at', '>=', $startDate);
        if ($endDate)         $query->where('created_at', '<=', $endDate);

        $tickets = $query->latest()->get();

        $units = in_array($role, ['ADMIN', 'MANAGER', 'CUSTOMER_SERVICE'])
            ? Unit::orderBy('name')->get()
            : Unit::where('id', $selectedUnitId)->get();

        $eligibleTypes = $selectedUnitId
            ? Type::where('unit_id', $selectedUnitId)->orderBy('name')->get()
            : Type::orderBy('name')->get();

        $statusColors = [
            'OPEN'        => 'warning',
            'IN_PROGRESS' => 'primary',
            'ON_HOLD'     => 'secondary',
            'RESOLVED'    => 'success',
            'CLOSED'      => 'dark',
            'TRANSFERRED' => 'secondary',
            'REOPENED'    => 'danger',
        ];

        $stats = [
            'total'       => (clone $query)->count(),
            'open'        => (clone $query)->where('status', 'OPEN')->count(),
            'inProgress'  => (clone $query)->where('status', 'IN_PROGRESS')->count(),
            'transferred' => (clone $query)->where('status', 'TRANSFERRED')->count(),
            'onHold'      => (clone $query)->where('status', 'ON_HOLD')->count(),
            'reopened'    => (clone $query)->where('status', 'REOPENED')->count(),
            'resolved'    => (clone $query)->where('status', 'RESOLVED')->count(),
            'closed'      => (clone $query)->where('status', 'CLOSED')->count(),
            'late'        => (clone $query)
                ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
                ->where('resolution_due_at', '<', now())
                ->count(),
        ];

        return view('tickets.index', compact(
            'tickets', 'statusColors', 'units', 'eligibleTypes',
            'selectedUnitId', 'selectedTypeId', 'startDate', 'endDate',
            'role', 'stats', 'canCreateTicket'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $role = strtolower($user->role?->name ?? '');

        if (!in_array($role, ['manager', 'customer_service', 'supervisor', 'admin'])) {
            abort(403);
        }

        $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;

        if ($role === 'supervisor' && $user->company_id != $eneoCompanyId) {
            abort(403, 'Seuls les superviseurs ENEO peuvent créer des tickets.');
        }

        $units    = Unit::orderBy('name')->get();
        $types    = Type::orderBy('name')->get();
        $agencies = Agency::orderBy('name')->get();

        $users = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))
            ->with(['agency', 'unit', 'company'])
            ->orderBy('name')
            ->get();

        return view('tickets.create', compact('units', 'types', 'agencies', 'users', 'eneoCompanyId'));
    }

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
            'contract_number'        => 'nullable|string',
            'attachment_path'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'client_contract_number' => 'nullable|string|max:50',
            'client_name'            => 'required|string|max:255',
            'client_firstname'       => 'nullable|string|max:255',
            'client_phone'           => 'required|string|max:20',
            'client_whatsapp'        => 'nullable|string|max:20',
            'client_delivery_point'  => 'nullable|string',
        ]);

        // Client
        $client = Client::where('contract_number', $data['client_contract_number'])
                        ->orWhere('phone', $data['client_phone'])
                        ->first();

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

        // Pièce jointe
        $attachmentPath = $request->file('attachment_path')
            ->store('tickets', 'public');

        // Urgence
        $isUrgent = $request->boolean('is_urgent');

        // Création du ticket
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
            'created_by'      => auth()->id(),
        ]);

        // Appliquer le SLA
        SlaService::applySla($ticket);
        $ticket->save();

        // Superviseur assigné
        if ($ticket->assigned_to) {
            $supervisor = User::find($ticket->assigned_to);
            if ($supervisor) {
                $ticket->addSupervisor($supervisor);
            }
        }

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket créé avec succès');
    }

    public function quickStore(Request $request)
    {
        $user          = auth()->user();
        $role          = strtolower($user->role?->name ?? '');
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

        // Vérifier unicité
        $exists = Type::where('unit_id', $request->unit_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ce type existe déjà pour cette unité.',
            ], 422);
        }

        $type = Type::create([
            'unit_id' => $request->unit_id,
            'name'    => $request->name,
        ]);

        // Info SLA par défaut de l'unité
        $slaDefault = SlaRule::where('unit_id', $request->unit_id)
            ->whereNull('type_id')
            ->where('is_active', true)
            ->get()
            ->keyBy('is_urgent');

        return response()->json([
            'success'  => true,
            'type'     => $type,
            'sla_info' => [
                'normal'      => $slaDefault->get(0) ? ['tto' => $slaDefault->get(0)->tto, 'ttr' => $slaDefault->get(0)->ttr] : null,
                'urgent'      => $slaDefault->get(1) ? ['tto' => $slaDefault->get(1)->tto, 'ttr' => $slaDefault->get(1)->ttr] : null,
                'has_default' => $slaDefault->isNotEmpty(),
            ],
        ]);
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['comments.user', 'documents.uploader', 'user', 'technicians', 'activities']);

        $user          = auth()->user();
        $role          = strtoupper($user->role?->name);
        $eneoCompanyId = Company::where('name', 'ENEO')->first()?->id;

        $supervisors        = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))->with(['agency', 'unit'])->get();
        $supervisorsByCompany = $supervisors->groupBy('company_id');
        $users              = User::orderBy('name')->get();

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

        $activities = collect();
        foreach ($ticket->activities as $a) {
            $activities->push(['type' => $a->type,      'date' => $a->created_at,  'data' => $a]);
        }
        foreach ($ticket->comments as $c) {
            $activities->push(['type' => 'comment',     'date' => $c->created_at,  'data' => $c]);
        }
        foreach ($ticket->documents as $d) {
            $activities->push(['type' => 'document',    'date' => $d->created_at,  'data' => $d]);
        }
        $activities = $activities->sortBy('date')->values();

        $units     = Unit::all();
        $agencies  = Agency::all();
        $companies = Company::all();
        $actions   = TicketActionService::allowedActions($ticket, $user);
        $ticket->checkSla();

        TicketView::firstOrCreate(
    [
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
    ],
    [
        'viewed_at' => now(),
    ]
);

        return view('tickets.show', compact(
            'ticket', 'activities', 'technicians', 'users', 'actions',
            'supervisors', 'supervisorsByCompany', 'units', 'agencies', 'companies'
        ));
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate(['technicians' => 'required|array']);

        $ticket->technicians()->sync($request->technicians);
        $ticket->update(['status' => 'IN_PROGRESS']);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'type'      => 'assignment',
            'message'   => 'Techniciens assignés',
        ]);

        return back()->with('success', 'Techniciens assignés avec succès');
    }

    public function start(Ticket $ticket)
    {
        $ticket->update([
            'status'     => 'IN_PROGRESS',
            'started_at' => now(),
            'taken_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Ticket en cours de traitement');
    }

    public function resolve(Request $request, Ticket $ticket)
    {
        $request->validate([
            'resolution_description' => 'required|string',
            'resolution_attachment'  => 'nullable|file|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('resolution_attachment')) {
            $attachmentPath = $request->file('resolution_attachment')
                ->store('resolutions', 'public');
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

        $this->notifyUsers($ticket, "Ticket #{$ticket->id} résolu par " . auth()->user()->name, 'résolution');

        return back()->with('success', 'Ticket résolu avec succès');
    }

    public function hold(Request $request, Ticket $ticket)
    {
        $request->validate([
            'reason'     => 'required|string|min:5',
            'attachment' => 'nullable|file|max:5120',
        ]);

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

        return back()->with('success', 'Ticket mis en attente');
    }

    public function resume(Ticket $ticket)
    {
        if (!$ticket->sla_paused_at) {
            return back()->with('error', 'Aucune pause SLA active');
        }

        $pauseDuration      = now()->diffInSeconds($ticket->sla_paused_at);
        $newResolutionDueAt = $ticket->resolution_due_at->copy()->addSeconds($pauseDuration);

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

        return back()->with('success', 'Traitement repris');
    }

    public function close(Ticket $ticket)
    {
        $ticket->update([
            'status'    => 'CLOSED',
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Ticket clôturé');
    }

    public function reopen(Request $request, Ticket $ticket)
    {
        $request->validate([
            'reason'     => 'required|string|min:5',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if (!in_array(strtoupper(auth()->user()->role?->name), ['CUSTOMER_SERVICE', 'SUPERVISOR'])) {
            abort(403);
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

        return back()->with('success', 'Ticket réouvert');
    }

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

    public function assignments()
    {
        $tickets = Ticket::with(['unit', 'type', 'agency'])
            ->whereNull('taken_by')
            ->latest()
            ->get();

        $technicians = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))->get();

        return view('tickets.assignments', compact('tickets', 'technicians'));
    }

    // ── Privées ───────────────────────────────────────────────

    private function notifyUsers(Ticket $ticket, string $message, string $type): void
    {
        $userIds = collect();

        if ($ticket->created_by) $userIds->push($ticket->created_by);
        if ($ticket->assigned_to) $userIds->push($ticket->assigned_to);
        foreach ($ticket->technicians as $tech) $userIds->push($tech->id);

        foreach ($userIds->unique() as $uid) {
            $user = User::find($uid);
            if ($user?->onesignal_player_id) {
                $this->sendOneSignal($user->onesignal_player_id, 'Ticket mis à jour', $message, [
                    'ticket_id' => $ticket->id,
                    'type'      => $type,
                ]);
            }
        }
    }

    private function sendOneSignal(string $playerId, string $title, string $message, array $data = []): void
    {
        $response = Http::withOptions(['verify' => 'C:/wamp64/bin/php/cacert.pem'])
            ->withHeaders([
                'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
                'Content-Type'  => 'application/json',
            ])
            ->post('https://onesignal.com/api/v1/notifications', [
                'app_id'             => env('ONESIGNAL_APP_ID'),
                'include_player_ids' => [$playerId],
                'headings'           => ['en' => $title],
                'contents'           => ['en' => $message],
                'data'               => $data,
            ]);

        \Log::info('ONESIGNAL', ['status' => $response->status(), 'body' => $response->json()]);
    }
}
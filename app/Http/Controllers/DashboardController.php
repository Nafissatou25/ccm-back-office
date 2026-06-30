<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Unit;
use App\Models\Type;
use App\Models\Agency;
use App\Models\User; 
use App\Models\SlaRule;
use App\Models\WhatsappRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Exports\TicketsExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role?->name ?? '');

        // ── Statistiques admin (toujours présentes) ────────────────
        $usersCount   = User::count();
        $unitsCount   = Unit::count();
        $agenciesCount = Agency::count();
        $typesCount   = Type::count();
        $slaCount     = SlaRule::where('is_active', true)->count();
        $activeSlaCount = SlaRule::where('is_active', true)->count();

        // ── Vérification des droits ─────────────────────────────────
        if (!in_array($role, ['manager', 'admin', 'customer_service', 'supervisor'])) {
            abort(403, 'Accès non autorisé.');
        }

        // ── Période ──────────────────────────────────────────────────
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonths(5)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // ── Filtres ──────────────────────────────────────────────────
        $selectedUnitId = (in_array($role, ['admin', 'manager']) && $request->filled('unit_id'))
            ? (int) $request->unit_id
            : null;

        $selectedAgencyId = (in_array($role, ['admin', 'manager']) && $request->filled('agency_id'))
            ? (int) $request->agency_id
            : null;

        // ── Requête de base (filtres : période, rôle) ──────────────
        $query = Ticket::whereBetween('tickets.created_at', [$startDate, $endDate]);

        // Restriction SUPERVISOR : tickets assignés ou dont il est superviseur
        if ($role === 'supervisor') {
            $query->where(function ($q) use ($user) {
                $q->where('tickets.assigned_to', $user->id)
                  ->orWhereHas('supervisors', fn($sq) => $sq->where('user_id', $user->id));
            });
        }

        // Filtres unité et agence (admin/manager)
        if ($selectedUnitId) {
            $query->where('tickets.unit_id', $selectedUnitId);
        }
        if ($selectedAgencyId) {
            $query->where('tickets.agency_id', $selectedAgencyId);
        }

        // ── Récupérer les IDs des tickets accessibles ──────────────
        $accessibleTicketIds = (clone $query)->pluck('tickets.id')->toArray();

        // ── Compteurs statuts ───────────────────────────────────────
        $totalTickets       = (clone $query)->count();
        $openTickets        = (clone $query)->where('status', 'OPEN')->count();
        $inProgressTickets  = (clone $query)->where('status', 'IN_PROGRESS')->count();
        $transferredTickets = (clone $query)->where('status', 'TRANSFERRED')->count();
        $onHoldTickets      = (clone $query)->where('status', 'ON_HOLD')->count();
        $reopenedTickets    = (clone $query)->where('status', 'REOPENED')->count();
        $resolvedTickets    = (clone $query)->where('status', 'RESOLVED')->count();
        $closedTickets      = (clone $query)->where('status', 'CLOSED')->count();
        $lateTickets        = (clone $query)
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where('resolution_due_at', '<', now())
            ->count();

        // ── KPI ─────────────────────────────────────────────────────
        $urgentTickets = (clone $query)->where('is_urgent', true)->count();

        // Temps moyen de résolution (TTR) – déjà présent
        $avgResolutionHours = (clone $query)
            ->whereNotNull('resolved_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, resolved_at)), 1) as avg_hours')
            ->value('avg_hours') ?? 0;

        // Temps moyen de prise en charge (TTO) – depuis created_at jusqu'à started_at
        $avgTTO = (clone $query)
            ->whereNotNull('started_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, started_at)), 1) as avg_hours')
            ->value('avg_hours') ?? 0;

        // Temps moyen d'attente (somme des pauses / nombre de tickets ayant eu une pause)
        $avgHoldTime = (clone $query)
            ->where('total_pause_duration', '>', 0)
            ->selectRaw('ROUND(AVG(total_pause_duration / 3600), 1) as avg_hours') // secondes en heures
            ->value('avg_hours') ?? 0;

        // Nombre de techniciens (pour productivité)
        $techniciansCount = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'))->count();

        // Taux
        $lateRate = $totalTickets > 0 ? round(($lateTickets / $totalTickets) * 100, 1) : 0;
        $resolutionRate = $totalTickets > 0 ? round((($resolvedTickets + $closedTickets) / $totalTickets) * 100, 1) : 0;
        $closureRate = $totalTickets > 0 ? round(($closedTickets / $totalTickets) * 100, 1) : 0;
        $reopenRate = $totalTickets > 0 ? round(($reopenedTickets / $totalTickets) * 100, 1) : 0;
        $slaCompliance = $totalTickets > 0 ? round((($totalTickets - $lateTickets) / $totalTickets) * 100, 1) : 0;

        // Productivité globale (tickets résolus + clos / nombre de techniciens)
        $productivity = $techniciansCount > 0 ? round(($resolvedTickets + $closedTickets) / $techniciansCount, 1) : 0;

        $unassignedTickets = (clone $query)
            ->whereIn('status', ['OPEN', 'REOPENED'])
            ->whereDoesntHave('technicians')
            ->count();

        // ── Demandes WhatsApp en attente ────────────────────────────
        $pendingWhatsappRequests = WhatsappRequest::where('status', 'COMPLETED')->count();

        // ── Types les plus fréquents ────────────────────────────────
        $topTypes = (clone $query)
            ->join('types', 'tickets.type_id', '=', 'types.id')
            ->leftJoin('units', 'types.unit_id', '=', 'units.id')
            ->selectRaw('types.name, units.name as unit_name, COUNT(*) as count')
            ->groupBy('types.id', 'types.name', 'units.name')
            ->orderByDesc('count')
            ->limit(7)
            ->get();

        $topTypesLabels = $topTypes->pluck('name')->toArray();
        $topTypesData   = $topTypes->pluck('count')->toArray();

        // ── Tickets urgents non résolus par agence ──────────────────
        $urgentByAgency = (clone $query)
            ->whereNotIn('tickets.status', ['RESOLVED', 'CLOSED'])
            ->where('tickets.is_urgent', true)
            ->join('agencies', 'tickets.agency_id', '=', 'agencies.id')
            ->selectRaw('agencies.name, COUNT(*) as urgent_count')
            ->groupBy('agencies.id', 'agencies.name')
            ->orderByDesc('urgent_count')
            ->get();

        // ── Évaluation par unité ─────────────────────────────────────
        $units = Unit::orderBy('name')->get();

        $unitPerformance = Unit::withCount([
            'tickets as total_tickets' => fn($q) => $q->whereIn('tickets.id', $accessibleTicketIds),
            'tickets as resolved_tickets' => fn($q) => $q->whereIn('tickets.id', $accessibleTicketIds)->whereIn('status', ['RESOLVED', 'CLOSED']),
            'tickets as late_tickets' => fn($q) => $q->whereIn('tickets.id', $accessibleTicketIds)->whereNotIn('status', ['RESOLVED', 'CLOSED'])->where('resolution_due_at', '<', now()),
            'tickets as reopened_tickets' => fn($q) => $q->whereIn('tickets.id', $accessibleTicketIds)->where('status', 'REOPENED'),
        ])->having('total_tickets', '>', 0)->get();

        // ── Évolution mensuelle ──────────────────────────────────────
        $months = collect(range(11, 0))->map(fn($i) => Carbon::now()->subMonths($i));

        $monthlyLabels = $months->map(fn($m) => $m->format('M Y'))->toArray();

        $monthlyCreated = $months->map(function ($m) use ($query) {
            return (clone $query)
                ->whereYear('tickets.created_at', $m->year)
                ->whereMonth('tickets.created_at', $m->month)
                ->count();
        })->toArray();

        $monthlyResolved = $months->map(function ($m) use ($query) {
            return (clone $query)
                ->whereYear('tickets.resolved_at', $m->year)
                ->whereMonth('tickets.resolved_at', $m->month)
                ->whereNotNull('tickets.resolved_at')
                ->count();
        })->toArray();

        $monthlyLate = $months->map(function ($m) use ($query) {
            return (clone $query)
                ->whereYear('tickets.created_at', $m->year)
                ->whereMonth('tickets.created_at', $m->month)
                ->whereNotIn('tickets.status', ['RESOLVED', 'CLOSED'])
                ->where('tickets.resolution_due_at', '<', now())
                ->count();
        })->toArray();

        // ── Liste des unités et agences pour les filtres ────────────
        $units = Unit::orderBy('name')->get();
        $agencies = Agency::orderBy('name')->get();

        // ── Performance par technicien ──────────────────────────────

        // ── Déterminer l'unité à filtrer ──────────────────────────────
$filteredUnitId = null;
if ($role === 'supervisor') {
    // Récupérer l'unité du superviseur (via la relation définie dans User)
    $supervisorUnit = $user->unit; // ou $user->unite selon votre modèle
    $filteredUnitId = $supervisorUnit ? $supervisorUnit->id : null;
} elseif (in_array($role, ['admin', 'manager']) && $selectedUnitId) {
    $filteredUnitId = $selectedUnitId;
}
    // ── Performance par technicien (filtrée par unité) ──────────────
$technicianQuery = User::whereHas('role', fn($q) => $q->where('name', 'TECHNICIAN'));

// Si un filtre unité est défini, on ne garde que les techniciens de cette unité
if ($filteredUnitId) {
    $technicianQuery->where('unit_id', $filteredUnitId); // ou 'unite_id'
}

$technicianPerformance = $technicianQuery
    ->withCount([
        'tickets as resolved_count' => function ($q) use ($startDate, $endDate, $filteredUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate])
              ->whereIn('status', ['RESOLVED', 'CLOSED']);
            if ($filteredUnitId) {
                $q->where('tickets.unit_id', $filteredUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
        'tickets as reopened_count' => function ($q) use ($startDate, $endDate, $filteredUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate])
              ->where('status', 'REOPENED');
            if ($filteredUnitId) {
                $q->where('tickets.unit_id', $filteredUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
        'tickets as assigned_count' => function ($q) use ($startDate, $endDate, $filteredUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate]);
            if ($filteredUnitId) {
                $q->where('tickets.unit_id', $filteredUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
    ])
    ->withAvg([
        'tickets as avg_resolution_time' => function ($q) use ($startDate, $endDate, $filteredUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate])
              ->whereNotNull('resolved_at');
            if ($filteredUnitId) {
                $q->where('tickets.unit_id', $filteredUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        }
    ], DB::raw('TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.resolved_at)'))
    ->having('assigned_count', '>', 0)
    ->orderByDesc('resolved_count')
    ->limit(10)
    ->get();
    
    // Préparer les données pour le graphique
    $technicianNames = $technicianPerformance->pluck('name')->toArray();
    $technicianResolved = $technicianPerformance->pluck('resolved_count')->toArray();
    $technicianReopened = $technicianPerformance->pluck('reopened_count')->toArray();
    $technicianAvgResolution = $technicianPerformance->pluck('avg_resolution_time')->map(fn($val) => round($val, 1))->toArray();

    // ── Performance des superviseurs ────────────────────────────────
$supervisorPerformance = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))
    ->withCount([
        'tickets as assigned_count' => function ($q) use ($startDate, $endDate, $selectedUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate]);
            if ($selectedUnitId) {
                $q->where('tickets.unit_id', $selectedUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
        'tickets as resolved_count' => function ($q) use ($startDate, $endDate, $selectedUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate])
              ->whereIn('status', ['RESOLVED', 'CLOSED']);
            if ($selectedUnitId) {
                $q->where('tickets.unit_id', $selectedUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
        'tickets as late_count' => function ($q) use ($startDate, $endDate, $selectedUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate])
              ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
              ->where('resolution_due_at', '<', now());
            if ($selectedUnitId) {
                $q->where('tickets.unit_id', $selectedUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
        'tickets as reopened_count' => function ($q) use ($startDate, $endDate, $selectedUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate])
              ->where('status', 'REOPENED');
            if ($selectedUnitId) {
                $q->where('tickets.unit_id', $selectedUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
    ])
    ->having('assigned_count', '>', 0)
    ->orderByDesc('resolved_count')
    ->limit(10)
    ->get();

// ── Performance des agents d'accueil (Customer Service) ────────
$customerServicePerformance = User::whereHas('role', fn($q) => $q->where('name', 'CUSTOMER_SERVICE'))
    ->withCount([
        'tickets as created_count' => function ($q) use ($startDate, $endDate, $selectedUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate]);
            if ($selectedUnitId) {
                $q->where('tickets.unit_id', $selectedUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
        'tickets as followed_count' => function ($q) use ($startDate, $endDate, $selectedUnitId, $selectedAgencyId) {
            $q->whereBetween('tickets.created_at', [$startDate, $endDate])
              ->whereHas('activities', function ($sq) {
                  $sq->where('type', 'comment')
                     ->orWhere('type', 'resolution')
                     ->orWhere('type', 'close');
              });
            if ($selectedUnitId) {
                $q->where('tickets.unit_id', $selectedUnitId);
            }
            if ($selectedAgencyId) {
                $q->where('tickets.agency_id', $selectedAgencyId);
            }
        },
    ])
    ->having('created_count', '>', 0)
    ->orderByDesc('created_count')
    ->limit(10)
    ->get();

// Préparer les variables pour la vue
$supervisorNames = $supervisorPerformance->pluck('name')->toArray();
$supervisorResolved = $supervisorPerformance->pluck('resolved_count')->toArray();
$supervisorTotal = $supervisorPerformance->pluck('assigned_count')->toArray();
$supervisorLate = $supervisorPerformance->pluck('late_count')->toArray();

$csNames = $customerServicePerformance->pluck('name')->toArray();
$csCreated = $customerServicePerformance->pluck('created_count')->toArray();
$csFollowed = $customerServicePerformance->pluck('followed_count')->toArray();

        // ── Retour à la vue ─────────────────────────────────────────
        return view('dashboard', compact(
            'startDate', 'endDate', 'role', 'units', 'agencies',
            'selectedUnitId', 'selectedAgencyId',
            'totalTickets', 'openTickets', 'inProgressTickets', 'transferredTickets',
            'onHoldTickets', 'reopenedTickets', 'resolvedTickets', 'closedTickets',
            'lateTickets', 'urgentTickets', 'avgResolutionHours', 'unassignedTickets',
            'urgentByAgency', 'unitPerformance',
            'monthlyLabels', 'monthlyCreated', 'monthlyResolved', 'monthlyLate',
            'usersCount', 'unitsCount', 'agenciesCount', 'typesCount',
            'slaCount', 'activeSlaCount', 'pendingWhatsappRequests',
            'topTypes', 'topTypesLabels', 'topTypesData',
            'avgTTO', 'avgHoldTime', 'lateRate', 'resolutionRate',
            'closureRate', 'reopenRate', 'slaCompliance', 'productivity',
            'techniciansCount', 'technicianPerformance',
        'technicianNames',
        'technicianResolved',
        'technicianReopened',
        'technicianAvgResolution', 'supervisorPerformance', 'supervisorNames', 'supervisorResolved', 'supervisorTotal', 'supervisorLate',
    'customerServicePerformance', 'csNames', 'csCreated', 'csFollowed'
        ));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->role?->name ?? '');

        if (!in_array($role, ['manager', 'admin', 'customer_service', 'supervisor'])) {
            abort(403);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonths(5)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $selectedUnitId = (in_array($role, ['admin', 'manager']) && $request->filled('unit_id'))
            ? (int) $request->unit_id
            : null;
        $selectedAgencyId = (in_array($role, ['admin', 'manager']) && $request->filled('agency_id'))
            ? (int) $request->agency_id
            : null;

        $query = Ticket::with(['unit', 'type', 'agency', 'client'])
            ->whereBetween('tickets.created_at', [$startDate, $endDate]);

        if ($role === 'supervisor') {
            $query->where(function ($q) use ($user) {
                $q->where('tickets.assigned_to', $user->id)
                  ->orWhereHas('supervisors', fn($sq) => $sq->where('user_id', $user->id));
            });
        }
        if ($selectedUnitId) {
            $query->where('tickets.unit_id', $selectedUnitId);
        }
        if ($selectedAgencyId) {
            $query->where('tickets.agency_id', $selectedAgencyId);
        }

        $tickets = $query->latest()->get();

        $export = new TicketsExport($tickets, $startDate, $endDate, $selectedUnitId);

        return Excel::download($export, 'tickets_' . now()->format('Y-m-d_H-i') . '.xlsx');
    }
}
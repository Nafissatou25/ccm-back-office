<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Unit;
use App\Models\Type;
use App\Models\Agency;
use App\Models\User; 
use App\Models\SlaRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role?->name ?? '');
        $usersCount   = User::count();
$unitsCount   = Unit::count();
$agenciesCount = Agency::count();
$typesCount   = Type::count();
$slaCount     = \App\Models\SlaRule::where('is_active', true)->count();
$activeSlaCount = SlaRule::where('is_active', true)->count(); // actives seulement

        if (!in_array($role, ['manager', 'admin', 'customer_service', 'supervisor'])) {
            abort(403, 'Accès non autorisé.');
        }

        // ── Période ──────────────────────────────────────────────
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonths(5)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // ── Filtres ───────────────────────────────────────────────
        $selectedUnitId = (in_array($role, ['admin', 'manager']) && $request->filled('unit_id'))
            ? (int) $request->unit_id
            : null;

        // ── Requête de base ───────────────────────────────────────
        $query = Ticket::whereBetween('tickets.created_at', [$startDate, $endDate]);

        if ($role === 'supervisor') {
            $query->where('unit_id', $user->unit_id);
        }
        if ($selectedUnitId) {
    $query->where('tickets.unit_id', $selectedUnitId);
}

        // ── Compteurs statuts ─────────────────────────────────────
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

        // ── KPI ───────────────────────────────────────────────────
        $urgentTickets = (clone $query)->where('is_urgent', true)->count();

        $avgResolutionHours = (clone $query)
            ->whereNotNull('resolved_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, resolved_at)), 1) as avg_hours')
            ->value('avg_hours') ?? 0;

        $unassignedTickets = (clone $query)
            ->whereIn('status', ['OPEN', 'REOPENED'])
            ->whereDoesntHave('technicians')
            ->count();

        // ── Types les plus fréquents (top 7) ─────────────────────
        // ← correction : tickets.created_at qualifié pour éviter l'ambiguïté
        $topTypes = (clone $query)
            ->join('types', 'tickets.type_id', '=', 'types.id')
            ->leftJoin('units', 'types.unit_id', '=', 'units.id')
            ->selectRaw('types.name, units.name as unit_name, COUNT(*) as count')
            ->groupBy('types.id', 'types.name', 'units.name')
            ->orderByDesc('count')
            ->limit(7)
            ->get();

        // ── Tickets urgents non résolus par agence ────────────────
        $urgentByAgency = Ticket::whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where('is_urgent', true)
            ->join('agencies', 'tickets.agency_id', '=', 'agencies.id')
            ->selectRaw('agencies.name, COUNT(*) as urgent_count')
            ->groupBy('agencies.id', 'agencies.name')
            ->orderByDesc('urgent_count')
            ->get();

        // ── Évaluation par unité ──────────────────────────────────
        $unitPerformance = Unit::withCount([
            'tickets as total_tickets' => fn($q) => $q
                ->whereBetween('tickets.created_at', [$startDate, $endDate]),
            'tickets as resolved_tickets' => fn($q) => $q
                ->whereBetween('tickets.created_at', [$startDate, $endDate])
                ->whereIn('status', ['RESOLVED', 'CLOSED']),
            'tickets as late_tickets' => fn($q) => $q
                ->whereBetween('tickets.created_at', [$startDate, $endDate])
                ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
                ->where('resolution_due_at', '<', now()),
            'tickets as reopened_tickets' => fn($q) => $q
                ->whereBetween('tickets.created_at', [$startDate, $endDate])
                ->where('status', 'REOPENED'),
        ])->having('total_tickets', '>', 0)->get();

        // ── Évolution mensuelle (12 derniers mois) ────────────────
        $months = collect(range(11, 0))->map(fn($i) => Carbon::now()->subMonths($i));

        $monthlyLabels = $months->map(fn($m) => $m->format('M Y'))->toArray();

        $monthlyCreated = $months->map(fn($m) => Ticket::whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)->count())->toArray();

        $monthlyResolved = $months->map(fn($m) => Ticket::whereYear('resolved_at', $m->year)
            ->whereMonth('resolved_at', $m->month)->whereNotNull('resolved_at')->count())->toArray();

        $monthlyLate = $months->map(fn($m) => Ticket::whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where('resolution_due_at', '<', now())->count())->toArray();

        // ── Données formulaire filtre ─────────────────────────────
        $units = Unit::orderBy('name')->get();

        return view('dashboard', compact(
            'startDate', 'endDate', 'role', 'units', 'selectedUnitId',
            'totalTickets', 'openTickets', 'inProgressTickets', 'transferredTickets',
            'onHoldTickets', 'reopenedTickets', 'resolvedTickets', 'closedTickets',
            'lateTickets', 'urgentTickets', 'avgResolutionHours', 'unassignedTickets',
            'topTypes', 'urgentByAgency', 'unitPerformance',
            'monthlyLabels', 'monthlyCreated', 'monthlyResolved', 'monthlyLate', 'usersCount', 'unitsCount', 'agenciesCount', 'typesCount', 'slaCount', 'activeSlaCount'
        ));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Unit;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role->name ?? 'client');

        if (!in_array($role, ['manager', 'admin', 'customer_service'])) {
        abort(403, 'Accès non autorisé à ce tableau de bord.');
    }

        // ----- Période -----
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfMonth();

        // ----- Filtre unité -----
        $selectedUnitId = $request->filled('unit_id') ? (int)$request->unit_id : null;

        // ----- Filtre type -----
       // ----- Filtre type : limiter aux types de l'unité sélectionnée -----
if ($role === 'manager') {
    $unitIdForTypes = $user->unit_id;
} else {
    $unitIdForTypes = $selectedUnitId;
}

if ($unitIdForTypes) {
    $eligibleTypes = Type::where('unit_id', $unitIdForTypes)->orderBy('name')->get();
} else {
    $eligibleTypes = Type::orderBy('name')->get();
}

$selectedTypeId = $request->filled('type_id') ? (int)$request->type_id : null;
if ($selectedTypeId && !$eligibleTypes->contains('id', $selectedTypeId)) {
    $selectedTypeId = null;
}



        // ----- Requête de base (période + restrictions par rôle) -----
        $baseQuery = Ticket::whereBetween('created_at', [$startDate, $endDate]);

        if ($role === 'technicien') {
            $baseQuery->where('assigned_to', $user->id);
       }  elseif ($role === 'client') {
            $baseQuery->where('created_by', $user->id);
        }

        // Appliquer le filtre unité (sauf pour manager déjà limité)
        if ($selectedUnitId && $role !== 'manager') {
            $baseQuery->where('unit_id', $selectedUnitId);
        }

        // Appliquer le filtre type
        if ($selectedTypeId) {
            $baseQuery->where('type_id', $selectedTypeId);
        }

        // ----- Cartes principales -----
        $totalTickets = (clone $baseQuery)->count();
        $openTickets = (clone $baseQuery)->where('status', 'OPEN')->count();
        $inProgressTickets = (clone $baseQuery)->where('status', 'IN_PROGRESS')->count();
        $transferredTickets = (clone $baseQuery)->where('status', 'TRANSFERRED')->count();
        $onHoldTickets = (clone $baseQuery)->where('status', 'ON_HOLD')->count();
        $reopenedTickets = (clone $baseQuery)->where('status', 'REOPENED')->count();
        $resolvedTickets = (clone $baseQuery)->where('status', 'RESOLVED')->count();
        $closedTickets = (clone $baseQuery)->where('status', 'CLOSED')->count();

        // ----- Tickets en retard (SLA dépassé et non clôturé/résolu) -----
        $lateTickets = (clone $baseQuery)
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where('resolution_due_at', '<', now())
            ->count();

        // ----- Tickets par priorité -----
        $priorityStats = [
            'LOW'      => (clone $baseQuery)->where('priority', 'LOW')->count(),
            'MEDIUM'   => (clone $baseQuery)->where('priority', 'MEDIUM')->count(),
            'HIGH'     => (clone $baseQuery)->where('priority', 'HIGH')->count(),
            'CRITICAL' => (clone $baseQuery)->where('priority', 'CRITICAL')->count(),
        ];

        // ----- Performance par unité (avec prise en compte du filtre type) -----
        $unitQuery = Ticket::whereBetween('created_at', [$startDate, $endDate]);
        if ($role === 'technicien') $unitQuery->where('assigned_to', $user->id);
        if ($role === 'manager') $unitQuery->where('unit_id', $user->unit_id);
        if ($role === 'client') $unitQuery->where('created_by', $user->id);
        if ($selectedTypeId) $unitQuery->where('type_id', $selectedTypeId);

        $unitPerformance = Unit::withCount([
            'tickets as total_tickets' => function ($q) use ($unitQuery) {
                $q->whereIn('tickets.id', $unitQuery->pluck('id'));
            },
            'tickets as resolved_tickets' => function ($q) use ($unitQuery) {
                $q->whereIn('tickets.id', (clone $unitQuery)->whereIn('status', ['RESOLVED', 'CLOSED'])->pluck('id'));
            },
            'tickets as late_tickets' => function ($q) use ($unitQuery) {
                $q->whereIn('tickets.id', (clone $unitQuery)->whereNotIn('status', ['RESOLVED', 'CLOSED'])->where('resolution_due_at', '<', now())->pluck('id'));
            },
            'tickets as reopened_tickets' => function ($q) use ($unitQuery) {
                $q->whereIn('tickets.id', (clone $unitQuery)->where('status', 'REOPENED')->pluck('id'));
            }
        ])->get();

        // ----- Tickets récents (10 derniers) -----
        $recentTickets = (clone $baseQuery)->latest()->take(10)->get();

        // ----- Liste des unités pour le filtre (selon les droits) -----
        $units = Unit::orderBy('name')->get();
        if ($role === 'manager') {
            $units = $units->filter(fn($u) => $u->id == $user->unit_id);
        }

        return view('dashboard', compact(
            'totalTickets',
            'openTickets',
            'inProgressTickets',
            'transferredTickets',
            'onHoldTickets',
            'reopenedTickets',
            'resolvedTickets',
            'closedTickets',
            'lateTickets',
            'priorityStats',
            'unitPerformance',
            'recentTickets',
            'startDate',
            'endDate',
            'selectedUnitId',
            'units',
            'role',
            'eligibleTypes',
            'selectedTypeId'
        ));
    }
}
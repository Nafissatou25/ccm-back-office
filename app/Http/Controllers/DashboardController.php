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

        // Redirection admin (si vous avez un dashboard admin séparé)
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
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

        // -----Filtre type ------
        $types = Type::orderBy('name')->get();
        $selectedTypeId = $request->filled('type_id') ? (int)$request->type_id : null;

        // ----- Requête de base (période + restrictions par rôle) -----
        $baseQuery = Ticket::whereBetween('created_at', [$startDate, $endDate]);

        if ($role === 'technicien') {
            $baseQuery->where('assigned_to', $user->id);
        } elseif ($role === 'manager') {
            $baseQuery->where('unit_id', $user->unit_id);
        } elseif ($role === 'client') {
            $baseQuery->where('created_by', $user->id);
        }

        // Appliquer le filtre unité (sauf pour manager, déjà limité)
        if ($selectedUnitId && $role !== 'manager') {
            $baseQuery->where('unit_id', $selectedUnitId);
        }

        if ($selectedTypeId) {
            $baseQuery->where('type_id', $selectedTypeId);
        }

        // ----- Cartes principales -----
        $totalTickets = (clone $baseQuery)->count();
        $openTickets = (clone $baseQuery)->where('status', 'OPEN')->count();
        $inProgressTickets = (clone $baseQuery)->where('status', 'IN_PROGRESS')->count();
        $resolvedTickets = (clone $baseQuery)->where('status', 'RESOLVED')->count();
        $closedTickets = (clone $baseQuery)->where('status', 'CLOSED')->count();
        $transferredTickets = (clone $baseQuery)->where('status', 'TRANSFERRED')->count();
        $onHoldTickets = (clone $baseQuery)->where('status', 'ON_HOLD')->count();

        // ----- Tickets en retard (SLA dépassé et non clos/résolu) -----
        $lateTickets = (clone $baseQuery)
            ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where('resolution_due_at', '<', now())
            ->count();

        // ----- Tickets réouverts -----
        $reopenedTickets = (clone $baseQuery)
            ->where('status', 'REOPENED')
            ->count();

        // ----- Tickets par priorité -----
        $priorityStats = [
            'LOW'      => (clone $baseQuery)->where('priority', 'LOW')->count(),
            'MEDIUM'   => (clone $baseQuery)->where('priority', 'MEDIUM')->count(),
            'HIGH'     => (clone $baseQuery)->where('priority', 'HIGH')->count(),
            'CRITICAL' => (clone $baseQuery)->where('priority', 'CRITICAL')->count(),
        ];

        // ----- Performance par unité (pour les graphiques et le tableau) -----
        $unitPerformance = Unit::withCount([
            'tickets as total_tickets' => function ($q) use ($startDate, $endDate, $role, $user) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
                if ($role === 'technicien') $q->where('assigned_to', $user->id);
                if ($role === 'manager') $q->where('unit_id', $user->unit_id);
                if ($role === 'client') $q->where('created_by', $user->id);
            },
            'tickets as resolved_tickets' => function ($q) use ($startDate, $endDate, $role, $user) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->whereIn('status', ['RESOLVED', 'CLOSED']);
                if ($role === 'technicien') $q->where('assigned_to', $user->id);
                if ($role === 'manager') $q->where('unit_id', $user->unit_id);
                if ($role === 'client') $q->where('created_by', $user->id);
            },
            'tickets as late_tickets' => function ($q) use ($startDate, $endDate, $role, $user) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->whereNotIn('status', ['RESOLVED', 'CLOSED'])
                  ->where('resolution_due_at', '<', now());
                if ($role === 'technicien') $q->where('assigned_to', $user->id);
                if ($role === 'manager') $q->where('unit_id', $user->unit_id);
                if ($role === 'client') $q->where('created_by', $user->id);
            },
            'tickets as reopened_tickets' => function ($q) use ($startDate, $endDate, $role, $user) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', 'REOPENED');
                if ($role === 'technicien') $q->where('assigned_to', $user->id);
                if ($role === 'manager') $q->where('unit_id', $user->unit_id);
                if ($role === 'client') $q->where('created_by', $user->id);
            }
        ])->get();

        // ----- Tickets récents (limite 10) -----
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
            'resolvedTickets',
            'closedTickets',
            'lateTickets',
            'reopenedTickets',
            'priorityStats',
            'unitPerformance',
            'recentTickets',
            'startDate',
            'endDate',
            'selectedUnitId',
            'units',
            'role',
            'transferredTickets',
            'onHoldTickets'
        ));
    }
}
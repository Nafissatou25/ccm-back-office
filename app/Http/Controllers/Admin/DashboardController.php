<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use App\Models\Agency;
use App\Models\Type;
use App\Models\SlaRule;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques de base
        $usersCount = User::count();
        $unitsCount = Unit::count();
        $agenciesCount = Agency::count();
        $typesCount = Type::count();
        $slaCount = SlaRule::count();
        $activeSlaCount = SlaRule::where('is_active', true)->count();

        // ----- Tickets par mois (12 derniers mois) -----
        $monthsLabels = [];
        $ticketsData = [];
        $cumulativeTickets = [];

        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $period = Carbon::parse($startDate)->monthsUntil(now()->endOfMonth());
        $runningTotal = 0;
        foreach ($period as $dt) {
            $monthsLabels[] = $dt->translatedFormat('M Y');
            $count = Ticket::whereYear('created_at', $dt->year)
                           ->whereMonth('created_at', $dt->month)
                           ->count();
            $ticketsData[] = $count;
            $runningTotal += $count;
            $cumulativeTickets[] = $runningTotal;
        }

        // ----- Répartition par statut -----
        $statusLabels = ['Ouvert', 'En cours', 'Transféré', 'En attente', 'Réouvert', 'Résolu', 'Clôturé'];
        $statusCounts = [
            Ticket::where('status', 'OPEN')->count(),
            Ticket::where('status', 'IN_PROGRESS')->count(),
            Ticket::where('status', 'TRANSFERRED')->count(),
            Ticket::where('status', 'ON_HOLD')->count(),
            Ticket::where('status', 'REOPENED')->count(),
            Ticket::where('status', 'RESOLVED')->count(),
            Ticket::where('status', 'CLOSED')->count(),
        ];

        // ----- SLA par priorité (temps de résolution en heures) -----
       $slas = SlaRule::with('unit')
    ->where('is_active', true)
    ->orderBy('unit_id')
    ->orderBy('is_urgent')
    ->get();

$slaPriorities = $slas->map(function($s) {
    $urgence = $s->is_urgent ? 'Urgent' : 'Normal';
    $unite   = $s->unit?->name ?? 'Unité ?';
    return "{$unite} — {$urgence}";
})->toArray();

$slaResolutionTimes = $slas->pluck('ttr')->toArray();

        // ----- Taux de résolution (tickets résolus + clos / total) -----
        $totalTickets = Ticket::count();
        $resolvedClosed = Ticket::whereIn('status', ['RESOLVED', 'CLOSED'])->count();
        $resolutionRate = $totalTickets > 0 ? round(($resolvedClosed / $totalTickets) * 100) : 0;

        // ----- Données pour scatter plot (temps de résolution par ticket) -----
        $scatterData = Ticket::whereNotNull('resolved_at')
            ->select('id', DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolved_at) as hours'))
            ->where('resolved_at', '>=', now()->subDays(30))
            ->get()
            ->map(fn($t) => ['x' => $t->id, 'y' => $t->hours])
            ->values()
            ->toArray();

        return view('admin.dashboard', compact(
            'usersCount', 'unitsCount', 'agenciesCount', 'typesCount',
            'slaCount', 'activeSlaCount',
            'monthsLabels', 'ticketsData', 'cumulativeTickets',
            'statusLabels', 'statusCounts',
            'slaPriorities', 'slaResolutionTimes',
            'resolutionRate', 'scatterData'
        ));
    }
}
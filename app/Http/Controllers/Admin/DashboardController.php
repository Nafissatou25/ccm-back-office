<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use App\Models\Agency;
use App\Models\Type;
use App\Models\SlaRule;
use App\Models\Ticket;
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

        // Tickets par mois (année en cours)
        $ticketsPerMonth = Ticket::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();
        $monthsLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $ticketsData = [];
        for ($i = 1; $i <= 12; $i++) {
            $ticketsData[] = $ticketsPerMonth[$i] ?? 0;
        }

        // Répartition par statut
        $statusLabels = ['Ouvert', 'En cours', 'En attente', 'Résolu', 'Clos', 'Réouvert', 'Transféré'];
        $statusCounts = [
            Ticket::where('status', 'OPEN')->count(),
            Ticket::where('status', 'IN_PROGRESS')->count(),
            Ticket::where('status', 'ON_HOLD')->count(),
            Ticket::where('status', 'RESOLVED')->count(),
            Ticket::where('status', 'CLOSED')->count(),
            Ticket::where('status', 'REOPENED')->count(),
            Ticket::where('status', 'TRANSFERRED')->count(),
        ];

        // SLA : temps de résolution moyen par priorité (basé sur les règles SLA actives)
        $slaPriorities = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];
        $slaResolutionTimes = [];
        foreach ($slaPriorities as $priority) {
            $sla = SlaRule::where('priority', $priority)->first();
            $slaResolutionTimes[] = $sla ? $sla->resolution_time : 0;
        }

        // Évolution des tickets (pour area chart) : cumul mensuel
        $cumulativeTickets = [];
        $runningTotal = 0;
        foreach ($ticketsData as $monthCount) {
            $runningTotal += $monthCount;
            $cumulativeTickets[] = $runningTotal;
        }

        // Taux de résolution global
        $totalTickets = Ticket::count();
        $resolvedClosed = Ticket::whereIn('status', ['RESOLVED', 'CLOSED'])->count();
        $resolutionRate = $totalTickets > 0 ? round(($resolvedClosed / $totalTickets) * 100) : 0;

        // Données pour scatter chart (exemple : temps de résolution par ticket)
        $scatterData = Ticket::whereNotNull('resolved_at')
            ->selectRaw('TIMESTAMPDIFF(HOUR, created_at, resolved_at) as hours, id')
            ->get()
            ->map(fn($t) => ['x' => $t->id, 'y' => $t->hours])
            ->values();

        return view('admin.dashboard', compact(
            'usersCount', 'unitsCount', 'agenciesCount', 'typesCount',
            'slaCount', 'activeSlaCount', 'monthsLabels', 'ticketsData',
            'statusLabels', 'statusCounts', 'slaPriorities', 'slaResolutionTimes',
            'cumulativeTickets', 'resolutionRate', 'scatterData'
        ));
    }
}
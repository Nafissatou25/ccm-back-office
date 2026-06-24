<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use App\Models\Agency;
use App\Models\Type;
use App\Models\SlaRule;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Statistiques de base ──
        $usersCount = User::count();
        $unitsCount = Unit::count();
        $agenciesCount = Agency::count();
        $typesCount = Type::count();
        $slaCount = SlaRule::count();
        $activeSlaCount = SlaRule::where('is_active', true)->count();

        // app/Http/Controllers/Admin/DashboardController.php

// ── Utilisateurs par rôle ──
$usersByRole = User::with('role')
    ->select('role_id', DB::raw('count(*) as total'))
    ->groupBy('role_id')
    ->get()
    ->mapWithKeys(function ($item) {
        $roleName = $item->role ? $item->role->display_name : 'Sans rôle';
        return [$roleName => $item->total];
    })
    ->toArray();

$roleLabels = array_keys($usersByRole);
$roleData = array_values($usersByRole);

        // ── Utilisateurs par agence (remplace Unités par agence) ──
        $usersByAgency = User::with('agency')
            ->select('agency_id', DB::raw('count(*) as total'))
            ->groupBy('agency_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $agencyName = $item->agency ? $item->agency->name : 'Sans agence';
                return [$agencyName => $item->total];
            })
            ->toArray();

        $agencyLabels = array_keys($usersByAgency);
        $agencyData = array_values($usersByAgency);

        // ── Types par unité ──
        $typesByUnit = Type::with('unit')
            ->select('unit_id', DB::raw('count(*) as total'))
            ->groupBy('unit_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $unitName = $item->unit ? $item->unit->name : 'Sans unité';
                return [$unitName => $item->total];
            })
            ->toArray();

        $typeUnitLabels = array_keys($typesByUnit);
        $typeUnitData = array_values($typesByUnit);

        // ── Règles SLA : actives vs inactives ──
        $slaActiveCount = SlaRule::where('is_active', true)->count();
        $slaInactiveCount = SlaRule::where('is_active', false)->count();

        // ── Dernières règles SLA ajoutées ──
        $recentSlaRules = SlaRule::with(['unit', 'type'])
            ->latest()
            ->limit(5)
            ->get();

        // ── Évolution des utilisateurs (6 derniers mois) ──
        $userMonths = collect(range(5, 0))->map(fn($i) => now()->subMonths($i));
        $userMonthsLabels = $userMonths->map(fn($m) => $m->format('M Y'))->toArray();
        $userMonthsData = $userMonths->map(fn($m) => 
            User::whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->count()
        )->toArray();

        return view('admin.dashboard', compact(
            'usersCount', 'unitsCount', 'agenciesCount', 'typesCount',
            'slaCount', 'activeSlaCount',
            'roleLabels', 'roleData',
            'agencyLabels', 'agencyData',
            'typeUnitLabels', 'typeUnitData',
            'slaActiveCount', 'slaInactiveCount',
            'recentSlaRules',
            'userMonthsLabels', 'userMonthsData'
        ));
    }
}
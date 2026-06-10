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


        return view('admin.dashboard', compact(
            'usersCount', 'unitsCount', 'agenciesCount', 'typesCount',
            'slaCount', 'activeSlaCount', 'monthsLabels', 'ticketsData',
            'statusLabels', 'statusCounts', 'slaPriorities', 'slaResolutionTimes',
            'cumulativeTickets', 'resolutionRate', 'scatterData'
        ));
    }
}
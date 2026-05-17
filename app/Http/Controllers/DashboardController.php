<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [

            'usersCount' => User::count(),

            'ticketsCount' => Ticket::count(),

            'openTickets' => Ticket::where('status', 'OPEN')->count(),

            'inProgressTickets' => Ticket::where('status', 'IN_PROGRESS')->count(),

            'resolvedTickets' => Ticket::where('status', 'RESOLVED')->count(),

            'closedTickets' => Ticket::where('status', 'CLOSED')->count(),
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CompanyController extends Controller
{

public function supervisors($companyId)
{
    $supervisors = User::whereHas('role', fn($q) => $q->where('name', 'SUPERVISOR'))
        ->where('company_id', $companyId)
        ->get();
        dd($companyId, $supervisors->pluck('name'));
    return response()->json($supervisors);
}
}

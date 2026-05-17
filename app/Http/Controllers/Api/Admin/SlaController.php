<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SlaRule;

class SlaController extends Controller
{
    public function index()
    {
        return SlaRule::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required',
            'priority' => 'required',
            'response_time' => 'required|integer',
            'resolution_time' => 'required|integer',
            'is_active' => 'required|boolean'
        ]);

        return SlaRule::create($validated);
    }
}
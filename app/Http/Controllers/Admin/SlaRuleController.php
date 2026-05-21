<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaRule;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SlaRuleController extends Controller
{
    /**
     * LISTE
     */
    public function index()
    {
        $rules = SlaRule::with('unit')
            ->latest()
            ->get();

        return view('admin.slaRules.index', compact('rules'));
    }

    /**
     * CREATE FORM
     */
    public function create()
    {
        $units = Unit::orderBy('name')->get();

        return view('admin.slaRules.create', compact('units'));
    }

    /**
     * STORE
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'unit_id' => 'required|exists:units,id',
        'priority' => 'required|string',
        'response_time' => 'required|integer|min:1',
        'resolution_time' => 'required|integer|min:1',
    ]);

    // Vérifier l'unicité avant création
    $exists = SlaRule::where('unit_id', $data['unit_id'])
        ->where('priority', $data['priority'])
        ->exists();

    if ($exists) {
        return back()
            ->withErrors(['priority' => 'Cette priorité existe déjà pour cette unité.'])
            ->withInput();
    }

    $data['is_active'] = $request->has('is_active');

    SlaRule::create($data);

    return redirect()
        ->route('admin.slaRules.index')
        ->with('success', 'Règle SLA créée avec succès');
}

    /**
     * EDIT FORM
     */
    public function edit(SlaRule $slaRule)
    {
        $units = Unit::orderBy('name')->get();

        return view('admin.slaRules.edit', compact('slaRule', 'units'));
    }

    /**
     * UPDATE
     */

public function update(Request $request, SlaRule $slaRule)
{
    $request->validate([
        'response_time' => 'required|integer|min:1',
        'resolution_time' => 'required|integer|min:1',
    ]);

    $slaRule->update([
        'response_time' => $request->response_time,
        'resolution_time' => $request->resolution_time,
        'is_active' => $request->has('is_active'),
    ]);

    return redirect()->route('admin.slaRules.index')
        ->with('success', 'Règle SLA modifiée avec succès');
}

    /**
     * DELETE
     */
    public function destroy(SlaRule $slaRule)
    {
        $slaRule->delete();

        return back()
            ->with('success', 'Règle SLA supprimée');
    }
}
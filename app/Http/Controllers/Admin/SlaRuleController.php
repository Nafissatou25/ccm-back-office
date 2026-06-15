<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaRule;
use App\Models\Unit;
use App\Models\Type;
use Illuminate\Http\Request;

class SlaRuleController extends Controller
{
    public function index()
    {
        $rules = SlaRule::with(['unit', 'type'])->latest()->get();
        return view('admin.slaRules.index', compact('rules'));
    }

    public function create()
    {
        $units = Unit::orderBy('name')->get();
        $types = Type::with('unit')->orderBy('name')->get();
        return view('admin.slaRules.create', compact('units', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id'   => 'required|exists:units,id',
            'type_id'   => 'nullable|exists:types,id',
            'is_urgent' => 'required|in:0,1',
            'tto'       => 'required|integer|min:1',
            'ttr'       => 'required|integer|min:1',
        ]);

        $exists = SlaRule::where('unit_id', $data['unit_id'])
            ->where('type_id', $data['type_id'] ?? null)
            ->where('is_urgent', $data['is_urgent'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['is_urgent' => 'Une règle SLA existe déjà pour cette combinaison.'])
                ->withInput();
        }

        $data['is_active'] = $request->has('is_active');

        SlaRule::create($data);

        return redirect()->route('admin.slaRules.index')
            ->with('success', 'Règle SLA créée avec succès');
    }

    public function edit(SlaRule $slaRule)
    {
        $units = Unit::orderBy('name')->get();
        $types = Type::with('unit')->orderBy('name')->get();
        return view('admin.slaRules.edit', compact('slaRule', 'units', 'types'));
    }

    public function update(Request $request, SlaRule $slaRule)
    {
        $request->validate([
            'tto' => 'required|integer|min:1',
            'ttr' => 'required|integer|min:1',
        ]);

        $slaRule->update([
            'tto'       => $request->tto,
            'ttr'       => $request->ttr,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.slaRules.index')
            ->with('success', 'Règle SLA modifiée avec succès');
    }

    public function destroy(SlaRule $slaRule)
    {
        $slaRule->delete();
        return back()->with('success', 'Règle SLA supprimée');
    }
}
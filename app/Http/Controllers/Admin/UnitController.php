<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * LISTE
     */
    public function index()
    {
        $units = Unit::latest()->get();

        return view('admin.units.index', compact('units'));
    }

    /**
     * FORM CREATE
     */
    public function create()
    {
        return view('admin.units.create');
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
        ]);

        Unit::create($data);

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unité créée avec succès');
    }

    /**
     * FORM EDIT
     */
    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    /**
     * UPDATE
     */
    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
        ]);

        $unit->update($data);

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unité modifiée avec succès');
    }

    /**
     * DELETE
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();

        return back()
            ->with('success', 'Unité supprimée');
    }
}
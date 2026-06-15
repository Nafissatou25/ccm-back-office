<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Models\Unit;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function index()
    {
        $types = Type::with('unit')->latest()->get();
        return view('admin.types.index', compact('types'));
    }

    public function create()
    {
        $units = Unit::orderBy('name')->get();
        return view('admin.types.create', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
        ]);

        $exists = Type::where('unit_id', $data['unit_id'])
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['name' => 'Ce type existe déjà pour cette unité.'])
                ->withInput();
        }

        Type::create($data);

        return redirect()->route('admin.types.index')
            ->with('success', 'Type créé avec succès');
    }

    public function edit(Type $type)
    {
        $units = Unit::orderBy('name')->get();
        return view('admin.types.edit', compact('type', 'units'));
    }

    public function update(Request $request, Type $type)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
        ]);

        // Vérifier unicité en excluant le type actuel
        $exists = Type::where('unit_id', $data['unit_id'])
            ->where('name', $data['name'])
            ->where('id', '!=', $type->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['name' => 'Ce type existe déjà pour cette unité.'])
                ->withInput();
        }

        $type->update($data);

        return redirect()->route('admin.types.index')
            ->with('success', 'Type modifié');
    }

    public function destroy(Type $type)
    {
        $type->delete();
        return back()->with('success', 'Type supprimé');
    }
}
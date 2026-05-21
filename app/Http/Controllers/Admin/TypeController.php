<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Models\Unit;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    /**
     * LISTE
     */
    public function index()
    {
        $types = Type::with('unit')
            ->latest()
            ->get();

        return view('admin.types.index', compact('types'));
    }

    /**
     * FORM CREATE
     */
    public function create()
    {
        $units = Unit::orderBy('name')->get();

        return view('admin.types.create', compact('units'));
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
        ]);

        Type::create($data);

        return redirect()
            ->route('admin.types.index')
            ->with('success', 'Type créé avec succès');
    }

    /**
     * FORM EDIT
     */
    public function edit(Type $type)
    {
        $units = Unit::orderBy('name')->get();

        return view('admin.types.edit', compact(
            'type',
            'units'
        ));
    }

    /**
     * UPDATE
     */
    public function update(Request $request, Type $type)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
        ]);

        $type->update($data);

        return redirect()
            ->route('admin.types.index')
            ->with('success', 'Type modifié');
    }

    /**
     * DELETE
     */
    public function destroy(Type $type)
    {
        $type->delete();

        return back()
            ->with('success', 'Type supprimé');
    }
}
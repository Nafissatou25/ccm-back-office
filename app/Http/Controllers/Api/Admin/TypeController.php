<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function index()
    {
        return Type::with('category')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required|exists:categories,id'
        ]);

        return Type::create($request->all());
    }

    public function show($id)
    {
        return Type::with('category')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $type = Type::findOrFail($id);
        $type->update($request->all());

        return $type;
    }

    public function destroy($id)
    {
        Type::destroy($id);

        return response()->json(['message' => 'Deleted']);
    }
}
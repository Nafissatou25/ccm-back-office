<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
class UnitController extends Controller
{
    public function index()
    {
        return Unit::all();
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        return Unit::create($request->all());
    }

    public function show($id)
    {
        return Unit::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->update($request->all());

        return $unit;
    }

    public function destroy($id)
    {
        Unit::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
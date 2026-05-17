<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
class AgencyController extends Controller
{
    public function index() { return Agency::all(); }

    public function store(Request $request)
    {
        return Agency::create($request->validate(['name' => 'required']));
    }

    public function show($id) { return Agency::findOrFail($id); }

    public function update(Request $request, $id)
    {
        $agency = Agency::findOrFail($id);
        $agency->update($request->all());
        return $agency;
    }

    public function destroy($id)
    {
        Agency::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
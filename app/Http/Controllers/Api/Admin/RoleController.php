<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index() { return Role::all(); }

    public function store(Request $request)
    {
        return Role::create($request->validate(['name' => 'required']));
    }

    public function show($id) { return Role::findOrFail($id); }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->all());
        return $role;
    }

    public function destroy($id)
    {
        Role::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
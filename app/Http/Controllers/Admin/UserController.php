<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Agency;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
{
    return view('admin.users.create', [
        'roles' => Role::all(),
        'agencies' => Agency::all(),
        'units' => Unit::all(),
        'companies' => Company::all(),
    ]);
}

    public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string',
        'matricule' => 'required|string|unique:users,matricule',
        'email' => 'required|email|unique:users,email',
        'password' => 'required',

        'role_id' => 'required|exists:roles,id',
        'agency_id' => 'nullable|exists:agencies,id',
        'unit_id' => 'nullable|exists:units,id',
        'company_id' => 'nullable|exists:companies,id',
    ]);

    $data['password'] = bcrypt($data['password']);

    User::create($data);

    return redirect()->route('admin.users.index')
        ->with('success', 'Utilisateur créé');
}

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
    'name' => 'required',
    'matricule' => 'required|unique:users,matricule,' . $user->id,
    'email' => 'nullable|email|unique:users,email,' . $user->id,
]);
        $user->update([
    'name' => $request->name,
    'matricule' => $request->matricule,
    'email' => $request->email,
    'role_id' => $request->role_id,
    'agency_id' => $request->agency_id,
    'unit_id' => $request->unit_id,
    'company_id' => $request->company_id,
]);
        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back();
    }
}
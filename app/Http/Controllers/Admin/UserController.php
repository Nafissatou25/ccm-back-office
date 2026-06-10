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
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'companies' => Company::all()
        ]);

        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back();
    }
}
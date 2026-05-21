@extends('layouts.app')

@section('title', 'Modifier utilisateur')

@section('content')

<div class="card">
    <div class="card-body">

        <h4 class="mb-4">Modifier utilisateur</h4>

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Nom</label>
                <input type="text" name="name" class="form-control"
                       value="{{ $user->name }}" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                       value="{{ $user->email }}" required>
            </div>

            <div class="mb-3">
                <label>Rôle</label>
                <select name="role_id" class="form-control">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}"
                            {{ $user->role_id == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Nouveau mot de passe (optionnel)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <button class="btn btn-primary">
                Mettre à jour
            </button>

        </form>

    </div>
</div>

@endsection
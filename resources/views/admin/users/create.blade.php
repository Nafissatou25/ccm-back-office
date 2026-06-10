@extends('layouts.app')

@section('title', 'Créer un utilisateur')

@section('content')

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="page-header">
    <h3 class="page-title">Créer un utilisateur</h3>
</div>

<div class="card">
    <div class="card-body">

        <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf

    <div class="row">

        {{-- NAME --}}
        <div class="col-md-6 mb-3">
            <label>Nom</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        {{-- EMAIL --}}
        <div class="col-md-6 mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        {{-- PASSWORD --}}
        <div class="col-md-6 mb-3">
            <label>Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        {{-- ROLE --}}
        <div class="col-md-6 mb-3">
            <label>Rôle</label>
            <select name="role_id" class="form-control" required>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- AGENCY --}}
        <div class="col-md-6 mb-3">
            <label>Agence</label>
            <select name="agency_id" class="form-control">
                <option value="">-- Optionnel --</option>
                @foreach($agencies as $agency)
                    <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- UNIT --}}
        <div class="col-md-6 mb-3">
            <label>Unité</label>
            <select name="unit_id" class="form-control">
                <option value="">-- Optionnel --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- COMPANY --}}
        <div class="col-md-6 mb-3">
            <label>Entreprise</label>
            <select name="company_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

    </div>

    <button class="btn btn-primary">Créer</button>
</form>

    </div>
</div>

@endsection
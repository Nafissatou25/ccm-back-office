@extends('layouts.app')
@section('title', 'Créer un type')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Créer un type de réclamation</h3>
    <a href="{{ route('admin.types.index') }}" class="btn btn-light border">← Retour</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.types.store') }}">
            @csrf
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Unité <span class="text-danger">*</span></label>
                    <select name="unit_id" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nom du type <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name') }}" required>
                </div>

            </div>

            <div class="alert alert-info mt-2">
                <i class="mdi mdi-information-outline"></i>
                Les délais SLA (TTO/TTR) sont configurés séparément dans
                <a href="{{ route('admin.slaRules.index') }}">Règles SLA</a>.
                Ce type héritera automatiquement de la règle par défaut de son unité
                jusqu'à ce qu'une règle spécifique lui soit assignée.
            </div>

            <button class="btn btn-primary">Créer</button>
        </form>
    </div>
</div>

@endsection
@extends('layouts.app')
@section('title', 'Modifier SLA')
@section('content')

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
        <h3 class="mb-4">Modifier la règle SLA</h3>

        <form method="POST" action="{{ route('admin.slaRules.update', $slaRule) }}">
            @csrf
            @method('PUT')
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Unité</label>
                    <input type="text" class="form-control"
                           value="{{ $slaRule->unit?->name }}" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Type</label>
                    <input type="text" class="form-control"
                           value="{{ $slaRule->type?->name ?? 'Par défaut (toute l\'unité)' }}" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Urgence</label>
                    <input type="text" class="form-control"
                           value="{{ $slaRule->is_urgent ? 'Urgent' : 'Normal' }}" disabled>
                </div>

                <div class="col-md-3 mb-3">
                    <label>TTO (heures) <span class="text-danger">*</span>
                        <small class="text-muted d-block">Temps de prise en charge</small>
                    </label>
                    <input type="number" name="tto" class="form-control"
                           value="{{ old('tto', $slaRule->tto) }}" min="1" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>TTR (heures) <span class="text-danger">*</span>
                        <small class="text-muted d-block">Temps de résolution</small>
                    </label>
                    <input type="number" name="ttr" class="form-control"
                           value="{{ old('ttr', $slaRule->ttr) }}" min="1" required>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input"
                               name="is_active" id="is_active"
                               {{ $slaRule->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Actif</label>
                    </div>
                </div>

            </div>

            <button class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('admin.slaRules.index') }}" class="btn btn-light border ml-2">Annuler</a>
        </form>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Modifier SLA')

@section('content')

<div class="card">
    <div class="card-body">

        <h3 class="mb-4">Modifier règle SLA</h3>

        <form method="POST"
              action="{{ route('admin.slaRules.update', $slaRule) }}">

            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-md-6 mb-3">
    <label>Unité</label>
    <input type="text" class="form-control" value="{{ $slaRule->unit->name }}" disabled>
</div>

<div class="col-md-6 mb-3">
    <label>Priorité</label>
    <input type="text" class="form-control" value="{{ $slaRule->priority }}" disabled>
</div>

                <div class="col-md-6 mb-3">
                    <label>Temps réponse</label>

                    <input type="number"
                           name="response_time"
                           class="form-control"
                           value="{{ $slaRule->response_time }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Temps résolution</label>

                    <input type="number"
                           name="resolution_time"
                           class="form-control"
                           value="{{ $slaRule->resolution_time }}">
                </div>

                <div class="col-md-12 mb-3">

                    <label>

                        <input type="checkbox"
                               name="is_active"
                               {{ $slaRule->is_active ? 'checked' : '' }}>

                        Actif

                    </label>

                </div>

            </div>

            <button class="btn btn-primary">
                Modifier
            </button>

        </form>

    </div>
</div>

@endsection
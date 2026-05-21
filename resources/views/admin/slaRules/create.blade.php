@extends('layouts.app')

@section('title', 'Créer SLA')

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

<div class="card">
    <div class="card-body">

        <h3 class="mb-4">Créer une règle SLA</h3>

        <form method="POST"
              action="{{ route('admin.slaRules.store') }}">

            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Unité</label>

                    <select name="unit_id"
                            class="form-control"
                            required>

                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">
                                {{ $unit->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Priorité</label>

                    <select name="priority"
                            class="form-control"
                            required>

                        <option value="LOW">LOW</option>
                        <option value="MEDIUM">MEDIUM</option>
                        <option value="HIGH">HIGH</option>
                        <option value="CRITICAL">CRITICAL</option>

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Temps de réponse (minutes)</label>

                    <input type="number"
                           name="response_time"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Temps de résolution (minutes)</label>

                    <input type="number"
                           name="resolution_time"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-12 mb-3">
                    <label>

                        <input type="checkbox"
                               name="is_active"
                               checked>

                        Actif

                    </label>
                </div>

            </div>

            <button class="btn btn-primary">
                Créer
            </button>

        </form>

    </div>
</div>

@endsection
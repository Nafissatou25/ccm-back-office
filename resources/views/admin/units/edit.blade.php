@extends('layouts.app')

@section('title', 'Modifier unité')

@section('content')

<div class="page-header">
    <h3 class="page-title">Modifier unité</h3>
</div>

<div class="card">
    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.units.update', $unit) }}">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Nom de l'unité</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name', $unit->name) }}"
                       required>
            </div>

            <button class="btn btn-primary">
                Mettre à jour
            </button>

            <a href="{{ route('admin.units.index') }}"
               class="btn btn-light">
                Annuler
            </a>

        </form>

    </div>
</div>

@endsection
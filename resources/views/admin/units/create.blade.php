@extends('layouts.app')

@section('title', 'Créer unité')

@section('content')

<div class="page-header">
    <h3 class="page-title">Créer une unité</h3>
</div>

<div class="card">
    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.units.store') }}">

            @csrf

            <div class="mb-3">

                <label>Nom</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       required>

            </div>

            <button class="btn btn-primary">
                Créer
            </button>

        </form>

    </div>
</div>

@endsection
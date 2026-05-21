@extends('layouts.app')

@section('title', 'Modifier agence')

@section('content')

<div class="card">
    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.agencies.update', $agency) }}">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Nom</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ $agency->name }}"
                       required>
            </div>

            <button class="btn btn-primary">
                Mettre à jour
            </button>

        </form>

    </div>
</div>

@endsection
@extends('layouts.app')

@section('title', 'Créer agence')

@section('content')

<div class="card">
    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.agencies.store') }}">

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
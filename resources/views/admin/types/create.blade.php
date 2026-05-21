@extends('layouts.app')

@section('title', 'Créer type')

@section('content')

<div class="card">
    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.types.store') }}">

            @csrf

            <div class="mb-3">
                <label>Nom</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       required>
            </div>

            <div class="mb-3">
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

            <button class="btn btn-primary">
                Créer
            </button>

        </form>

    </div>
</div>

@endsection
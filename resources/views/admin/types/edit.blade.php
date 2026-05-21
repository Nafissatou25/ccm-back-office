@extends('layouts.app')

@section('title', 'Modifier type')

@section('content')

<div class="card">
    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.types.update', $type) }}">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Nom</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ $type->name }}"
                       required>
            </div>

            <div class="mb-3">
                <label>Unité</label>

                <select name="unit_id"
                        class="form-control"
                        required>

                    @foreach($units as $unit)

                        <option value="{{ $unit->id }}"
                            {{ $type->unit_id == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>

                    @endforeach

                </select>
            </div>

            <button class="btn btn-primary">
                Mettre à jour
            </button>

        </form>

    </div>
</div>

@endsection
@extends('layouts.app')

@section('title', 'Types')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Types de réclamations</h3>

    <a href="{{ route('admin.types.create') }}"
       class="btn btn-primary">
        + Nouveau type
    </a>
</div>

<div class="card">
    <div class="card-body">

        <table class="table table-hover">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Unité</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @foreach($types as $type)

                    <tr>

                        <td>#{{ $type->id }}</td>

                        <td>{{ $type->name }}</td>

                        <td>
                            {{ $type->unit?->name }}
                        </td>

                        <td>

                            <a href="{{ route('admin.types.edit', $type) }}"
                               class="btn btn-warning btn-sm">
                                Modifier
                            </a>

                            <form action="{{ route('admin.types.destroy', $type) }}"
                                  method="POST"
                                  style="display:inline;">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Supprimer ?')">
                                    Supprimer
                                </button>

                            </form>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>
</div>

@endsection
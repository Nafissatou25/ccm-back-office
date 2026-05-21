@extends('layouts.app')

@section('title', 'Agences')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Liste des agences</h3>

    <a href="{{ route('admin.agencies.create') }}"
       class="btn btn-primary">
        + Nouvelle agence
    </a>
</div>

<div class="card">
    <div class="card-body">

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($agencies as $agency)
                    <tr>
                        <td>#{{ $agency->id }}</td>

                        <td>{{ $agency->name }}</td>

                        <td>
                            <a href="{{ route('admin.agencies.edit', $agency) }}"
                               class="btn btn-warning btn-sm">
                                Modifier
                            </a>

                            <form action="{{ route('admin.agencies.destroy', $agency) }}"
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
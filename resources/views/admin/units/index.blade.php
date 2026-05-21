@extends('layouts.app')

@section('title', 'Unités')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <h3 class="mb-0">Liste des unités</h3>

    <a href="{{ route('admin.units.create') }}"
       class="btn btn-primary">
        + Nouvelle unité
    </a>

</div>

<div class="card">
    <div class="card-body">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Utilisateurs</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse($units as $unit)

                    <tr>

                        <td>#{{ $unit->id }}</td>

                        <td>
                            <strong>{{ $unit->name }}</strong>
                        </td>

                        <td>
                            {{ $unit->users()->count() }}
                        </td>

                        <td>

                            <a href="{{ route('admin.units.edit', $unit) }}"
                               class="btn btn-sm btn-warning">
                                Modifier
                            </a>

                            <form action="{{ route('admin.units.destroy', $unit) }}"
                                  method="POST"
                                  style="display:inline;">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-danger"
                                        onclick="return confirm('Supprimer cette unité ?')">
                                    Supprimer
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4" class="text-center">
                            Aucune unité trouvée
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>
</div>

@endsection
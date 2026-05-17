@extends('layouts.app')

@section('title', 'Users')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Liste des utilisateurs</h3>

    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        + Nouvel utilisateur
    </a>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <div style="max-height: 70vh; overflow-y: auto; border-radius: 0.5rem;">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td><strong>#{{ $user->id }}</strong></td>

                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>

                                <td>{{ $user->role?->name ?? '-' }}</td>

                                <td>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-sm btn-warning">
                                        Modifier
                                    </a>

                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                          method="POST"
                                          style="display:inline;">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-danger"
                                                onclick="return confirm('Supprimer cet utilisateur ?')">
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

    </div>
</div>

@endsection
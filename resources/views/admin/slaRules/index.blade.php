@extends('layouts.app')

@section('title', 'SLA')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Règles SLA</h3>

    <a href="{{ route('admin.slaRules.create') }}"
       class="btn btn-primary">
        + Nouvelle règle
    </a>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>
                    <tr>
                        <th>Unité</th>
                        <th>Priorité</th>
                        <th>Temps réponse</th>
                        <th>Temps résolution</th>
                        <th>Statut</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($rules as $rule)

                        <tr>

                            <td>{{ $rule->unit?->name }}</td>

                            <td>{{ $rule->priority }}</td>

                            <td>
                                {{ $rule->response_time }} min
                            </td>

                            <td>
                                {{ $rule->resolution_time }} min
                            </td>

                            <td>
                                @if($rule->is_active)
                                        Active
                                @else
                                        Inactive
                                @endif
                            </td>

                            <td>

                                <a href="{{ route('admin.slaRules.edit', $rule) }}"
                                   class="btn btn-warning btn-sm">
                                    Modifier
                                </a>

                                <form action="{{ route('admin.slaRules.destroy', $rule) }}"
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
</div>

@endsection
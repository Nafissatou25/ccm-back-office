@extends('layouts.app')
@section('title', 'Règles SLA')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Règles SLA</h3>
    <a href="{{ route('admin.slaRules.create') }}" class="btn btn-primary">+ Nouvelle règle</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Unité</th>
                        <th>Type</th>
                        <th>Urgence</th>
                        <th>TTO</th>
                        <th>TTR</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td>{{ $rule->unit?->name ?? '—' }}</td>
                            <td>
                                @if($rule->type)
                                    {{ $rule->type->name }}
                                @else
                                    <span class="text-muted"><em>Par défaut</em></span>
                                @endif
                            </td>
                            <td>
                                @if($rule->is_urgent)
                                    <span class="badge badge-danger">Urgent</span>
                                @else
                                    <span class="badge badge-primary">Normal</span>
                                @endif
                            </td>
                            <td>{{ $rule->tto }}h</td>
                            <td>{{ $rule->ttr }}h</td>
                            <td>
                                @if($rule->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.slaRules.edit', $rule) }}"
                                   class="btn btn-warning btn-sm">Modifier</a>

                                <form action="{{ route('admin.slaRules.destroy', $rule) }}"
                                      method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Supprimer cette règle ?')">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune règle SLA configurée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
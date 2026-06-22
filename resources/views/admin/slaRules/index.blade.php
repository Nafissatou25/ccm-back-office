@extends('layouts.app')

@section('title', 'Règles SLA')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0 fw-bold">
            <i class="mdi mdi-clock-alert me-2 text-primary"></i>
            Règles SLA
        </h3>
        <p class="text-muted small mb-0">
            {{ $rules->count() }} règle(s) SLA configurée(s)
        </p>
    </div>

    <a href="{{ route('admin.slaRules.create') }}"
       class="btn btn-primary rounded-pill px-4">
        <i class="mdi mdi-plus me-1"></i> Nouvelle règle
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">

        <div class="table-responsive">
            <div style="max-height: 70vh; overflow-y: auto; border-radius: 0.75rem;">

                <table class="table table-hover align-middle mb-0">

                    <thead class="bg-light" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-domain me-1"></i> Unités
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-tag me-1"></i> Type
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">
                                <i class="mdi mdi-alert me-1"></i> Urgence
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">
                                <i class="mdi mdi-clock-start me-1"></i> TTO
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">
                                <i class="mdi mdi-clock-end me-1"></i> TTR
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">
                                <i class="mdi mdi-check-circle me-1"></i> Statut
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-end pe-4">
                                <i class="mdi mdi-tooltip-edit me-1"></i> Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rules as $rule)
                            <tr class="border-bottom">
                                {{-- UNITÉ --}}
                                <td class="ps-4">
                                    @if($rule->unit)
                                        <span class="d-flex align-items-center">
                                            <i class="mdi mdi-domain text-muted me-1" style="font-size: 14px;"></i>
                                            <strong>{{ $rule->unit->name }}</strong>
                                        </span>
                                    @else
                                        <span class="text-muted">Toutes unités</span>
                                    @endif
                                </td>

                                {{-- TYPE --}}
                                <td>
                                    @if($rule->type)
                                        {{ $rule->type->name }}
                                    @else
                                        <span class="text-muted"><em>Tous types</em></span>
                                    @endif
                                </td>

                                {{-- URGENCE --}}
                                <td class="text-center">
                                    @if($rule->is_urgent)
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                            <i class="mdi mdi-alert me-1"></i> Urgent
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill">
                                            <i class="mdi mdi-check-circle me-1"></i> Normal
                                        </span>
                                    @endif
                                </td>

                                {{-- TTO (heures) --}}
                                <td class="text-center">
                                    <span class="fw-bold">{{ $rule->tto }}</span>
                                    <span class="text-muted small">h</span>
                                </td>

                                {{-- TTR (heures) --}}
                                <td class="text-center">
                                    <span class="fw-bold">{{ $rule->ttr }}</span>
                                    <span class="text-muted small">h</span>
                                </td>

                                {{-- STATUT (actif/inactif) --}}
                                <td class="text-center">
                                    @if($rule->is_active)
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                            <i class="mdi mdi-check-circle me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill">
                                            <i class="mdi mdi-close-circle me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.slaRules.edit', $rule) }}"
                                           class="btn btn-sm btn-outline-warning rounded-pill px-3"
                                           title="Modifier la règle">
                                            <i class="mdi mdi-pencil me-1"></i>
                                            Modifier
                                        </a>

                                        <form action="{{ route('admin.slaRules.destroy', $rule) }}"
                                              method="POST"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    onclick="return confirm('Supprimer définitivement cette règle SLA ?')"
                                                    title="Supprimer la règle">
                                                <i class="mdi mdi-delete me-1"></i>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-clock-alert-outline text-muted" style="font-size: 48px;"></i>
                                        <p class="text-muted mt-3 mb-0">Aucune règle SLA configurée.</p>
                                        <a href="{{ route('admin.slaRules.create') }}" class="btn btn-primary btn-sm mt-2 rounded-pill">
                                            <i class="mdi mdi-plus me-1"></i> Ajouter une règle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

        @if(isset($rules) && method_exists($rules, 'links'))
            <div class="d-flex justify-content-center p-3 border-top bg-light rounded-bottom-4">
                {{ $rules->links() }}
            </div>
        @endif

    </div>
</div>

@endsection

@push('styles')
<style>
    .bg-warning-subtle { background-color: #fff3cd !important; }
    .bg-danger-subtle { background-color: #f8d7da !important; }
    .bg-success-subtle { background-color: #d1e7dd !important; }
    .bg-info-subtle { background-color: #cff4fc !important; }
    .bg-primary-subtle { background-color: #cfe2ff !important; }
    .bg-secondary-subtle { background-color: #e9ecef !important; }

    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-success { color: #198754 !important; }
    .text-info { color: #0dcaf0 !important; }
    .text-primary { color: #0d6efd !important; }
    .text-secondary { color: #6c757d !important; }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .rounded-4 {
        border-radius: 0.75rem !important;
    }
    .rounded-bottom-4 {
        border-bottom-left-radius: 0.75rem !important;
        border-bottom-right-radius: 0.75rem !important;
    }

    .btn-outline-warning {
        border-color: #ffc107;
        color: #ffc107;
    }
    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #fff;
    }

    .btn-outline-danger {
        border-color: #dc3545;
        color: #dc3545;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }

    .badge .mdi {
        font-size: 0.8rem;
        vertical-align: middle;
    }
</style>
@endpush
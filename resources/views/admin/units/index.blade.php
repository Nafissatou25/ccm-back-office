@extends('layouts.app')

@section('title', 'Unités')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0 fw-bold">
            <i class="mdi mdi-domain me-2 text-primary"></i>
            Unités
        </h3>
        <p class="text-muted small mb-0">
            {{ $units->count() }} unité(s) enregistrée(s)
        </p>
    </div>

    <a href="{{ route('admin.units.create') }}"
       class="btn btn-primary rounded-pill px-4">
        <i class="mdi mdi-plus me-1"></i> Nouvelle unité
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
                                <i class="mdi mdi-hash me-1"></i> ID
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-domain me-1"></i> Unité
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">
                                <i class="mdi mdi-account-group me-1"></i> Utilisateurs
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-end pe-4">
                                <i class="mdi mdi-tooltip-edit me-1"></i> Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($units as $unit)
                            <tr class="border-bottom">
                                {{-- ID --}}
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark fw-normal">
                                        #{{ $unit->id }}
                                    </span>
                                </td>

                                {{-- NOM DE L'UNITÉ --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="unit-icon me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: linear-gradient(135deg, #4e73df, #224abe); color: white;">
                                            <i class="mdi mdi-domain" style="font-size: 20px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $unit->name }}</div>
                                            <small class="text-muted">
                                                {{ $unit->users()->count() }} utilisateur(s)
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                {{-- NOMBRE D'UTILISATEURS (badge) --}}
                                <td class="text-center">
                                    @php
                                        $count = $unit->users()->count();
                                        $badgeClass = $count > 10 ? 'danger' : ($count > 5 ? 'warning' : 'success');
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}-subtle text-{{ $badgeClass }} px-3 py-2 rounded-pill fs-6">
                                        {{ $count }}
                                        <i class="mdi mdi-account ms-1"></i>
                                    </span>
                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.units.edit', $unit) }}"
                                           class="btn btn-sm btn-outline-warning rounded-pill px-3"
                                           title="Modifier l'unité">
                                            <i class="mdi mdi-pencil me-1"></i>
                                            Modifier
                                        </a>

                                        <form action="{{ route('admin.units.destroy', $unit) }}"
                                              method="POST"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    onclick="return confirm('Supprimer définitivement cette unité ? Tous les utilisateurs et types associés seront affectés.')"
                                                    title="Supprimer l'unité">
                                                <i class="mdi mdi-outline-danger me-1"></i>
                                                Désactiver
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-domain-off text-muted" style="font-size: 48px;"></i>
                                        <p class="text-muted mt-3 mb-0">Aucune unité enregistrée.</p>
                                        <a href="{{ route('admin.units.create') }}" class="btn btn-primary btn-sm mt-2 rounded-pill">
                                            <i class="mdi mdi-plus me-1"></i> Ajouter une unité
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

        @if(isset($units) && method_exists($units, 'links'))
            <div class="d-flex justify-content-center p-3 border-top bg-light rounded-bottom-4">
                {{ $units->links() }}
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

    .unit-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: linear-gradient(135deg, #4e73df, #224abe);
        color: white;
        flex-shrink: 0;
    }

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

    .badge.fs-6 {
        font-size: 0.95rem;
    }
</style>
@endpush
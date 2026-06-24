@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0 fw-bold">
            <i class="mdi mdi-account-group me-2 text-primary"></i>
            Utilisateurs
        </h3>
        <p class="text-muted small mb-0">
            {{ $users->count() }} utilisateur(s) enregistré(s)
        </p>
    </div>

    <a href="{{ route('admin.users.create') }}" class="btn btn-primary rounded-pill px-4">
        <i class="mdi mdi-plus me-1"></i> Nouvel utilisateur
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">

        <div class="table-responsive">
            <div style="max-height: 70vh; overflow-y: auto; border-radius: 0.5rem;">

                <table class="table table-hover align-middle mb-0">

                    <thead class="bg-light" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-hash me-1"></i> ID
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-account me-1"></i> Utilisateur
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-email me-1"></i> Email
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-shield-account me-1"></i> Rôle
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-domain me-1"></i> Agence
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-google-circles-communities me-1"></i> Unité
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-end pe-4">
                                <i class="mdi mdi-tooltip-edit me-1"></i> Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                            <tr class="border-bottom">
                                {{-- ID --}}
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark fw-normal">
                                        #{{ $user->id }}
                                    </span>
                                </td>

                                {{-- NOM --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 600; font-size: 14px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            @if($user->id === auth()->id())
                                                <span class="badge bg-primary bg-opacity-10 text-primary fs-7">(Vous)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- EMAIL --}}
                                <td>
                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none text-dark">
                                        {{ $user->email }}
                                    </a>
                                </td>

                                {{-- RÔLE --}}
                                <td>
                                    @php
                                        $roleColors = [
                                            'admin' => 'danger',
                                            'manager' => 'warning',
                                            'supervisor' => 'info',
                                            'technician' => 'primary',
                                            'customer_service' => 'success',
                                        ];
                                        $roleLabel = [
                                            'admin' => 'Administrateur',
                                            'manager' => 'Manager',
                                            'supervisor' => 'Superviseur',
                                            'technician' => 'Technicien',
                                            'customer_service' => 'Service client',
                                        ];
                                        $role = strtolower($user->role?->display_name ?? '');
                                        $color = $roleColors[$role] ?? 'secondary';
                                        $label = $roleLabel[$role] ?? $user->role?->display_name ?? '—';
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} px-3 py-2 rounded-pill">
                                        {{ $label }}
                                    </span>
                                </td>

                                {{-- AGENCE --}}
                                <td>
                                    @if($user->agency?->name)
                                        <span class="d-flex align-items-center">
                                            <i class="mdi mdi-map-marker text-muted me-1" style="font-size: 14px;"></i>
                                            {{ $user->agency->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- UNITÉ --}}
                                <td>
                                    @if($user->unit?->name)
                                        <span class="d-flex align-items-center">
                                            <i class="mdi mdi-domain text-muted me-1" style="font-size: 14px;"></i>
                                            {{ $user->unit->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="btn btn-sm btn-outline-warning rounded-pill px-3"
                                           title="Modifier">
                                            <i class="mdi mdi-pencil me-1"></i>
                                            Modifier
                                        </a>

                                        <form action="{{ route('admin.users.destroy', $user) }}"
                                              method="POST"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    onclick="return confirm('Supprimer définitivement cet utilisateur ? Cette action est irréversible.')"
                                                    title="Supprimer">
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
                                        <i class="mdi mdi-account-off text-muted" style="font-size: 48px;"></i>
                                        <p class="text-muted mt-3 mb-0">Aucun utilisateur enregistré.</p>
                                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mt-2 rounded-pill">
                                            <i class="mdi mdi-plus me-1"></i> Ajouter le premier utilisateur
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

        @if(isset($users) && method_exists($users, 'links'))
            <div class="d-flex justify-content-center p-3 border-top bg-light rounded-bottom-4">
                {{ $users->links() }}
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

    .avatar-circle {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 600;
        font-size: 14px;
        flex-shrink: 0;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge.fs-7 {
        font-size: 0.7rem;
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

    .rounded-4 {
        border-radius: 0.75rem !important;
    }
    .rounded-bottom-4 {
        border-bottom-left-radius: 0.75rem !important;
        border-bottom-right-radius: 0.75rem !important;
    }
</style>
@endpush
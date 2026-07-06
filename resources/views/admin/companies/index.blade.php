@extends('layouts.app')

@section('title', 'Entreprises sous-traitantes')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0 fw-bold">
            <i class="mdi mdi-handshake me-2 text-primary"></i>
            Entreprises sous-traitantes
        </h3>
        <p class="text-muted small mb-0">
            {{ $companies->count() }} entreprise(s) enregistrée(s)
        </p>
    </div>

    <a href="{{ route('admin.companies.create') }}"
       class="btn btn-primary rounded-pill px-4">
        <i class="mdi mdi-plus me-1"></i> Nouvelle entreprise
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">

        <div class="table-responsive">
            <div style="max-height: 70vh; overflow-y: auto; border-radius: 0.75rem;">

                <table class="table table-hover align-middle mb-0">

                    <thead class="bg-light" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">ID</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Entreprise</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Contact</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Statut</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-end pe-4">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($companies as $company)
                            <tr class="border-bottom {{ $company->trashed() ? 'bg-light opacity-75' : '' }}">
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark fw-normal">
                                        #{{ $company->id }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="company-icon me-3"
                                             style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: linear-gradient(135deg, {{ $company->trashed() ? '#6c757d' : '#6f42c1' }}, {{ $company->trashed() ? '#495057' : '#4a1a8a' }}); color: white; flex-shrink: 0;">
                                            <i class="mdi mdi-handshake" style="font-size: 20px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold {{ $company->trashed() ? 'text-muted' : '' }}">
                                                {{ $company->name }}
                                            </div>
                                            @if($company->email)
                                                <small class="text-muted">{{ $company->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($company->contact)
                                        <div>{{ $company->contact }}</div>
                                        <small class="text-muted">{{ $company->phone ?? '' }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($company->trashed())
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
                                            <i class="mdi mdi-circle-off-outline me-1"></i> Désactivée
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                            <i class="mdi mdi-circle me-1"></i> Active
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">

                                        <a href="{{ route('admin.companies.edit', $company) }}"
                                           class="btn btn-sm btn-outline-warning rounded-pill px-3"
                                           title="Modifier">
                                            <i class="mdi mdi-pencil me-1"></i> Modifier
                                        </a>

                                        @if($company->trashed())
                                            <form action="{{ route('admin.companies.restore', $company->id) }}"
                                                  method="POST"
                                                  style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success rounded-pill px-3"
                                                        onclick="return confirm('Réactiver cette entreprise ?')"
                                                        title="Réactiver">
                                                    <i class="mdi mdi-reload me-1"></i> Réactiver
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.companies.destroy', $company) }}"
                                                  method="POST"
                                                  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        onclick="return confirm('Désactiver cette entreprise ?')"
                                                        title="Désactiver">
                                                    <i class="mdi mdi-power me-1"></i> Désactiver
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-domain-off text-muted" style="font-size: 48px;"></i>
                                        <p class="text-muted mt-3 mb-0">Aucune entreprise enregistrée.</p>
                                        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary btn-sm mt-2 rounded-pill">
                                            <i class="mdi mdi-plus me-1"></i> Ajouter une entreprise
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

        @if(isset($companies) && method_exists($companies, 'links'))
            <div class="d-flex justify-content-center p-3 border-top bg-light rounded-bottom-4">
                {{ $companies->links() }}
            </div>
        @endif

    </div>
</div>

@endsection

@push('styles')
<style>
    .company-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        color: white;
        flex-shrink: 0;
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

    .btn-outline-success {
        border-color: #198754;
        color: #198754;
    }
    .btn-outline-success:hover {
        background-color: #198754;
        color: #fff;
    }

    .bg-success-subtle { background-color: #d1e7dd !important; }
    .bg-secondary-subtle { background-color: #e9ecef !important; }

    .rounded-4 {
        border-radius: 0.75rem !important;
    }
    .rounded-bottom-4 {
        border-bottom-left-radius: 0.75rem !important;
        border-bottom-right-radius: 0.75rem !important;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush
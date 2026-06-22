@extends('layouts.app')

@section('title', 'Modifier l\'unité')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="mb-0 fw-bold" style="color: #1e293b;">
            <i class="mdi mdi-domain-edit me-2" style="color: #3b82f6;"></i>
            Modifier l'unité
        </h3>
        <p class="text-muted small mb-0">
            Mettez à jour les informations de l'unité
        </p>
    </div>
    <a href="{{ route('admin.units.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="mdi mdi-arrow-left me-1"></i> Retour
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4">
        <div class="d-flex align-items-start">
            <i class="mdi mdi-alert-circle me-2" style="font-size: 1.5rem; color: #dc3545;"></i>
            <div>
                <strong class="d-block">Veuillez corriger les erreurs suivantes :</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-lg-5">

        <form method="POST"
              action="{{ route('admin.units.update', $unit) }}">

            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-8 mx-auto">
                    <div class="form-floating">
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Nom de l'unité"
                               value="{{ old('name', $unit->name) }}"
                               required
                               autofocus>
                        <label for="name">
                            <i class="mdi mdi-domain me-1"></i> Nom de l'unité
                        </label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text text-muted mt-2">
                        <i class="mdi mdi-information me-1"></i>
                        Exemple : Branchement, Petites interventions, Exploitation réseau, etc.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.units.index') }}"
                   class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="mdi mdi-close me-1"></i> Annuler
                </a>
                <button type="submit"
                        class="btn btn-primary rounded-pill px-4"
                        style="background-color: #3b82f6; border-color: #3b82f6;">
                    <i class="mdi mdi-check me-1"></i> Mettre à jour
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('styles')
<style>
    .card {
        border-radius: 1rem !important;
        background: #ffffff;
        transition: box-shadow 0.2s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1.2rem rgba(0, 0, 0, 0.04);
    }

    .form-floating {
        margin-bottom: 0;
    }
    .form-floating > .form-control {
        height: calc(3.5rem + 2px);
        border-radius: 0.5rem;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-floating > .form-control:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.10);
    }
    .form-floating > .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.10);
    }

    .invalid-feedback {
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }

    .alert-danger {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .alert-danger ul {
        list-style-type: none;
        padding-left: 0;
    }
    .alert-danger ul li::before {
        content: "• ";
        color: #dc3545;
    }

    .btn-primary {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    .btn-primary:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1.25rem !important;
        }
    }
</style>
@endpush
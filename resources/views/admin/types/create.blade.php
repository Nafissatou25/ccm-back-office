@extends('layouts.app')

@section('title', 'Créer un type de réclamation')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="mb-0 fw-bold" style="color: #1e293b;">
            <i class="mdi mdi-tag-plus me-2" style="color: #3b82f6;"></i>
            Créer un type de réclamation
        </h3>
        <p class="text-muted small mb-0">
            Ajoutez un nouveau type de réclamation
        </p>
    </div>
    <a href="{{ route('admin.types.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
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

        <form method="POST" action="{{ route('admin.types.store') }}">
            @csrf

            <div class="row g-4">

                {{-- UNITÉ --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="unit_id"
                                id="unit_id"
                                class="form-select @error('unit_id') is-invalid @enderror"
                                required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="unit_id">
                            <i class="mdi mdi-domain me-1"></i> Unité <span class="text-danger">*</span>
                        </label>
                        @error('unit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- NOM DU TYPE --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Nom du type"
                               value="{{ old('name') }}"
                               required>
                        <label for="name">
                            <i class="mdi mdi-tag me-1"></i> Nom du type <span class="text-danger">*</span>
                        </label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            {{-- INFO SLA --}}
            <div class="alert alert-info border-0 rounded-3 mt-4" style="background-color: #eff6ff; color: #1e40af;">
                <div class="d-flex align-items-start">
                    <i class="mdi mdi-information-outline me-2" style="font-size: 1.25rem;"></i>
                    <div>
                        Les délais SLA (TTO/TTR) sont configurés séparément dans
                        <a href="{{ route('admin.slaRules.index') }}" class="fw-semibold" style="color: #1e40af; text-decoration: underline;">
                            Règles SLA
                        </a>.
                        Ce type héritera automatiquement de la règle par défaut de son unité
                        jusqu'à ce qu'une règle spécifique lui soit assignée.
                    </div>
                </div>
            </div>

            {{-- BOUTONS --}}
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.types.index') }}"
                   class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="mdi mdi-close me-1"></i> Annuler
                </a>
                <button type="submit"
                        class="btn btn-primary rounded-pill px-4"
                        style="background-color: #3b82f6; border-color: #3b82f6;">
                    <i class="mdi mdi-check me-1"></i> Créer
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
    .form-floating > .form-control,
    .form-floating > .form-select {
        height: calc(3.5rem + 2px);
        border-radius: 0.5rem;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-floating > .form-control:focus,
    .form-floating > .form-select:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.10);
    }
    .form-floating > .form-control.is-invalid,
    .form-floating > .form-select.is-invalid {
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

    .btn-outline-secondary {
        border-color: #e2e8f0;
        color: #475569;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1.25rem !important;
        }
    }
</style>
@endpush
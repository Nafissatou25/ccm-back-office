@extends('layouts.app')

@section('title', 'Modifier la règle SLA')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="mb-0 fw-bold" style="color: #1e293b;">
            <i class="mdi mdi-clock-edit me-2" style="color: #3b82f6;"></i>
            Modifier la règle SLA
        </h3>
        <p class="text-muted small mb-0">
            Mettez à jour les délais de la règle
            @if($slaRule->unit)
                pour <strong>{{ $slaRule->unit->name }}</strong>
            @endif
            @if($slaRule->type)
                · <strong>{{ $slaRule->type->name }}</strong>
            @endif
        </p>
    </div>
    <a href="{{ route('admin.slaRules.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
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

        <form method="POST" action="{{ route('admin.slaRules.update', $slaRule) }}">
            @csrf
            @method('PUT')

            {{-- INFORMATIONS LECTURE SEULE --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <small class="text-muted d-block mb-1">Unité</small>
                        <span class="fw-semibold">
                            <i class="mdi mdi-domain me-1" style="color: #3b82f6;"></i>
                            {{ $slaRule->unit?->name ?? 'Toutes les unités' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <small class="text-muted d-block mb-1">Type</small>
                        <span class="fw-semibold">
                            <i class="mdi mdi-tag me-1" style="color: #8b5cf6;"></i>
                            {{ $slaRule->type?->name ?? 'Par défaut (toute l\'unité)' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3">
                        <small class="text-muted d-block mb-1">Niveau d'urgence</small>
                        <span class="fw-semibold">
                            @if($slaRule->is_urgent)
                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                    <i class="mdi mdi-alert me-1"></i> Urgent
                                </span>
                            @else
                                <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill">
                                    <i class="mdi mdi-check-circle me-1"></i> Normal
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="row g-4">

                {{-- TTO --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number"
                               name="tto"
                               id="tto"
                               class="form-control @error('tto') is-invalid @enderror"
                               placeholder="TTO en heures"
                               value="{{ old('tto', $slaRule->tto) }}"
                               min="1"
                               required>
                        <label for="tto">
                            <i class="mdi mdi-clock-start me-1"></i> TTO (heures)
                            <span class="text-danger">*</span>
                        </label>
                        <div class="form-text text-muted">
                            <i class="mdi mdi-information me-1"></i>
                            Temps de prise en charge (Time to Own)
                        </div>
                        @error('tto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- TTR --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number"
                               name="ttr"
                               id="ttr"
                               class="form-control @error('ttr') is-invalid @enderror"
                               placeholder="TTR en heures"
                               value="{{ old('ttr', $slaRule->ttr) }}"
                               min="1"
                               required>
                        <label for="ttr">
                            <i class="mdi mdi-clock-end me-1"></i> TTR (heures)
                            <span class="text-danger">*</span>
                        </label>
                        <div class="form-text text-muted">
                            <i class="mdi mdi-information me-1"></i>
                            Temps de résolution (Time to Resolve)
                        </div>
                        @error('ttr')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ACTIF --}}
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                               class="form-check-input"
                               name="is_active"
                               id="is_active"
                               role="switch"
                               {{ old('is_active', $slaRule->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">
                            <i class="mdi mdi-check-circle me-1" style="color: #22c55e;"></i>
                            Règle active
                        </label>
                        <div class="form-text text-muted">
                            Une règle inactive ne sera pas appliquée aux nouveaux tickets.
                        </div>
                    </div>
                </div>

            </div>

            {{-- BOUTONS --}}
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.slaRules.index') }}"
                   class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="mdi mdi-close me-1"></i> Annuler
                </a>
                <button type="submit"
                        class="btn btn-primary rounded-pill px-4"
                        style="background-color: #3b82f6; border-color: #3b82f6;">
                    <i class="mdi mdi-check me-1"></i> Enregistrer
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
    .form-floating > .form-control[disabled] {
        background-color: #f1f5f9;
        color: #475569;
    }
    .form-floating .form-text {
        margin-top: 0.25rem;
        font-size: 0.8rem;
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

    /* Badges personnalisés */
    .bg-danger-subtle {
        background-color: #fee2e2 !important;
    }
    .bg-info-subtle {
        background-color: #dbeafe !important;
    }
    .text-danger {
        color: #dc2626 !important;
    }
    .text-info {
        color: #2563eb !important;
    }

    /* Switch moderne */
    .form-switch .form-check-input {
        width: 2.5rem;
        height: 1.25rem;
        border-radius: 1rem;
        background-color: #cbd5e1;
        border-color: #cbd5e1;
        cursor: pointer;
        transition: background-color 0.2s, border-color 0.2s;
    }
    .form-switch .form-check-input:checked {
        background-color: #22c55e;
        border-color: #22c55e;
    }
    .form-switch .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25);
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
        .bg-light.rounded-3.p-3 {
            padding: 0.75rem !important;
        }
    }
</style>
@endpush
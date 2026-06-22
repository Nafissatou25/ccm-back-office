@extends('layouts.app')

@section('title', 'Créer une règle SLA')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="mb-0 fw-bold" style="color: #1e293b;">
            <i class="mdi mdi-clock-plus me-2" style="color: #3b82f6;"></i>
            Créer une règle SLA
        </h3>
        <p class="text-muted small mb-0">
            Définissez les délais de prise en charge et de résolution
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

        <form method="POST" action="{{ route('admin.slaRules.store') }}">
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

                {{-- TYPE --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="type_id"
                                id="type_id"
                                class="form-select @error('type_id') is-invalid @enderror">
                            <option value="">-- Par défaut (toute l'unité) --</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}"
                                        data-unit="{{ $type->unit_id }}"
                                        {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="type_id">
                            <i class="mdi mdi-tag me-1"></i> Type
                        </label>
                        @error('type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text text-muted mt-1">
                        <i class="mdi mdi-information me-1"></i>
                        Laissez "Par défaut" pour appliquer la règle à toute l'unité.
                    </div>
                </div>

                {{-- URGENCE --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="is_urgent"
                                id="is_urgent"
                                class="form-select @error('is_urgent') is-invalid @enderror"
                                required>
                            <option value="0" {{ old('is_urgent') == '0' ? 'selected' : '' }}>Normal</option>
                            <option value="1" {{ old('is_urgent') == '1' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        <label for="is_urgent">
                            <i class="mdi mdi-alert me-1"></i> Niveau d'urgence <span class="text-danger">*</span>
                        </label>
                        @error('is_urgent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- TTO --}}
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number"
                               name="tto"
                               id="tto"
                               class="form-control @error('tto') is-invalid @enderror"
                               placeholder="TTO en heures"
                               value="{{ old('tto', 24) }}"
                               min="1"
                               required>
                        <label for="tto">
                            <i class="mdi mdi-clock-start me-1"></i> TTO (heures)
                            <span class="text-danger">*</span>
                        </label>
                        <div class="form-text text-muted">
                            <i class="mdi mdi-information me-1"></i>
                            Temps de prise en charge
                        </div>
                        @error('tto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- TTR --}}
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number"
                               name="ttr"
                               id="ttr"
                               class="form-control @error('ttr') is-invalid @enderror"
                               placeholder="TTR en heures"
                               value="{{ old('ttr', 72) }}"
                               min="1"
                               required>
                        <label for="ttr">
                            <i class="mdi mdi-clock-end me-1"></i> TTR (heures)
                            <span class="text-danger">*</span>
                        </label>
                        <div class="form-text text-muted">
                            <i class="mdi mdi-information me-1"></i>
                            Temps de résolution
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
                               {{ old('is_active', true) ? 'checked' : '' }}>
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
                    <i class="mdi mdi-check me-1"></i> Créer
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const unitSelect = document.getElementById('unit_id');
        const typeSelect = document.getElementById('type_id');
        const oldTypeId = "{{ old('type_id') }}";

        // Stocker toutes les options types (avec data-unit)
        const allTypeOptions = Array.from(typeSelect.querySelectorAll('option'))
            .filter(opt => opt.value !== '')
            .map(opt => opt.cloneNode(true));

        function filterTypes() {
            const selectedUnit = unitSelect.value;

            // Garder uniquement l'option "Par défaut"
            typeSelect.innerHTML = '<option value="">-- Par défaut (toute l\'unité) --</option>';

            if (!selectedUnit) return;

            allTypeOptions.forEach(opt => {
                if (opt.getAttribute('data-unit') == selectedUnit) {
                    const clone = opt.cloneNode(true);
                    if (oldTypeId && oldTypeId == clone.value) clone.selected = true;
                    typeSelect.appendChild(clone);
                }
            });
        }

        unitSelect.addEventListener('change', filterTypes);

        // Init au chargement (utile si old('unit_id') est défini après erreur de validation)
        filterTypes();
    });
</script>
@endpush

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
    }
</style>
@endpush
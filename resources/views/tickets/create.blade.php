@extends('layouts.app')

@section('title', 'Créer un ticket')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="mb-0 fw-bold" style="color: #1e293b;">
            <i class="mdi mdi-ticket-plus me-2" style="color: #3b82f6;"></i>
            Créer un ticket
        </h3>
        <p class="text-muted small mb-0">
            Remplissez les informations pour ouvrir une nouvelle réclamation
        </p>
    </div>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
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

<form method="POST"
      action="{{ route('tickets.store') }}"
      enctype="multipart/form-data"
      id="ticketForm">

    @csrf

    {{-- ============================================================ --}}
    {{-- SECTION 1 : INFORMATIONS CLIENT --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="mb-0 fw-semibold" style="color: #1e293b;">
                <i class="mdi mdi-account me-2" style="color: #3b82f6;"></i>
                Informations client
            </h5>
            <hr class="mt-2 mb-0" style="border-color: #e9ecef;">
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text"
                               name="client_name"
                               id="client_name"
                               class="form-control @error('client_name') is-invalid @enderror"
                               placeholder="Nom"
                               value="{{ old('client_name') }}"
                               required>
                        <label for="client_name">
                            <i class="mdi mdi-account me-1"></i> Nom <span class="text-danger">*</span>
                        </label>
                        @error('client_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text"
                               name="client_firstname"
                               id="client_firstname"
                               class="form-control @error('client_firstname') is-invalid @enderror"
                               placeholder="Prénom"
                               value="{{ old('client_firstname') }}">
                        <label for="client_firstname">
                            <i class="mdi mdi-account me-1"></i> Prénom
                        </label>
                        @error('client_firstname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="tel"
                               name="client_phone"
                               id="client_phone"
                               class="form-control @error('client_phone') is-invalid @enderror"
                               placeholder="Téléphone"
                               value="{{ old('client_phone') }}"
                               required>
                        <label for="client_phone">
                            <i class="mdi mdi-phone me-1"></i> Téléphone <span class="text-danger">*</span>
                        </label>
                        @error('client_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="tel"
                               name="client_whatsapp"
                               id="client_whatsapp"
                               class="form-control @error('client_whatsapp') is-invalid @enderror"
                               placeholder="WhatsApp"
                               value="{{ old('client_whatsapp') }}">
                        <label for="client_whatsapp">
                            <i class="mdi mdi-whatsapp me-1"></i> WhatsApp
                        </label>
                        @error('client_whatsapp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text"
                               name="client_contract_number"
                               id="client_contract_number"
                               class="form-control @error('client_contract_number') is-invalid @enderror"
                               placeholder="Numéro de contrat"
                               value="{{ old('client_contract_number') }}">
                        <label for="client_contract_number">
                            <i class="mdi mdi-file-document me-1"></i> Numéro de contrat
                        </label>
                        @error('client_contract_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text"
                               name="client_delivery_point"
                               id="client_delivery_point"
                               class="form-control @error('client_delivery_point') is-invalid @enderror"
                               placeholder="Point de livraison"
                               value="{{ old('client_delivery_point') }}">
                        <label for="client_delivery_point">
                            <i class="mdi mdi-map-marker me-1"></i> Point de livraison
                        </label>
                        @error('client_delivery_point')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION 2 : DÉTAILS DE LA RÉCLAMATION --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="mb-0 fw-semibold" style="color: #1e293b;">
                <i class="mdi mdi-ticket me-2" style="color: #3b82f6;"></i>
                Détails de la réclamation
            </h5>
            <hr class="mt-2 mb-0" style="border-color: #e9ecef;">
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="form-floating">
                        <select name="unit_id"
                                id="unit_id"
                                class="form-select @error('unit_id') is-invalid @enderror"
                                required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
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

                <div class="col-md-4">
                    <div class="form-floating">
                        <select name="type_id"
                                id="type_id"
                                class="form-select @error('type_id') is-invalid @enderror"
                                required>
                            <option value="">-- Sélectionner une unité --</option>
                        </select>
                        <label for="type_id">
                            <i class="mdi mdi-tag me-1"></i> Type <span class="text-danger">*</span>
                        </label>
                        @error('type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mt-2">
                        <button type="button"
                                class="btn btn-sm btn-outline-success rounded-pill px-3"
                                id="btnNewType">
                            <i class="mdi mdi-plus me-1"></i> Nouveau type
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                        <select name="agency_id"
                                id="agency_id"
                                class="form-select @error('agency_id') is-invalid @enderror"
                                required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}" {{ old('agency_id') == $agency->id ? 'selected' : '' }}>
                                    {{ $agency->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="agency_id">
                            <i class="mdi mdi-bank me-1"></i> Agence <span class="text-danger">*</span>
                        </label>
                        @error('agency_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-floating">
                        <textarea name="description"
                                  id="description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Description du problème"
                                  rows="5"
                                  style="min-height: 130px;"
                                  required>{{ old('description') }}</textarea>
                        <label for="description">
                            <i class="mdi mdi-text me-1"></i> Description <span class="text-danger">*</span>
                        </label>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="assigned_to"
                                id="assigned_to"
                                class="form-select @error('assigned_to') is-invalid @enderror">
                            <option value="">-- Sélectionner --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                        data-agency="{{ $user->agency_id }}"
                                        data-unit="{{ $user->unit_id }}"
                                        data-company="{{ $user->company_id }}"
                                        {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="assigned_to">
                            <i class="mdi mdi-account-badge me-1"></i> Responsable
                        </label>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="is_urgent"
                                id="is_urgent"
                                class="form-select @error('is_urgent') is-invalid @enderror">
                            <option value="0" {{ old('is_urgent', 0) == 0 ? 'selected' : '' }}>Normal</option>
                            <option value="1" {{ old('is_urgent') == 1 ? 'selected' : '' }}>Urgent</option>
                        </select>
                        <label for="is_urgent">
                            <i class="mdi mdi-alert me-1"></i> Niveau d'urgence
                        </label>
                        @error('is_urgent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION 3 : FICHE DE RÉCLAMATION (PIÈCE JOINTE) --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="mb-0 fw-semibold" style="color: #1e293b;">
                <i class="mdi mdi-paperclip me-2" style="color: #3b82f6;"></i>
                Fiche de réclamation
            </h5>
            <hr class="mt-2 mb-0" style="border-color: #e9ecef;">
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-floating">
                        <input type="file"
                               name="attachment_path"
                               id="attachment_path"
                               class="form-control @error('attachment_path') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png"
                               style="padding-top: 1.5rem; background-color: #f8fafc;">
                        <label for="attachment_path">
                            <i class="mdi mdi-file-upload me-1"></i> Pièce jointe
                        </label>
                        @error('attachment_path')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text text-muted mt-1">
                        <i class="mdi mdi-information me-1"></i>
                        Formats acceptés : PDF, JPG, JPEG, PNG. Taille maximale : 5 Mo.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- BOUTONS D'ACTION --}}
    {{-- ============================================================ --}}
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('tickets.index') }}"
           class="btn btn-outline-secondary rounded-pill px-4">
            <i class="mdi mdi-close me-1"></i> Annuler
        </a>
        <button type="submit"
                class="btn btn-primary rounded-pill px-5"
                style="background-color: #3b82f6; border-color: #3b82f6;">
            <i class="mdi mdi-check me-1"></i> Créer le ticket
        </button>
    </div>

</form>

{{-- ============================================================ --}}
{{-- MODAL : CRÉER UN NOUVEAU TYPE --}}
{{-- ============================================================ --}}
<div class="modal fade" id="newTypeModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold" style="color: #1e293b;">
                    <i class="mdi mdi-tag-plus me-2" style="color: #22c55e;"></i>
                    Créer un nouveau type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-2">
                <div class="alert alert-danger d-none rounded-3" id="typeModalError"></div>
                <div class="form-floating mb-3">
                    <input type="text"
                           id="newTypeName"
                           class="form-control"
                           placeholder="Nom du type"
                           style="border-radius: 0.5rem;">
                    <label for="newTypeName">
                        <i class="mdi mdi-tag me-1"></i> Nom du type
                    </label>
                </div>
                <p class="text-muted small mb-0">
                    <i class="mdi mdi-information me-1"></i>
                    Le type sera associé à l'unité actuellement sélectionnée.
                </p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="mdi mdi-close me-1"></i> Annuler
                </button>
                <button type="button" class="btn btn-success rounded-pill px-4" id="saveNewType" style="background-color: #22c55e; border-color: #22c55e;">
                    <i class="mdi mdi-check me-1"></i> Créer
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* --- Style général des cartes et champs --- */
    .card {
        border-radius: 1rem !important;
        background: #ffffff;
        transition: box-shadow 0.2s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1.2rem rgba(0, 0, 0, 0.04);
    }
    .card-header hr {
        opacity: 0.6;
    }

    .form-floating {
        margin-bottom: 0;
    }
    .form-floating > .form-control,
    .form-floating > .form-select {
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
    .form-floating > textarea.form-control {
        min-height: 130px;
    }
    .form-floating > input[type="file"] {
        height: calc(3.5rem + 2px);
        line-height: 1.6;
        padding-top: 1.5rem;
        padding-bottom: 0.5rem;
        background-color: #f8fafc;
        border-radius: 0.5rem;
    }
    .form-floating > input[type="file"] + label {
        padding-top: 0.5rem;
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

    .btn-outline-success {
        color: #22c55e;
        border-color: #22c55e;
    }
    .btn-outline-success:hover {
        background-color: #22c55e;
        color: #fff;
    }

    .btn-primary {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    .btn-primary:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    .btn-success {
        background-color: #22c55e;
        border-color: #22c55e;
    }
    .btn-success:hover {
        background-color: #16a34a;
        border-color: #16a34a;
    }

    .modal-content {
        border-radius: 1rem;
    }
    .modal-header {
        border-bottom: none;
    }
    .modal-footer {
        border-top: none;
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1.25rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============================================================
        // 1. FILTRAGE DES TYPES SELON L'UNITÉ
        // ============================================================
        const unitSelect = document.getElementById('unit_id');
        const typeSelect = document.getElementById('type_id');
        const allTypes = @json($types->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'unit_id' => $t->unit_id]));

        function filterTypes() {
            const selectedUnitId = unitSelect.value;
            const oldTypeId = "{{ old('type_id') }}";
            typeSelect.innerHTML = '<option value="">-- Sélectionner --</option>';
            if (!selectedUnitId) return;
            allTypes.forEach(type => {
                if (type.unit_id == selectedUnitId) {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    if (oldTypeId && oldTypeId == type.id) option.selected = true;
                    typeSelect.appendChild(option);
                }
            });
        }

        unitSelect.addEventListener('change', filterTypes);
        filterTypes();

        // ============================================================
        // 2. FILTRAGE DES SUPERVISEURS (ASSIGNÉ À)
        // ============================================================
        const agencySelect = document.getElementById('agency_id');
        const assignedToSelect = document.getElementById('assigned_to');
        const eneoCompanyId = {{ $eneoCompanyId ?? 0 }};
        const originalOptions = Array.from(assignedToSelect.querySelectorAll('option')).map(opt => opt.cloneNode(true));

        function filterSupervisors() {
            const selectedAgency = agencySelect.value;
            const selectedUnit = unitSelect.value;
            const oldAssigned = "{{ old('assigned_to') }}";

            assignedToSelect.innerHTML = '<option value="">-- Sélectionner --</option>';

            originalOptions.forEach(opt => {
                if (opt.value === '') return;
                const agencyId = opt.getAttribute('data-agency');
                const unitId = opt.getAttribute('data-unit');
                const companyId = opt.getAttribute('data-company');
                if ((!selectedAgency || agencyId == selectedAgency) &&
                    (!selectedUnit || unitId == selectedUnit) &&
                    companyId == eneoCompanyId) {
                    const clone = opt.cloneNode(true);
                    if (oldAssigned && oldAssigned == clone.value) clone.selected = true;
                    assignedToSelect.appendChild(clone);
                }
            });
        }

        agencySelect.addEventListener('change', filterSupervisors);
        unitSelect.addEventListener('change', function() {
            filterSupervisors();
            filterTypes();
        });
        filterSupervisors();

        // ============================================================
        // 3. CRÉATION D'UN NOUVEAU TYPE
        // ============================================================
        document.getElementById('btnNewType').addEventListener('click', function() {
            if (!document.getElementById('unit_id').value) {
                alert('Veuillez d’abord sélectionner une unité.');
                return;
            }
            document.getElementById('newTypeName').value = '';
            document.getElementById('typeModalError').classList.add('d-none');
            $('#newTypeModal').modal('show');
        });

        document.getElementById('saveNewType').addEventListener('click', function() {
            const unitId = document.getElementById('unit_id').value;
            const name = document.getElementById('newTypeName').value.trim();
            if (!name) {
                alert('Veuillez saisir un nom.');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Création...';

            fetch('{{ route("types.quick") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ unit_id: unitId, name: name })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Erreur serveur');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const typeSelect = document.getElementById('type_id');
                    const option = document.createElement('option');
                    option.value = data.type.id;
                    option.textContent = data.type.name;
                    typeSelect.appendChild(option);
                    typeSelect.value = data.type.id;
                    $('#newTypeModal').modal('hide');
                } else {
                    const errorDiv = document.getElementById('typeModalError');
                    errorDiv.textContent = data.message || 'Erreur lors de la création.';
                    errorDiv.classList.remove('d-none');
                }
            })
            .catch(error => {
                const errorDiv = document.getElementById('typeModalError');
                errorDiv.textContent = 'Erreur : ' + error.message;
                errorDiv.classList.remove('d-none');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="mdi mdi-check me-1"></i> Créer';
            });
        });
    });
</script>
@endpush
@extends('layouts.app')

@section('title', 'Créer un ticket')

@section('content')
<div class="page-header">
    <h3 class="page-title">Créer un ticket</h3>
</div>

<div class="card">
    <div class="card-body">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" id="ticketForm">
            @csrf

            {{-- SECTION 1 : INFORMATIONS CLIENT --}}
            <div class="card mb-4 border-secondary">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📋 Informations client</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Nom <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" class="form-control @error('client_name') is-invalid @enderror" value="{{ old('client_name') }}" required>
                            @error('client_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Prénom</label>
                            <input type="text" name="client_firstname" class="form-control @error('client_firstname') is-invalid @enderror" value="{{ old('client_firstname') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" name="client_phone" class="form-control @error('client_phone') is-invalid @enderror" value="{{ old('client_phone') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Numéro de contrat</label>
                            <input type="text" name="client_contract_number" class="form-control @error('client_contract_number') is-invalid @enderror" value="{{ old('client_contract_number') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Point de livraison</label>
                            <input type="text" name="client_delivery_point" class="form-control @error('client_delivery_point') is-invalid @enderror" value="{{ old('client_delivery_point') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2 : DÉTAILS DU TICKET --}}
            <div class="card mb-4 border-secondary">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🎫 Détails du ticket</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Unité <span class="text-danger">*</span></label>
                            <select name="unit_id" class="form-control @error('unit_id') is-invalid @enderror" id="unit_id" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Type <span class="text-danger">*</span></label>
                            <select name="type_id" class="form-control @error('type_id') is-invalid @enderror" id="type_id" required>
                                <option value="">-- Sélectionner d'abord une unité --</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Agence <span class="text-danger">*</span></label>
                            <select name="agency_id" class="form-control @error('agency_id') is-invalid @enderror" id="agency_id" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($agencies as $agency)
                                    <option value="{{ $agency->id }}" {{ old('agency_id') == $agency->id ? 'selected' : '' }}>{{ $agency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col-md-6 mb-3">
                            <label>Priorité <span class="text-danger">*</span></label>
                            <select name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                <option value="LOW" {{ old('priority') == 'LOW' ? 'selected' : '' }}>Faible</option>
                                <option value="MEDIUM" {{ old('priority') == 'MEDIUM' ? 'selected' : '' }}>Moyenne</option>
                                <option value="HIGH" {{ old('priority') == 'HIGH' ? 'selected' : '' }}>Grande</option>
                                <option value="CRITICAL" {{ old('priority') == 'CRITICAL' ? 'selected' : '' }}>Critique</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Responsable</label>
                            <select name="assigned_to" class="form-control @error('assigned_to') is-invalid @enderror" id="assigned_to">
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
                            <!-- <small class="text-muted">Seuls les superviseurs ENEO de l’agence et de l’unité sélectionnées sont affichés.</small> -->
                        </div>
                        </div>
                </div>
            </div>

            {{-- SECTION 4 : PIÈCE JOINTE --}}
            <div class="card mb-4 border-secondary">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📎 Fiche de réclamation</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>Fichier <span class="text-danger">*</span></label>
                            <input type="file" name="attachment_path" class="form-control-file @error('attachment_path') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Format accepté : PDF, JPG, PNG. Taille max : 5 Mo.</small>
                            @error('attachment_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary px-4">Créer le ticket</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Annuler</a>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtrage des types selon unité
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

        // Filtrage des superviseurs (assigné à) selon agence + unité + ENEO
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
    });
</script>
@endpush
@endsection
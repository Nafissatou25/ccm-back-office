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

        <form method="POST"
      action="{{ route('tickets.store') }}"
      enctype="multipart/form-data"
      id="ticketForm">

    @csrf

    <!-- ========================= -->
    <!-- INFORMATIONS CLIENT -->
    <!-- ========================= -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="mdi mdi-account"></i>
                Informations client
            </h5>
        </div>

        <div class="card-body">
            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Nom <span class="text-danger">*</span></label>
                    <input type="text"
                           name="client_name"
                           class="form-control"
                           value="{{ old('client_name') }}"
                           required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Prénom</label>
                    <input type="text"
                           name="client_firstname"
                           class="form-control"
                           value="{{ old('client_firstname') }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Téléphone <span class="text-danger">*</span></label>
                    <input type="tel"
                           name="client_phone"
                           class="form-control"
                           value="{{ old('client_phone') }}"
                           required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>WhatsApp</label>
                    <input type="tel"
                           name="client_whatsapp"
                           class="form-control"
                           value="{{ old('client_whatsapp') }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Numéro contrat</label>
                    <input type="text"
                           name="client_contract_number"
                           class="form-control"
                           value="{{ old('client_contract_number') }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Point de livraison</label>
                    <input type="text"
                           name="client_delivery_point"
                           class="form-control"
                           value="{{ old('client_delivery_point') }}">
                </div>

            </div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- RECLAMATION -->
    <!-- ========================= -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0">
                <i class="mdi mdi-ticket"></i>
                Détails de la réclamation
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Unité <span class="text-danger">*</span></label>
                    <select name="unit_id"
                            id="unit_id"
                            class="form-control"
                            required>

                        <option value="">Sélectionner</option>

                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">
                                {{ $unit->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Type <span class="text-danger">*</span></label>

                    <div class="input-group">

                        <select name="type_id"
                                id="type_id"
                                class="form-control"
                                required>

                            <option value="">
                                Sélectionner une unité
                            </option>

                        </select>

                        <div class="input-group-append">
                            <button type="button"
                                    class="btn btn-success"
                                    id="btnNewType">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </div>

                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Agence <span class="text-danger">*</span></label>

                    <select name="agency_id"
                            id="agency_id"
                            class="form-control"
                            required>

                        <option value="">Sélectionner</option>

                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}">
                                {{ $agency->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

            </div>

            <div class="row">

                <div class="col-md-12 mb-3">
                    <label>Description <span class="text-danger">*</span></label>

                    <textarea name="description"
                              rows="5"
                              class="form-control"
                              required>{{ old('description') }}</textarea>
                </div>

            </div>

            <div class="row">

                <div class="col-md-6">

                    <label>Responsable</label>

                    <select name="assigned_to"
                            id="assigned_to"
                            class="form-control">

                        <option value="">
                            Sélectionner
                        </option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    data-agency="{{ $user->agency_id }}"
                                    data-unit="{{ $user->unit_id }}"
                                    data-company="{{ $user->company_id }}">

                                {{ $user->name }}

                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="col-md-6">

                    <label>Niveau d'urgence</label>

                    <div>

                        <div class="form-check">

                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_urgent"
                                   value="1"
                                   id="is_urgent">
                                   <label class="form-check-label"
                                   for="is_urgent">

                                Urgent ?

                            </label>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- ========================= -->
    <!-- PIECE JOINTE -->
    <!-- ========================= -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="mdi mdi-paperclip"></i>
                Fiche de réclamation
            </h5>
        </div>

        <div class="card-body">

            <div class="form-group">

                <label>Pièce jointe</label>

                <input type="file"
                       name="attachment_path"
                       class="form-control-file"
                       accept=".pdf,.jpg,.jpeg,.png">

                <small class="text-muted">
                    PDF, JPG ou PNG (5 Mo max)
                </small>

            </div>

        </div>
    </div>

    <!-- ========================= -->
    <!-- BOUTONS -->
    <!-- ========================= -->
    <div class="text-right">

        <a href="{{ route('tickets.index') }}"
           class="btn btn-light border">

            Annuler

        </a>

        <button type="submit"
                class="btn btn-primary px-5">

            <i class="mdi mdi-content-save"></i>
            Créer le ticket

        </button>

    </div>

</form>
    </div>
</div>

<!-- Modal Nouveau type -->
<div class="modal fade" id="newTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Créer un nouveau type</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="typeModalError"></div>
                <div class="form-group">
                    <label>Nom du type</label>
                    <input type="text" id="newTypeName" class="form-control" placeholder="Ex: Panne électrique">
                    <small class="text-muted">Le type sera associé à l’unité actuellement sélectionnée.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveNewType">Créer</button>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.form-control,
.custom-select {
    border-radius: 8px;
}

.btn {
    border-radius: 8px;
}
</style>

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

    // Nouveau type
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
    btn.innerHTML = 'Création...';

    fetch('{{ route("types.quick") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ unit_id: unitId, name: name })
    })
    .then(response => response.json())
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
        errorDiv.textContent = 'Erreur réseau : ' + error;
        errorDiv.classList.remove('d-none');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Créer';
    });
});
</script>
@endpush
@endsection
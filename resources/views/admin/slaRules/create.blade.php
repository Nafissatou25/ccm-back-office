@extends('layouts.app')
@section('title', 'Créer SLA')
@section('content')

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<h3 class="mb-4">Créer une règle SLA</h3>
<div class="card">
    <div class="card-body">
        

        <form method="POST" action="{{ route('admin.slaRules.store') }}">
            @csrf
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Unité <span class="text-danger">*</span></label>
                    <select name="unit_id" id="unit_id" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Type
                    </label>
                    <select name="type_id" id="type_id" class="form-control">
                        <option value="">-- Par défaut --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}"
                                    data-unit="{{ $type->unit_id }}"
                                    {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Niveau d'urgence <span class="text-danger">*</span></label>
                    <select name="is_urgent" class="form-control" required>
                        <option value="0" {{ old('is_urgent') == '0' ? 'selected' : '' }}>Normal</option>
                        <option value="1" {{ old('is_urgent') == '1' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label>TTO (heures) <span class="text-danger">*</span>
                        <small class="text-muted d-block fw-normal">Temps de prise en charge</small>
                    </label>
                    <input type="number" name="tto" class="form-control"
                           value="{{ old('tto', 24) }}" min="1" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label>TTR (heures) <span class="text-danger">*</span>
                        <small class="text-muted d-block fw-normal">Temps de résolution</small>
                    </label>
                    <input type="number" name="ttr" class="form-control"
                           value="{{ old('ttr', 72) }}" min="1" required>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input"
                               name="is_active" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Actif</label>
                    </div>
                </div>

            </div>

            <button class="btn btn-primary">Créer</button>
            <a href="{{ route('admin.slaRules.index') }}" class="btn btn-light border ml-2">Annuler</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const unitSelect  = document.getElementById('unit_id');
    const typeSelect  = document.getElementById('type_id');
    const oldTypeId   = "{{ old('type_id') }}";

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
</script>
@endpush

@endsection
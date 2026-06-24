@extends('layouts.app')
@section('title', 'Demande WhatsApp')
@section('content')

@php $ref = '#WA-' . str_pad($whatsappRequest->id, 4, '0', STR_PAD_LEFT); @endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Demande {{ $ref }}</h3>
    @if($whatsappRequest->status === 'CONVERTED')
        <a href="{{ route('tickets.show', $whatsappRequest->ticket_id) }}" class="btn btn-success">
            Voir le ticket →
        </a>
    @endif
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white fw-semibold">Informations client</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Nom</th><td>{{ $whatsappRequest->full_name }}</td></tr>
                    <tr><th>N° contrat</th><td>{{ $whatsappRequest->contract_number ?? '—' }}</td></tr>
                    <tr><th>Téléphone</th><td>{{ $whatsappRequest->contact_phone }}</td></tr>
                    <tr><th>WhatsApp</th><td>{{ $whatsappRequest->wa_phone }}</td></tr>
                    <tr><th>Localisation</th><td>{{ $whatsappRequest->location_hint }}</td></tr>
                    <tr><th>Reçu le</th><td>{{ $whatsappRequest->created_at->format('d/m/Y à H:i') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white fw-semibold">Description du problème</div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-wrap;">{{ $whatsappRequest->description }}</p>
            </div>
        </div>
    </div>

    @if($whatsappRequest->status === 'COMPLETED')
    <div class="col-12">
        <div class="card border-warning shadow-sm rounded-4">
            <div class="card-header bg-warning bg-opacity-10 fw-semibold">Convertir en ticket</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.whatsapp.convert', $whatsappRequest) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Unité <span class="text-danger">*</span></label>
                            <select name="unit_id" id="unit_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type_id" id="type_id" class="form-select" required>
                                <option value="">-- Sélectionner d'abord une unité --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Agence <span class="text-danger">*</span></label>
                            <select name="agency_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($agencies as $agency)
                                    <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_urgent" value="1" class="form-check-input" id="is_urgent">
                                <label class="form-check-label" for="is_urgent">Marquer comme urgent</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Créer le ticket + générer PDF</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    const allTypes = @json(\App\Models\Type::all()->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'unit_id' => $t->unit_id]));

    document.getElementById('unit_id').addEventListener('change', function () {
        const unitId = this.value;
        const typeSelect = document.getElementById('type_id');
        typeSelect.innerHTML = '<option value="">-- Sélectionner --</option>';
        allTypes.filter(t => t.unit_id == unitId).forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            typeSelect.appendChild(opt);
        });
    });
</script>
@endpush
@endsection
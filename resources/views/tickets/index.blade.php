@extends('layouts.app')

@section('title', 'Tickets')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Liste des tickets</h3>

    <!-- <a href="{{ route('tickets.create') }}" class="btn btn-primary">
        + Nouveau ticket
    </a> -->

@php
    $role = strtolower(auth()->user()->role?->name);
@endphp

@if(in_array($role, ['manager', 'customer_service', 'supervisor']))
    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
        + Nouveau ticket
    </a>
@endif
</div>

{{-- CARTES STATUTS (comptes rapides) --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    @php
        $cards = [
            
            ['label' => 'Ouverts', 'value' => $stats['open'], 'color' => 'warning', 'icon' => 'mdi-alert-circle'],
            ['label' => 'En cours', 'value' => $stats['inProgress'], 'color' => 'info', 'icon' => 'mdi-progress-clock'],
            ['label' => 'Transférés', 'value' => $stats['transferred'], 'color' => 'secondary', 'icon' => 'mdi-swap-horizontal'],
            ['label' => 'En attente', 'value' => $stats['onHold'], 'color' => 'dark', 'icon' => 'mdi-pause-circle'],
            ['label' => 'Réouverts', 'value' => $stats['reopened'], 'color' => 'danger', 'icon' => 'mdi-repeat'],
            ['label' => 'Résolus', 'value' => $stats['resolved'], 'color' => 'success', 'icon' => 'mdi-check-circle'],
            ['label' => 'Clôturés', 'value' => $stats['closed'], 'color' => 'secondary', 'icon' => 'mdi-lock'],
            ['label' => 'En retard', 'value' => $stats['late'], 'color' => 'danger', 'icon' => 'mdi-alarm-check'],
            ['label' => 'Total', 'value' => $stats['total'], 'color' => 'primary', 'icon' => 'mdi-ticket'],
        ];
    @endphp
    @foreach($cards as $card)
        <div class="card border-0 shadow-sm" style="min-width: 100px; flex: 1 0 auto; border-radius: 12px;">
            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase">{{ $card['label'] }}</span>
                    <h4 class="fw-bold mb-0">{{ $card['value'] }}</h4>
                </div>
                <i class="mdi {{ $card['icon'] }} fs-4 text-{{ $card['color'] }}"></i>
            </div>
        </div>
    @endforeach
</div>
<div class="card">

    <div class="card-body">

    <div class="px-4 py-4">

        <!-- {{-- Barre de filtre (inspirée du dashboard) --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <form method="GET" action="{{ route('tickets.index') }}" class="d-flex gap-2 flex-wrap align-items-end bg-white p-2 rounded-3 shadow-sm">
                <div>
                    <label class="form-label small text-muted mb-0">Du</label>
                    <input type="date" name="start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}" class="form-control form-control-sm border-0 bg-light">
                </div>
                <div>
                    <label class="form-label small text-muted mb-0">Au</label>
                    <input type="date" name="end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}" class="form-control form-control-sm border-0 bg-light">
                </div>
                @if($role !== 'MANAGER')
                <div>
                    <select name="unit_id" class="form-select form-select-sm border-0 bg-light" id="unitSelect">
                        <option value="">Toutes unités</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($role !== 'MANAGER')
                <div>
                    <select name="type_id" class="form-select form-select-sm border-0 bg-light" id="typeSelect">
                        <option value="">Tous types</option>
                        @foreach($eligibleTypes as $type)
                            <option value="{{ $type->id }}" {{ $selectedTypeId == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button class="btn btn-sm btn-primary rounded-pill px-3"><i class="mdi mdi-filter-outline"></i> Filtrer</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-secondary rounded-pill px-3">Réinitialiser</a>
            </form>
        </div> -->
        <div class="table-responsive">
    {{-- Barre de recherche --}}
    <div class="mb-3 d-flex justify-content-between align-items-center">
    <div class="position-relative" style="width: 280px;">
        <i class="mdi mdi-magnify position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1.1rem;"></i>
        <input type="text" id="ticketSearch" class="form-control rounded-pill ps-5" placeholder="Rechercher un ticket..." style="background-color: #f8f9fa; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    </div>
</div>
    <div style="max-height: 70vh; overflow-y: auto; border-radius: 0.5rem;">
        <table class="table table-hover align-middle" id="ticketsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Unité</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                @php
                    $statusColors = [
                        'OPEN' => 'warning',
                        'IN_PROGRESS' => 'info',
                        'TRANSFERRED' => 'secondary',
                        'ON_HOLD' => 'dark',
                        'REOPENED' => 'danger',
                        'RESOLVED' => 'success',
                        'CLOSED' => 'secondary',
                        'ASSIGNED_TO_TECHNICIANS' => 'info',   // optionnel, on garde info
                    ];
                    $statusLabels = [
                        'OPEN' => 'Ouvert',
                        'ASSIGNED_TO_TECHNICIANS' => 'Assigné',
                        'IN_PROGRESS' => 'En cours',
                        'ON_HOLD' => 'En attente',
                        'RESOLVED' => 'Résolu',
                        'CLOSED' => 'Clôturé',
                        'REOPENED' => 'Réouvert',
                        'TRANSFERRED' => 'Transféré'
                    ];
                @endphp
                <tr class="ticket-row">
                    <td><strong>#{{ $ticket->id }}</strong></td>
                    <td>{{ $ticket->unit->name ?? '-' }}</td>
                    <td>{{ $ticket->type->name ?? '-' }}</td>
                    <td style="max-width: 300px;">
                        <div class="text-truncate">{{ $ticket->description }}</div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                            {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">Voir</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('ticketSearch').addEventListener('keyup', function() {
        let searchTerm = this.value.toLowerCase();
        let rows = document.querySelectorAll('#ticketsTable tbody .ticket-row');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>

    </div>
</div>
@push('scripts')
<script>
    // Auto‑soumission au changement d'unité ou de type
    document.getElementById('unitSelect')?.addEventListener('change', () => document.querySelector('form[action="{{ route('tickets.index') }}"]').submit());
    document.getElementById('typeSelect')?.addEventListener('change', () => document.querySelector('form[action="{{ route('tickets.index') }}"]').submit());
</script>
@endpush
@endsection
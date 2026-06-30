@extends('layouts.app')

@section('title', 'Tickets')

@section('content')

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="mb-0 fw-bold">
            <i class="mdi mdi-ticket me-2 text-primary"></i>
            Liste des tickets
        </h3>
        <p class="text-muted small mb-0">
            {{ $stats['total'] }} ticket(s) au total
        </p>
    </div>
    <div class="d-flex gap-2">
    @php $role = strtolower(auth()->user()->role?->name); @endphp
    @if(in_array($role, ['admin']))
            <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4">
                <i class="mdi mdi-view-dashboard"></i> Tableau de bord
            </a>
        @endif
    @if(in_array($role, ['manager', 'customer_service', 'supervisor', 'admin']))
        <a href="{{ route('tickets.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="mdi mdi-plus me-1"></i> Nouveau ticket
        </a>
    @endif
    </div>
</div>

{{-- CARTES STATUTS (cliquables) --}}
 <!-- <div class="d-flex flex-wrap gap-3 mb-4">
    @php
        $currentParams = request()->except('status', 'late');
        $cards = [
            ['label' => 'Ouverts',    'value' => $stats['open'],        'color' => '#3b82f6', 'icon' => 'mdi-alert-circle',    'status' => 'OPEN'],
            ['label' => 'En cours',   'value' => $stats['inProgress'],  'color' => '#8b5cf6', 'icon' => 'mdi-progress-clock',  'status' => 'IN_PROGRESS'],
            ['label' => 'Transférés', 'value' => $stats['transferred'], 'color' => '#6b7280', 'icon' => 'mdi-swap-horizontal', 'status' => 'TRANSFERRED'],
            ['label' => 'En attente', 'value' => $stats['onHold'],      'color' => '#f59e0b', 'icon' => 'mdi-pause-circle',    'status' => 'ON_HOLD'],
            ['label' => 'Réouverts',  'value' => $stats['reopened'],    'color' => '#ef4444', 'icon' => 'mdi-repeat',          'status' => 'REOPENED'],
            ['label' => 'Résolus',    'value' => $stats['resolved'],    'color' => '#10b981', 'icon' => 'mdi-check-circle',    'status' => 'RESOLVED'],
            ['label' => 'Clôturés',   'value' => $stats['closed'],      'color' => '#6b7280', 'icon' => 'mdi-lock',            'status' => 'CLOSED'],
            ['label' => 'En retard',  'value' => $stats['late'],        'color' => '#dc2626', 'icon' => 'mdi-alarm-check',     'late'   => 1],
            ['label' => 'Total',      'value' => $stats['total'],       'color' => '#1e293b', 'icon' => 'mdi-ticket',          'status' => null],
        ];
        $currentStatus = request('status');
        $currentLate   = request('late');
    @endphp

    @foreach($cards as $card)
        @php
            $params = $currentParams;
            if (isset($card['late'])) {
                $params['late'] = 1;
                unset($params['status']);
            } elseif ($card['status'] === null) {
                unset($params['status'], $params['late']);
            } else {
                $params['status'] = $card['status'];
                unset($params['late']);
            }
            $url        = route('tickets.index', $params);
            $cardStatus = $card['status'] ?? null;
            $isActive   = (isset($card['late']) && $currentLate == 1)
                        || ($cardStatus !== null && $currentStatus == $cardStatus)
                        || ($cardStatus === null && empty($currentStatus) && empty($currentLate));
        @endphp
        <a href="{{ $url }}"
           class="status-card text-decoration-none {{ $isActive ? 'active' : '' }}"
           style="min-width:120px; flex:1 0 auto;">
            <div class="card-content">
                <div class="card-icon" style="background: {{ $card['color'] }}20; color: {{ $card['color'] }};">
                    <i class="mdi {{ $card['icon'] }}"></i>
                </div>
                <div class="card-info">
                    <span class="card-label">{{ $card['label'] }}</span>
                    <span class="card-value">{{ $card['value'] }}</span>
                </div>
            </div>
        </a>
    @endforeach
</div>

<style>
    .status-card {
        display: block;
        background: #ffffff;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
        border: 1px solid #f1f5f9;
    }
    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06), 0 2px 4px rgba(0,0,0,0.04);
        border-color: #e2e8f0;
    }
    .status-card.active {
        border-color: #3b82f6;
        box-shadow: 0 0 0 1px #3b82f6, 0 4px 12px rgba(59,130,246,0.10);
    }
    .status-card .card-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .status-card .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
        transition: background 0.2s;
    }
    .status-card:hover .card-icon {
        transform: scale(1.05);
    }
    .status-card .card-info {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
    }
    .status-card .card-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #94a3b8;
        font-weight: 600;
    }
    .status-card .card-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .status-card.active .card-value {
        color: #1e293b;
    }

    /* Responsive : sur très petits écrans, les cartes prennent plus de place */
    @media (max-width: 480px) {
        .status-card {
            min-width: 80px !important;
            flex: 1 1 45% !important;
            padding: 0.6rem 0.8rem;
        }
        .status-card .card-icon {
            width: 32px;
            height: 32px;
            font-size: 1.1rem;
        }
        .status-card .card-value {
            font-size: 1.2rem;
        }
    }
</style>  -->

{{-- TABLEAU DES TICKETS --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">

        {{-- BARRE DE RECHERCHE ET FILTRES --}}
        <div class="d-flex flex-wrap gap-3 align-items-center p-3 border-bottom bg-light rounded-top-4">
            <div class="position-relative flex-grow-1" style="max-width:320px;">
                <i class="mdi mdi-magnify position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="ticketSearch"
                       class="form-control rounded-pill ps-5"
                       placeholder="Rechercher un ticket..."
                       style="background:#fff; border:1px solid #e9ecef; box-shadow:none;">
            </div>

            <div class="d-flex gap-2 ms-auto">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary rounded-pill dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="mdi mdi-filter me-1"></i> Filtrer
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:220px;">
                        <div class="mb-2">
                            <label class="form-label small">Statut</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                @foreach(['OPEN','IN_PROGRESS','TRANSFERRED','ON_HOLD','REOPENED','RESOLVED','CLOSED'] as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ $statusLabels[$status] ?? $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Unité</label>
                            <select id="filterUnit" class="form-select form-select-sm">
                                <option value="">Toutes</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Type</label>
                            <select id="filterType" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                @foreach($eligibleTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Agence</label>
                            <select id="filterAgency" class="form-select form-select-sm">
                                <option value="">Toutes</option>
                                @foreach($agencies as $agency)
                                    <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                        {{ $agency->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button id="applyFilters" class="btn btn-primary btn-sm w-100 rounded-pill">Appliquer</button>
                    </div>
                </div>
                <button id="clearFilters" class="btn btn-outline-secondary rounded-pill btn-sm">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>
        </div>

        {{-- TABLEAU --}}
        <div class="table-responsive">
            <div style="max-height:70vh; overflow-y:auto; border-radius:0 0 0.75rem 0.75rem;">
                <table class="table table-hover align-middle mb-0" id="ticketsTable">
                    <thead class="bg-light" style="position:sticky; top:0; z-index:5;">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-hash me-1"></i> ID
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-account me-1"></i> Client
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">
                                <i class="mdi mdi-tag me-1"></i> Type
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">
                                <i class="mdi mdi-clock-end me-1"></i> Échéance
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">
                                <i class="mdi mdi-check-circle me-1"></i> Statut
                            </th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-end pe-4">
                                <i class="mdi mdi-eye me-1"></i> Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            @php
                                $statusColors = [
                                    'OPEN'        => 'warning',
                                    'IN_PROGRESS' => 'info',
                                    'TRANSFERRED' => 'secondary',
                                    'ON_HOLD'     => 'dark',
                                    'REOPENED'    => 'orange',
                                    'RESOLVED'    => 'success',
                                    'CLOSED'      => 'secondary',
                                ];
                                $statusLabels = [
                                    'OPEN'        => 'Ouvert',
                                    'IN_PROGRESS' => 'En cours',
                                    'TRANSFERRED' => 'Transféré',
                                    'ON_HOLD'     => 'En attente',
                                    'REOPENED'    => 'Réouvert',
                                    'RESOLVED'    => 'Résolu',
                                    'CLOSED'      => 'Clôturé',
                                ];
                                $isLate = $ticket->resolution_due_at
                                    && now()->greaterThan($ticket->resolution_due_at)
                                    && !in_array($ticket->status, ['RESOLVED', 'CLOSED']);
                                $isNew = $ticket->views->isEmpty();
                                $isUrgent = $ticket->is_urgent === 1;
                            @endphp
                            <tr class="border-bottom ticket-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-light text-dark fw-normal">#{{ $ticket->id }}</span>
                                        @if($isNew)
                                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:0.6rem; padding:0.25rem 0.5rem;">New</span>
                                        @endif
                                        @if($isUrgent)
                                            <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:0.6rem; padding:0.25rem 0.5rem;">
                                                <i class="mdi mdi-alert me-1"></i>Urgent
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($ticket->client)
                                        <span class="d-flex align-items-center">
                                            
                                            {{ $ticket->client->name }} {{ $ticket->client->firstname  }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->type->name ?? '—' }}</td>
                                <td class="text-center">
                                    @if($ticket->resolution_due_at)
                                        <span class="{{ $isLate ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ $ticket->resolution_due_at->format('d/m/Y H:i') }}
                                        </span>
                                        @if($isLate)
                                            <i class="mdi mdi-alarm text-danger ms-1" style="font-size:14px;"></i>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$ticket->status] ?? 'secondary' }} px-3 py-2 rounded-pill">
                                        {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('tickets.show', $ticket->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="mdi mdi-eye me-1"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-ticket-off text-muted" style="font-size:48px;"></i>
                                        <p class="text-muted mt-3 mb-0">Aucun ticket trouvé.</p>
                                        @if(in_array($role, ['manager', 'customer_service', 'supervisor', 'admin']))
                                            <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm mt-2 rounded-pill">
                                                <i class="mdi mdi-plus me-1"></i> Créer un ticket
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($tickets) && method_exists($tickets, 'links'))
            <div class="d-flex justify-content-center p-3 border-top bg-light rounded-bottom-4">
                {{ $tickets->links() }}
            </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Recherche en temps réel
        const searchInput = document.getElementById('ticketSearch');
        const rows = document.querySelectorAll('#ticketsTable tbody .ticket-row');

        searchInput.addEventListener('keyup', function() {
            const term = this.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });

        // Filtres dropdown
        document.getElementById('applyFilters').addEventListener('click', function() {
            const status = document.getElementById('filterStatus').value;
            const unit = document.getElementById('filterUnit').value;
            const type = document.getElementById('filterType').value;
            const agency = document.getElementById('filterAgency').value
            let url = '{{ route("tickets.index") }}?';
            const params = [];
            if (status) params.push('status=' + status);
            if (unit) params.push('unit_id=' + unit);
            if (type) params.push('type_id=' + type);
            if (agency) params.push('agency_id=' + agency);
            // Keep existing date filters, etc.
            const currentParams = new URLSearchParams(window.location.search);
            for (let [key, value] of currentParams) {
                if (!['status', 'unit_id', 'type_id'].includes(key)) {
                    params.push(key + '=' + value);
                }
            }
            window.location.href = url + params.join('&');
        });

        document.getElementById('clearFilters').addEventListener('click', function() {
            window.location.href = '{{ route("tickets.index") }}';
        });
    });
</script>
@endpush

@push('styles')
<style>
    .bg-warning-subtle { background-color: #fff3cd !important; }
    .bg-danger-subtle { background-color: #f8d7da !important; }
    .bg-success-subtle { background-color: #d1e7dd !important; }
    .bg-info-subtle { background-color: #cff4fc !important; }
    .bg-primary-subtle { background-color: #cfe2ff !important; }
    .bg-secondary-subtle { background-color: #e9ecef !important; }

    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-success { color: #198754 !important; }
    .text-info { color: #0dcaf0 !important; }
    .text-primary { color: #0d6efd !important; }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .rounded-4 { border-radius: 0.75rem !important; }
    .rounded-top-4 { border-top-left-radius: 0.75rem !important; border-top-right-radius: 0.75rem !important; }
    .rounded-bottom-4 { border-bottom-left-radius: 0.75rem !important; border-bottom-right-radius: 0.75rem !important; }

    .btn-outline-warning { border-color: #ffc107; color: #ffc107; }
    .btn-outline-warning:hover { background-color: #ffc107; color: #fff; }
</style>
@endpush
@endsection
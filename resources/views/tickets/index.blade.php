@extends('layouts.app')
@section('title', 'Tickets')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Liste des tickets</h3>
    @php $role = strtolower(auth()->user()->role?->name); @endphp
    @if(in_array($role, ['manager', 'customer_service', 'supervisor', 'admin']))
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">+ Nouveau ticket</a>
    @endif
</div>

{{-- CARTES STATUTS --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    @php
        $currentParams = request()->except('status', 'late');
        $cards = [
            ['label' => 'Ouverts',    'value' => $stats['open'],        'color' => 'warning',  'icon' => 'mdi-alert-circle',    'status' => 'OPEN'],
            ['label' => 'En cours',   'value' => $stats['inProgress'],  'color' => 'info',     'icon' => 'mdi-progress-clock',  'status' => 'IN_PROGRESS'],
            ['label' => 'Transférés', 'value' => $stats['transferred'], 'color' => 'secondary','icon' => 'mdi-swap-horizontal', 'status' => 'TRANSFERRED'],
            ['label' => 'En attente', 'value' => $stats['onHold'],      'color' => 'dark',     'icon' => 'mdi-pause-circle',    'status' => 'ON_HOLD'],
            ['label' => 'Réouverts',  'value' => $stats['reopened'],    'color' => 'orange',   'icon' => 'mdi-repeat',          'status' => 'REOPENED'],
            ['label' => 'Résolus',    'value' => $stats['resolved'],    'color' => 'success',  'icon' => 'mdi-check-circle',    'status' => 'RESOLVED'],
            ['label' => 'Clôturés',   'value' => $stats['closed'],      'color' => 'secondary','icon' => 'mdi-lock',            'status' => 'CLOSED'],
            ['label' => 'En retard',  'value' => $stats['late'],        'color' => 'danger',   'icon' => 'mdi-alarm-check',     'late'   => 1],
            ['label' => 'Total',      'value' => $stats['total'],       'color' => 'primary',  'icon' => 'mdi-ticket',          'status' => null],
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
           class="card border-0 shadow-sm text-decoration-none {{ $isActive ? 'border border-primary' : '' }}"
           style="min-width:100px; flex:1 0 auto; border-radius:12px;">
            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase">{{ $card['label'] }}</span>
                    <h4 class="fw-bold mb-0">{{ $card['value'] }}</h4>
                </div>
                <i class="mdi {{ $card['icon'] }} fs-4 text-{{ $card['color'] }}"></i>
            </div>
        </a>
    @endforeach
</div>

<div class="card">
    <div class="card-body px-4 py-4">

        {{-- Barre de recherche --}}
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div class="position-relative" style="width:280px;">
                <i class="mdi mdi-magnify position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="ticketSearch"
                       class="form-control rounded-pill ps-5"
                       placeholder="Rechercher un ticket..."
                       style="background:#f8f9fa; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            </div>
        </div>

        <div class="table-responsive" style="max-height:70vh; overflow-y:auto; border-radius:0.5rem;">
            <table class="table table-hover align-middle" id="ticketsTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Unité</th>
                        <th>Type</th>
                        
                        <th>Délai de résolution</th>
                        <th>Statut</th>
                        <th>Action</th>
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
                                'ON_HOLD'     => 'En attente',
                                'RESOLVED'    => 'Résolu',
                                'CLOSED'      => 'Clôturé',
                                'REOPENED'    => 'Réouvert',
                                'TRANSFERRED' => 'Transféré',
                            ];

                            // Ticket en retard
                            $isLate = $ticket->resolution_due_at
                                && now()->greaterThan($ticket->resolution_due_at)
                                && !in_array($ticket->status, ['RESOLVED', 'CLOSED']);

                            // Ticket nouveau (jamais consulté) — basé sur viewed_at ou created_at < 24h sans activité
                            $isNew = $ticket->views->isEmpty();

                            $isUrgent = $ticket->is_urgent === 1;

                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $ticket->id }}</strong>
                                @if($isNew)
                                    <span class="badge bg-primary ms-1" style="font-size:10px;">New</span>
                                @endif
                                @if($isUrgent)
                                    <span class="badge bg-danger ms-1" style="font-size:10px;">
                                         Urgent
                                    </span>
                                @endif
                            </td>
                            <td>{{ $ticket->unit->name ?? '—' }}</td>
                            <td>{{ $ticket->type->name ?? '—' }}</td>
                            
                            
                            
                            <td>
                                @if($ticket->resolution_due_at)
                                    <span class="{{ $isLate ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ $ticket->resolution_due_at->format('d/m/Y H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket->id) }}"
                                   class="btn btn-sm btn-primary">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Aucun ticket trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Recherche
    document.getElementById('ticketSearch').addEventListener('keyup', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#ticketsTable tbody .ticket-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>
@endpush

@endsection
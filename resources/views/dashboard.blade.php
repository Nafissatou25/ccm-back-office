@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="content-wrapper">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="fw-bold mb-1">Dashboard</h3>
            <p>Suivi des performances et statistiques des tickets</p>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2 flex-wrap align-items-end">
            <div>
                <label class="form-label small">Du</label>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control">
            </div>
            <div>
                <label class="form-label small">Au</label>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control">
            </div>
            @if($role !== 'manager')
            <div>
                <label class="form-label small">Unité</label>
                <select name="unit_id" class="form-select">
                    <option value="">Toutes les unités</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            @if($role !== 'manager')
<div>
    <label class="form-label small">Type</label>
    <select name="type_id" class="form-select">
        <option value="">Tous les types</option>
        @foreach($types as $type)
            <option value="{{ $type->id }}" {{ $selectedTypeId == $type->id ? 'selected' : '' }}>
                {{ $type->name }}
            </option>
        @endforeach
    </select>
</div>
@endif
            <div>
                <button class="btn btn-primary"><i class="mdi mdi-filter"></i> Filtrer</button>
            </div>
        </form>
    </div>

    {{-- CARDS STATS --}}
    {{-- PREMIÈRE LIGNE DE CARTES --}}
<div class="row">
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card shadow border-0 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Total Tickets</h6>
                        <h2 class="fw-bold">{{ $totalTickets }}</h2>
                    </div>
                    <i class="mdi mdi-ticket fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="col-md-3 grid-margin stretch-card">
        <div class="card shadow border-0 bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Ouverts</h6>
                        <h2 class="fw-bold">{{ $openTickets }}</h2>
                    </div>
                    <i class="mdi mdi-alert-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div> -->
    <!-- <div class="col-md-3 grid-margin stretch-card">
        <div class="card shadow border-0 bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">En cours</h6>
                        <h2 class="fw-bold">{{ $inProgressTickets }}</h2>
                    </div>
                    <i class="mdi mdi-progress-clock fs-1"></i>
                </div>
            </div>
        </div>
    </div> -->

    <!-- <div class="col-md-3 grid-margin stretch-card">
        <div class="card shadow border-0 bg-dark text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">En attente</h6>
                        <h2 class="fw-bold">{{ $onHoldTickets ?? 0 }}</h2>
                    </div>
                    <i class="mdi mdi-pause-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div> -->


    <div class="col-md-3 grid-margin stretch-card">
        <div class="card shadow border-0 bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Résolus</h6>
                        <h2 class="fw-bold">{{ $resolvedTickets }}</h2>
                    </div>
                    <i class="mdi mdi-check-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 grid-margin stretch-card">
        <div class="card shadow border-0 bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Clôturés</h6>
                        <h2 class="fw-bold">{{ $closedTickets ?? 0 }}</h2>
                    </div>
                    <i class="mdi mdi-swap-horizontal fs-1"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 grid-margin stretch-card">
        <div class="card shadow border-0 bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">En retard</h6>
                        <h2 class="fw-bold">{{ $lateTickets }}</h2>
                    </div>
                    <i class="mdi mdi-alarm-check fs-1"></i>
                </div>
            </div>
        </div>
    </div>

    
</div>

{{-- DEUXIÈME LIGNE DE CARTES (statuts supplémentaires + retard) --}}
<div class="row mt-3">
    
    
    <!-- <div class="col-md-3 grid-margin stretch-card">
        <div class="card shadow border-0 bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Réouverts</h6>
                        <h2 class="fw-bold">{{ $reopenedTickets }}</h2>
                    </div>
                    <i class="mdi mdi-repeat fs-1"></i>
                </div>
            </div>
        </div>
    </div> -->
    
</div>

    {{-- GRAPHIQUES LIGNE 1 --}}
    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-bold">Tickets par priorité</h5>
                        <span class="badge bg-light text-dark">Vue globale</span>
                    </div>
                    <canvas id="priorityChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-bold">Répartition des statuts</h5>
                        <span class="badge bg-light text-dark">Tickets</span>
                    </div>
                    <canvas id="statusChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- PERFORMANCE ET UNITÉS --}}
    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Indicateurs de performance</h5>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tickets en retard</span>
                            <strong class="text-danger">{{ $lateTickets }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: {{ $totalTickets > 0 ? ($lateTickets / $totalTickets) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tickets réouverts</span>
                            <strong class="text-warning">{{ $reopenedTickets }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ $totalTickets > 0 ? ($reopenedTickets / $totalTickets) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Performance des unités</h5>
                        <span class="badge bg-success">Taux de résolution</span>
                    </div>
                    <canvas id="unitChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLEAU DÉTAILLÉ PAR UNITÉ (EFFICACITÉ) --}}
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h5 class="fw-bold mb-4">Évaluation des équipes</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Unité</th>
                            <th>Résolus / Total</th>
                            <th>En retard</th>
                            <th>Réouverture</th>
                            <th>Efficacité*</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unitPerformance as $unit)
                            @php
                                $total = $unit->total_tickets;
                                $resolved = $unit->resolved_tickets;
                                $late = $unit->late_tickets;
                                $reopened = $unit->reopened_tickets;
                                $resolutionRate = $total > 0 ? round(($resolved / $total) * 100) : 0;
                                $latencyRate = $total > 0 ? round(($late / $total) * 100) : 0;
                                $reopenRate = $total > 0 ? round(($reopened / $total) * 100) : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $unit->name }}</strong></td>
                                <td>{{ $resolved }} / {{ $total }}</td>
                                <td class="text-danger">{{ $late }} ({{ $latencyRate }}%)</td>
                                <td class="text-warning">{{ $reopened }} ({{ $reopenRate }}%)</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: {{ $resolutionRate }}%"></div>
                                        </div>
                                        <span class="small">{{ $resolutionRate }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucune donnée disponible</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-2">* Efficacité = % de tickets résolus. Un taux de retard élevé ou de réouverture indique une performance à améliorer.</p>
        </div>
    </div>

    {{-- TICKETS RÉCENTS --}}
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Tickets récents</h5>
                <span class="badge bg-primary">{{ count($recentTickets) }} tickets</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Priorité</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTickets as $ticket)
                            <tr>
                                <td><strong>#{{ $ticket->id }}</strong></td>
                                <td>{{ Str::limit($ticket->description, 50) }}</td>
                                <td>
                                    @if($ticket->priority === 'LOW')
                                        <span class="badge bg-success">LOW</span>
                                    @elseif($ticket->priority === 'MEDIUM')
                                        <span class="badge bg-info">MEDIUM</span>
                                    @elseif($ticket->priority === 'HIGH')
                                        <span class="badge bg-warning">HIGH</span>
                                    @else
                                        <span class="badge bg-danger">CRITICAL</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($ticket->status)
                                        @case('OPEN') <span class="badge bg-warning">OPEN</span> @break
                                        @case('IN_PROGRESS') <span class="badge bg-info">IN_PROGRESS</span> @break
                                        @case('RESOLVED') <span class="badge bg-success">RESOLVED</span> @break
                                        @default <span class="badge bg-secondary">{{ $ticket->status }}</span>
                                    @endswitch
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Aucun ticket récent</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des priorités (barres)
    new Chart(document.getElementById('priorityChart'), {
        type: 'bar',
        data: {
            labels: ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'],
            datasets: [{
                label: 'Tickets',
                data: [{{ $priorityStats['LOW'] }}, {{ $priorityStats['MEDIUM'] }}, {{ $priorityStats['HIGH'] }}, {{ $priorityStats['CRITICAL'] }}],
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Graphique des statuts (doughnut)
    // Graphique des statuts (doughnut)
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['OPEN', 'IN_PROGRESS', 'TRANSFERRED', 'ON_HOLD', 'REOPENED', 'RESOLVED', 'CLOSED'],
        datasets: [{
            data: [
                {{ $openTickets }},
                {{ $inProgressTickets }},
                {{ $transferredTickets ?? 0 }},
                {{ $onHoldTickets ?? 0 }},
                {{ $reopenedTickets ?? 0 }},
                {{ $resolvedTickets }},
                {{ $closedTickets }}
            ],
            backgroundColor: [
                '#ffc107', '#17a2b8', '#fd7e14', '#6c757d', '#e83e8c', '#28a745', '#343a40'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

    // Graphique de performance des unités (barres groupées)
    new Chart(document.getElementById('unitChart'), {
        type: 'bar',
        data: {
            labels: [
                @foreach($unitPerformance as $unit)
                    '{{ $unit->name }}',
                @endforeach
            ],
            datasets: [
                {
                    label: 'Résolus',
                    data: [ @foreach($unitPerformance as $unit) {{ $unit->resolved_tickets }}, @endforeach ],
                    backgroundColor: '#28a745'
                },
                {
                    label: 'En retard',
                    data: [ @foreach($unitPerformance as $unit) {{ $unit->late_tickets }}, @endforeach ],
                    backgroundColor: '#dc3545'
                },
                {
                    label: 'Réouverts',
                    data: [ @foreach($unitPerformance as $unit) {{ $unit->reopened_tickets }}, @endforeach ],
                    backgroundColor: '#ffc107'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endpush
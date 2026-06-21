@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="fw-semibold mb-1" style="font-size: 1.5rem;">TABLEAU DE BORD</h3>
    </div>

    <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2 flex-wrap align-items-end bg-white p-2 rounded-3 shadow-sm">
        <div>
            <label class="form-label small text-muted mb-0">Du</label>
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control form-control-sm border-0 bg-light">
        </div>
        <div>
            <label class="form-label small text-muted mb-0">Au</label>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control form-control-sm border-0 bg-light">
        </div>
        @if(in_array($role, ['admin', 'manager']))
        <div>
            <select name="unit_id" class="form-select form-select-sm border-0 bg-light" id="unitSelect">
                <option value="">Toutes unités</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button class="btn btn-sm btn-primary rounded-pill px-3"><i class="mdi mdi-filter-outline"></i> Filtrer</button>
    </form>
</div>

{{-- CARTES STATUTS --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    @php
        $cards = [
            ['label' => 'Ouverts',    'value' => $openTickets,              'status' => 'OPEN',        'icon' => 'mdi-alert-circle',    'color' => '#f6c23e'],
            ['label' => 'En cours',   'value' => $inProgressTickets,        'status' => 'IN_PROGRESS', 'icon' => 'mdi-progress-clock',  'color' => '#36b9cc'],
            ['label' => 'Transférés', 'value' => $transferredTickets ?? 0,  'status' => 'TRANSFERRED', 'icon' => 'mdi-swap-horizontal', 'color' => '#6f42c1'],
            ['label' => 'En attente', 'value' => $onHoldTickets ?? 0,       'status' => 'ON_HOLD',     'icon' => 'mdi-pause-circle',    'color' => '#a09898'],
            ['label' => 'Réouverts',  'value' => $reopenedTickets,          'status' => 'REOPENED',    'icon' => 'mdi-repeat',          'color' => '#fd7e14'],
            ['label' => 'Résolus',    'value' => $resolvedTickets,          'status' => 'RESOLVED',    'icon' => 'mdi-check-circle',    'color' => '#1cc88a'],
            ['label' => 'Clôturés',   'value' => $closedTickets,            'status' => 'CLOSED',      'icon' => 'mdi-lock',            'color' => '#6c757d'],
            ['label' => 'En retard',  'value' => $lateTickets,              'late'   => 1,             'icon' => 'mdi-alarm-check',     'color' => '#e74a3b'],
            ['label' => 'Total',      'value' => $totalTickets,             'status' => null,          'icon' => 'mdi-ticket',          'color' => '#4e73df'],
        ];
    @endphp
    @foreach($cards as $card)
        @php
            $url = isset($card['late'])
                ? route('tickets.index', ['late' => 1])
                : ($card['status'] !== null ? route('tickets.index', ['status' => $card['status']]) : route('tickets.index'));
        @endphp
        <a href="{{ $url }}" class="card border-0 shadow-sm text-decoration-none" style="min-width:100px; flex:1 0 auto; border-radius:12px;">
            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase">{{ $card['label'] }}</span>
                    <h4 class="fw-bold mb-0">{{ $card['value'] }}</h4>
                </div>
                <i class="mdi {{ $card['icon'] }} fs-4" style="color: {{ $card['color'] }};"></i>
            </div>
        </a>
    @endforeach
</div>

{{-- LIGNE 1 : KPI CLÉS --}}
<div class="row g-3 mb-4">
    {{-- Taux de résolution dans les délais --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body text-center p-3">
                <div class="text-muted small mb-1">Résolution dans les délais</div>
                @php
                    $onTimeRate = $totalTickets > 0 ? round((($resolvedTickets - $lateTickets) / max($resolvedTickets, 1)) * 100) : 0;
                    $onTimeRate = max(0, $onTimeRate);
                    $onTimeColor = $onTimeRate >= 80 ? '#1cc88a' : ($onTimeRate >= 50 ? '#f6c23e' : '#e74a3b');
                @endphp
                <h2 class="fw-bold mb-0" style="color: {{ $onTimeColor }};">{{ $onTimeRate }}%</h2>
                <!-- <small class="text-muted">{{ $resolvedTickets }} résolus / {{ $lateTickets }} en retard</small> -->
            </div>
        </div>
    </div>

    {{-- Taux de réouverture --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body text-center p-3">
                <div class="text-muted small mb-1">Taux de réouverture</div>
                @php
                    $reopenRate = $resolvedTickets > 0 ? round(($reopenedTickets / $resolvedTickets) * 100) : 0;
                    $reopenColor = $reopenRate <= 10 ? '#1cc88a' : ($reopenRate <= 25 ? '#f6c23e' : '#e74a3b');
                @endphp
                <h2 class="fw-bold mb-0" style="color: {{ $reopenColor }};">{{ $reopenRate }}%</h2>
                <!-- <small class="text-muted">{{ $reopenedTickets }} réouvertures</small> -->
            </div>
        </div>
    </div>

    {{-- Tickets urgents --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body text-center p-3">
                <div class="text-muted small mb-1">Tickets urgents</div>
                @php
                    $urgentRate = $totalTickets > 0 ? round(($urgentTickets / $totalTickets) * 100) : 0;
                @endphp
                <h2 class="fw-bold mb-0 text-danger">{{ $urgentRate }}%</h2>
                <!-- <small class="text-muted">{{ $urgentRate }}% du total</small> -->
            </div>
        </div>
    </div>

    {{-- Délai moyen de résolution --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body text-center p-3">
                <div class="text-muted small mb-1">Délai moyen de résolution</div>
                <h2 class="fw-bold mb-0" style="color:#36b9cc;">{{ $avgResolutionHours }}h</h2>
                <!-- <small class="text-muted">sur les tickets résolus</small> -->
            </div>
        </div>
    </div>
</div>

{{-- LIGNE 2 : GRAPHIQUES PRINCIPAUX --}}
<div class="row g-3 mb-4">
    {{-- Évolution mensuelle --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3"><i class="mdi mdi-chart-line me-1"></i> Évolution mensuelle des tickets</h6>
                <canvas id="evolutionChart" height="110"></canvas>
            </div>
        </div>
    </div>

    {{-- Répartition des statuts --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3"><i class="mdi mdi-chart-donut me-1"></i> Répartition des statuts</h6>
                <canvas id="statusChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- LIGNE 3 : TYPES RÉCURRENTS + URGENTS PAR AGENCE --}}
<div class="row g-3 mb-4">
    {{-- Types les plus récurrents --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3">
                    <i class="mdi mdi-fire me-1 text-danger"></i>
                    Types de réclamations les plus fréquents
                </h6>
                @forelse($topTypes as $type)
                    @php
                        $pct = $totalTickets > 0 ? round(($type->count / $totalTickets) * 100) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <a href="{{ route('tickets.index', ['status' => 'OPEN']) }}" class="btn btn-sm btn-outline-warning rounded-pill"></a>
                                <span class="fw-semibold" style="font-size:13px;">{{ $type->name }}</span>
                                <small class="text-muted ms-1">({{ $type->unit_name ?? '' }})</small>
                            </div>
                            <span class="badge bg-light text-dark border">{{ $type->count }} tickets</span>
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar" style="width:{{ $pct }}%; background:#4e73df;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small">Aucune donnée</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tickets urgents non résolus par agence --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3">
                    <i class="mdi mdi-alert me-1 text-warning"></i>
                    Tickets urgents non résolus par agence
                </h6>
                @forelse($urgentByAgency as $agency)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span style="font-size:13px;">{{ $agency->name }}</span>
                        <span class="badge {{ $agency->urgent_count > 3 ? 'bg-danger' : 'bg-warning text-dark' }}">
                            {{ $agency->urgent_count }} urgent{{ $agency->urgent_count > 1 ? 's' : '' }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted small mt-2">Aucun ticket urgent en cours</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- LIGNE 4 : ÉVALUATION DES ÉQUIPES + TICKETS EN RETARD PAR UNITÉ --}}
<div class="row g-3 mb-4">
    {{-- Évaluation des équipes --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3">Évaluation des équipes par unité</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Unité</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Résolus</th>
                                <th class="text-center">En retard</th>
                                <th class="text-center">Réouvertures</th>
                                <th>Efficacité</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unitPerformance as $unit)
                                @php
                                    $total    = $unit->total_tickets;
                                    $resolved = $unit->resolved_tickets;
                                    $late     = $unit->late_tickets;
                                    $reopened = $unit->reopened_tickets;
                                    $rate     = $total > 0 ? round(($resolved / $total) * 100) : 0;
                                    $barColor = $rate >= 80 ? '#1cc88a' : ($rate >= 50 ? '#f6c23e' : '#e74a3b');
                                @endphp
                                <tr>
                                    <td><strong>{{ $unit->name }}</strong></td>
                                    <td class="text-center">{{ $total }}</td>
                                    <td class="text-center text-success">{{ $resolved }}</td>
                                    <td class="text-center text-danger">
                                        {{ $late }}
                                        @if($total > 0)
                                            <small class="text-muted">({{ round(($late/$total)*100) }}%)</small>
                                        @endif
                                    </td>
                                    <td class="text-center text-warning">
                                        {{ $reopened }}
                                        @if($total > 0)
                                            <small class="text-muted">({{ round(($reopened/$total)*100) }}%)</small>
                                        @endif
                                    </td>
                                    <td style="min-width:120px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;">
                                                <div class="progress-bar" style="width:{{ $rate }}%; background:{{ $barColor }};"></div>
                                            </div>
                                            <span class="small fw-semibold" style="color:{{ $barColor }};">{{ $rate }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Aucune donnée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Tickets sans technicien assigné --}}
    <!-- <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3">
                    <i class="mdi mdi-account-alert me-1" style="color:#fd7e14;"></i>
                    Tickets sans technicien
                </h6>
                <div class="text-center py-3">
                    <h1 class="fw-bold" style="color:#fd7e14; font-size:3rem;">{{ $unassignedTickets }}</h1>
                    <p class="text-muted mb-3">tickets ouverts sans technicien assigné</p>
                    <a href="{{ route('tickets.index', ['status' => 'OPEN']) }}" class="btn btn-sm btn-outline-warning rounded-pill">
                        Voir les tickets <i class="mdi mdi-arrow-right"></i>
                    </a>
                </div>
                @if($unassignedTickets > 0)
                    <div class="alert alert-warning alert-sm py-2 px-3 mb-0" style="font-size:12px;">
                        <i class="mdi mdi-information-outline me-1"></i>
                        Ces tickets attendent d'être pris en charge.
                    </div>
                @endif
            </div>
        </div>
    </div> -->
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Évolution mensuelle ───────────────────────────────────
    new Chart(document.getElementById('evolutionChart'), {
        type: 'line',
        data: {
            labels: @json($monthlyLabels),
            datasets: [
                {
                    label: 'Créés',
                    data: @json($monthlyCreated),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78,115,223,0.08)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                },
                {
                    label: 'Résolus',
                    data: @json($monthlyResolved),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28,200,138,0.08)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                },
                {
                    label: 'En retard',
                    data: @json($monthlyLate),
                    borderColor: '#e74a3b',
                    backgroundColor: 'rgba(231,74,59,0.05)',
                    tension: 0.4,
                    fill: false,
                    borderDash: [5, 5],
                    pointRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // ── Répartition des statuts ───────────────────────────────
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Ouvert', 'En cours', 'Transféré', 'En attente', 'Réouvert', 'Résolu', 'Clôturé'],
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
                backgroundColor: ['#f6c23e','#36b9cc','#6f42c1','#a09898','#fd7e14','#1cc88a','#6c757d'],
                borderWidth: 0,
                cutout: '65%'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
            }
        }
    });

});

document.getElementById('unitSelect')?.addEventListener('change', () =>
    document.querySelector('form[action="{{ route('dashboard') }}"]').submit()
);
</script>
@endpush
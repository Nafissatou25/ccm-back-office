@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="fw-semibold mb-1" style="font-size: 1.5rem;">TABLEAU DE BORD</h3>
    </div>

    <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2 flex-wrap align-items-end bg-white p-2 rounded-3 shadow-sm" id="dashboardForm">
        <div>
            <label class="form-label small text-muted mb-0">Du</label>
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
       class="form-control form-control-sm border-0 bg-light" 
       id="startDate" onchange="this.form.submit()">
        </div>
        <div>
            <label class="form-label small text-muted mb-0">Au</label>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" 
       class="form-control form-control-sm border-0 bg-light" 
       id="endDate" onchange="this.form.submit()">
        </div>
        @if(in_array($role, ['admin', 'manager']))
        {{-- Dropdown Filtres --}}
        <div class="dropdown">
            <button class="btn btn-primary rounded-pill dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                <i class="mdi mdi-filter-outline"></i> Filtrer
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:220px;">
                @if(in_array($role, ['admin', 'manager']))
                <div class="mb-2">
                    <label class="form-label small">Unité</label>
                    <select name="unit_id" class="form-select form-select-sm" id="filterUnit">
                        <option value="">Toutes unités</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Agence</label>
                    <select name="agency_id" class="form-select form-select-sm" id="filterAgency">
                        <option value="">Toutes agences</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}" {{ $selectedAgencyId == $agency->id ? 'selected' : '' }}>{{ $agency->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button id="applyFilters" class="btn btn-primary btn-sm w-100 rounded-pill">Appliquer</button>
            </div>
        </div>
        @endif
        
        <a href="{{ route('dashboard.export', request()->query()) }}" class="btn btn-sm btn-success rounded-pill px-3">
        <i class="mdi mdi-file-excel"></i> Exporter
    </a>
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
            <div style="height:250px;">
                <canvas id="topTypesChart"></canvas>
            </div>
        </div>
    </div>
</div>

    {{-- LIGNE KPI : Performance et qualité --}}
<div class="col-md-6">
    <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body p-3">
            <h6 class="fw-semibold mb-3">
                <i class="mdi mdi-alert me-1 text-warning"></i>
                Performance et qualité opérationnelle
            </h6>

            <div class="row g-3">
                {{-- Taux de résolution --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div class="text-muted small">Taux de résolution</div>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="fw-bold mb-0" style="color: #1cc88a;">{{ $resolutionRate }}%</h2>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: {{ $resolutionRate }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Respect des SLA --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div class="text-muted small">Respect des SLA</div>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="fw-bold mb-0" style="color: {{ $slaCompliance >= 90 ? '#1cc88a' : ($slaCompliance >= 70 ? '#f6c23e' : '#e74a3b') }};">{{ $slaCompliance }}%</h2>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar {{ $slaCompliance >= 90 ? 'bg-success' : ($slaCompliance >= 70 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $slaCompliance }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Taux de clôture --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div class="text-muted small">Taux de clôture</div>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="fw-bold mb-0" style="color: #6c757d;">{{ $closureRate }}%</h2>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-secondary" style="width: {{ $closureRate }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Taux de réouverture --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div class="text-muted small">Taux de réouverture</div>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="fw-bold mb-0" style="color: #fd7e14;">{{ $reopenRate }}%</h2>
                                @if($reopenRate > 5)
                                    <span class="badge bg-danger-subtle text-danger small">
                                        <i class="mdi mdi-arrow-up"></i>
                                    </span>
                                @endif
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: {{ $reopenRate }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



{{-- LIGNE 4 : ÉVALUATION DES ÉQUIPES + TICKETS EN RETARD PAR UNITÉ --}}
<!-- <div class="row g-3 mb-4">
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
    <div class="col-md-4">
    <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body p-3">
            <h6 class="fw-semibold mb-3">
                <i class="mdi mdi-whatsapp me-1" style="color:#25D366;"></i>
                Demandes WhatsApp
            </h6>
            <div class="text-center py-3">
                <h1 class="fw-bold" style="color:#25D366; font-size:3rem;">
                    {{ $pendingWhatsappRequests ?? 0 }}
                </h1>
                <p class="text-muted mb-3">demandes en attente de traitement</p>
                <a href="{{ route('admin.whatsapp.index') }}" class="btn btn-sm btn-outline-success rounded-pill">
                    Voir les demandes <i class="mdi mdi-arrow-right"></i>
                </a>
            </div>
            @if(($pendingWhatsappRequests ?? 0) > 0)
                <div class="alert alert-success alert-sm py-2 px-3 mb-0" style="font-size:12px;">
                    <i class="mdi mdi-information-outline me-1"></i>
                    Ces demandes peuvent être converties en tickets.
                </div>
            @endif
        </div>
    </div>
</div>
</div> -->

{{-- LIGNE : Performance par technicien --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3">
                    <i class="mdi mdi-account-group me-1 text-primary"></i>
                    Performance par technicien
                </h6>
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Technicien</th>
                                <th class="text-center">Tickets résolus</th>
                                <th class="text-center">TTR moyen (h)</th>
                                <th class="text-center">Réouvertures</th>
                                <th class="text-center">Efficacité</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($technicianPerformance as $tech)
                                @php
                                    $totalAssigned = $tech->assigned_count;
                                    $resolved = $tech->resolved_count;
                                    $efficiency = $totalAssigned > 0 ? round(($resolved / $totalAssigned) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $tech->name }}</strong></td>
                                    <td class="text-center text-success">{{ $resolved }}</td>
                                    <td class="text-center">{{ round($tech->avg_resolution_time ?? 0, 1) }}</td>
                                    <td class="text-center text-warning">{{ $tech->reopened_count }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;">
                                                <div class="progress-bar bg-{{ $efficiency >= 80 ? 'success' : ($efficiency >= 50 ? 'warning' : 'danger') }}" 
                                                     style="width:{{ $efficiency }}%;"></div>
                                            </div>
                                            <span class="small fw-semibold">{{ $efficiency }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Aucune donnée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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

    // ── Top 5 types de réclamations ──
new Chart(document.getElementById('topTypesChart'), {
    type: 'bar',
    data: {
        labels: @json($topTypesLabels),
        datasets: [{
            label: 'Nombre de tickets',
            data: @json($topTypesData),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
            borderRadius: 6,
            barPercentage: 0.6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => `${ctx.raw} tickets`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
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

document.getElementById('applyFilters')?.addEventListener('click', function() {
    // Récupérer les valeurs des filtres
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const unitId = document.getElementById('filterUnit')?.value || '';
    const agencyId = document.getElementById('filterAgency')?.value || '';

    // Construire l'URL
    let url = '{{ route("dashboard") }}?';
    const params = [];
    if (startDate) params.push('start_date=' + startDate);
    if (endDate) params.push('end_date=' + endDate);
    if (unitId) params.push('unit_id=' + unitId);
    if (agencyId) params.push('agency_id=' + agencyId);

    window.location.href = url + params.join('&');
});

// Bouton "Effacer" (optionnel) pour réinitialiser les filtres
document.getElementById('clearFilters')?.addEventListener('click', function() {
    window.location.href = '{{ route("dashboard") }}';
});

// ── Performance par technicien ──────────────────────────────
new Chart(document.getElementById('technicianChart'), {
    type: 'bar',
    data: {
        labels: @json($technicianNames),
        datasets: [
            {
                label: 'Résolus',
                data: @json($technicianResolved),
                backgroundColor: '#1cc88a',
                borderRadius: 4,
                order: 1,
            },
            {
                label: 'Réouvertures',
                data: @json($technicianReopened),
                backgroundColor: '#fd7e14',
                borderRadius: 4,
                order: 2,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    afterBody: function(context) {
                        const index = context[0].dataIndex;
                        const avg = @json($technicianAvgResolution)[index];
                        return `TTR moyen : ${avg ?? '—'} h`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>
@endpush
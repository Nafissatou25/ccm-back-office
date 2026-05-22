@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div>

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
                <!-- @if($role !== 'manager')
                <div>
                    <select name="unit_id" class="form-select form-select-sm border-0 bg-light" id="unitSelect">
                        <option value="">Toutes unités</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($role !== 'manager')
                <div>
                    <select name="type_id" class="form-select form-select-sm border-0 bg-light" id="typeSelect">
                        <option value="">Tous types</option>
                        @foreach($eligibleTypes as $type)
                            <option value="{{ $type->id }}" {{ $selectedTypeId == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif -->
                <button class="btn btn-sm btn-primary rounded-pill px-3"><i class="mdi mdi-filter-outline"></i> Filtrer</button>
            </form>
        </div>

        {{-- CARTES STATUTS (tous les statuts, couleurs distinctes) --}}
        <div class="d-flex flex-wrap gap-2 mb-5">
            @php
                // Palette distincte pour chaque statut
                $cardPalette = [
                    'Ouverts'    => ['icon' => 'mdi-alert-circle', 'color' => '#f6c23e'],
                    'En cours'   => ['icon' => 'mdi-progress-clock', 'color' => '#36b9cc'],
                    'Transférés' => ['icon' => 'mdi-swap-horizontal', 'color' => '#6f42c1'],    // violet
                    'En attente' => ['icon' => 'mdi-pause-circle', 'color' => '#a09898'],        // gris brun
                    'Réouverts'  => ['icon' => 'mdi-repeat', 'color' => '#fd7e14'],              // orange foncé
                    'Résolus'    => ['icon' => 'mdi-check-circle', 'color' => '#1cc88a'],
                    'Clôturés'   => ['icon' => 'mdi-lock', 'color' => '#6c757d'],                // gris foncé
                    'En retard'  => ['icon' => 'mdi-alarm-check', 'color' => '#e74a3b'],         
                    'Total'      => ['icon' => 'mdi-ticket', 'color' => '#4e73df'],
                ];
                $cards = [
                    ['label' => 'Ouverts',    'value' => $openTickets],
                    ['label' => 'En cours',   'value' => $inProgressTickets],
                    ['label' => 'Transférés', 'value' => $transferredTickets ?? 0],
                    ['label' => 'En attente', 'value' => $onHoldTickets ?? 0],
                    ['label' => 'Réouverts',  'value' => $reopenedTickets],
                    ['label' => 'Résolus',    'value' => $resolvedTickets],
                    ['label' => 'Clôturés',   'value' => $closedTickets],
                    ['label' => 'En retard',  'value' => $lateTickets],
                    ['label' => 'Total',      'value' => $totalTickets],
                ];
            @endphp
            @foreach($cards as $card)
                @php $p = $cardPalette[$card['label']]; @endphp
                <div class="card border-0 shadow-sm" style="min-width: 100px; flex: 1 0 auto; border-radius: 12px;">
                    <div class="card-body p-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase">{{ $card['label'] }}</span>
                            <h4 class="fw-bold mb-0">{{ $card['value'] }}</h4>
                        </div>
                        <i class="mdi {{ $p['icon'] }} fs-4" style="color: {{ $p['color'] }};"></i>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- GRAPHIQUES --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-2"><i class="mdi mdi-chart-bar me-1"></i> Tickets par priorité</h6>
                        <canvas id="priorityChart" height="150"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-2"><i class="mdi mdi-chart-pie me-1"></i> Répartition des statuts</h6>
                        <canvas id="statusChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLEAU ÉVALUATION DES ÉQUIPES --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3">Évaluation des équipes</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Unité</th>
                                <th>Résolus / Total</th>
                                <th>Retard</th>
                                <th>Réouverture</th>
                                <th>Efficacité</th>
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
                                @endphp
                                <tr>
                                    <td><strong>{{ $unit->name }}</strong></td>
                                    <td>{{ $resolved }} / {{ $total }}</td>
                                    <td class="text-danger">{{ $late }} ({{ $total > 0 ? round(($late / $total) * 100) : 0 }}%)</td>
                                    <td class="text-warning">{{ $reopened }} ({{ $total > 0 ? round(($reopened / $total) * 100) : 0 }}%)</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: {{ $resolutionRate }}%"></div>
                                            </div>
                                            <span class="small">{{ $resolutionRate }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Aucune donnée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- <p class="text-muted small mt-2 mb-0">* Efficacité = % de tickets résolus. Retard ou réouverture élevés → marge de progression.</p> -->
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique des priorités (noms en français, couleurs appropriées)
        new Chart(document.getElementById('priorityChart'), {
            type: 'bar',
            data: {
                labels: ['Faible', 'Moyenne', 'Haute', 'Critique'],
                datasets: [{
                    data: [
                        {{ $priorityStats['LOW'] }},
                        {{ $priorityStats['MEDIUM'] }},
                        {{ $priorityStats['HIGH'] }},
                        {{ $priorityStats['CRITICAL'] }}
                    ],
                    backgroundColor: [
        '#28a745',  // Faible → vert
        '#ffc107',  // Moyenne → jaune/orange
        '#fd7e14',  // Haute → orange foncé
        '#dc3545'   // Critique → rouge
    ],
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => `${ctx.raw} tickets` } } },
                scales: { y: { beginAtZero: true, grid: { display: false }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
            }
        });

        // Graphique des statuts (doughnut) – couleurs distinctes pour chaque statut
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Ouvert', 'En cours', 'Transféré', 'En attente', 'Réouvert', 'Résolu', 'Clôturé', 'En retard'],
        datasets: [{
            data: [
                {{ $openTickets }},
                {{ $inProgressTickets }},
                {{ $transferredTickets ?? 0 }},
                {{ $onHoldTickets ?? 0 }},
                {{ $reopenedTickets ?? 0 }},
                {{ $resolvedTickets }},
                {{ $closedTickets }},
                {{ $lateTickets }}   // Ajout de la part "En retard"
            ],
            backgroundColor: [
                '#f6c23e',   // Ouvert (jaune)
                '#36b9cc',   // En cours (bleu clair)
                '#6f42c1',   // Transféré (violet)
                '#a09898',   // En attente (gris brun)
                '#fd7e14',   // Réouvert (orange foncé)
                '#1cc88a',   // Résolu (vert)
                '#6c757d',   // Clôturé (gris foncé)
                '#dc3545'    // En retard (rouge vif)
            ],
            borderWidth: 0,
            cutout: '60%'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 10, font: { size: 10 } }
            }
        }
    }
});
    });
</script>
<script>
    document.getElementById('unitSelect')?.addEventListener('change', () => document.querySelector('form[action="{{ route('dashboard') }}"]').submit());
    document.getElementById('typeSelect')?.addEventListener('change', () => document.querySelector('form[action="{{ route('dashboard') }}"]').submit());
</script>
@endpush
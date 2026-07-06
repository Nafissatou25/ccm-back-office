@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="content-wrapper">

    {{-- EN-TÊTE --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <h3 class="fw-bold">
            <i class="mdi mdi-view-dashboard me-2 text-primary"></i>
            Tableau de bord administrateur
        </h3>
       
    </div>

    {{-- CARTES STATISTIQUES --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-0">Utilisateurs</p>
                        <h3 class="fw-bold mb-0">{{ $usersCount }}</h3>
                    </div>
                    <i class="mdi mdi-account-group fs-1 text-primary opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-0">Unités</p>
                        <h3 class="fw-bold mb-0">{{ $unitsCount }}</h3>
                    </div>
                    <i class="mdi mdi-domain fs-1 text-success opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-0">Agences</p>
                        <h3 class="fw-bold mb-0">{{ $agenciesCount }}</h3>
                    </div>
                    <i class="mdi mdi-bank fs-1 text-warning opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-0">Types réclamations</p>
                        <h3 class="fw-bold mb-0">{{ $typesCount }}</h3>
                    </div>
                    <i class="mdi mdi-tag-multiple fs-1 text-info opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- LIGNE 1 : GRAPHIQUES PRINCIPAUX --}}
    <div class="row g-3 mb-4">
        {{-- Utilisateurs par rôle --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-3">
                        <i class="mdi mdi-account-group me-1 text-primary"></i>
                        Utilisateurs par rôle
                    </h6>
                    <div style="height:200px;">
                        <canvas id="usersByRoleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Utilisateurs par agence --}}
<div class="col-md-4">
    <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body p-3">
            <h6 class="fw-semibold mb-3">
                <i class="mdi mdi-account-group me-1 text-primary"></i>
                Utilisateurs par agence
            </h6>
            <div style="height:200px;">
                <canvas id="usersByAgencyChart"></canvas>
            </div>
        </div>
    </div>
</div>

        {{-- Types par unité --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-3">
                        <i class="mdi mdi-tag-multiple me-1 text-info"></i>
                        Types par unité
                    </h6>
                    <div style="height:200px;">
                        <canvas id="typesByUnitChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LIGNE 2 : ÉVOLUTION + SLA --}}
    <div class="row g-3 mb-4">
        {{-- Évolution des utilisateurs --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-3">
                        <i class="mdi mdi-chart-line me-1 text-primary"></i>
                        Évolution des utilisateurs (6 mois)
                    </h6>
                    <div style="height:180px;">
                        <canvas id="usersEvolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- SLA : actifs vs inactifs --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-3">
                        <i class="mdi mdi-format-list-bulleted-type me-1 text-secondary"></i>
                        Règles SLA
                    </h6>
                    <div style="height:180px;">
                        <canvas id="slaStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Résumé SLA --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-3">
                        <i class="mdi mdi-information-outline me-1 text-muted"></i>
                        Résumé SLA
                    </h6>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Total</span>
                        <strong>{{ $slaCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Actives</span>
                        <strong class="text-success">{{ $slaActiveCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span>Inactives</span>
                        <strong class="text-danger">{{ $slaInactiveCount }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LIGNE 3 : DERNIÈRES RÈGLES SLA --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-3">
                        <i class="mdi mdi-clock-history me-1 text-muted"></i>
                        Dernières règles SLA ajoutées
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Unité</th>
                                    <th>Type</th>
                                    <th>TTO (h)</th>
                                    <th>TTR (h)</th>
                                    <th>Statut</th>
                                    <th>Créée le</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSlaRules as $sla)
                                    <tr>
                                        <td>{{ $sla->unit?->name ?? 'Toutes' }}</td>
                                        <td>{{ $sla->type?->name ?? 'Par défaut' }}</td>
                                        <td>{{ $sla->tto }}</td>
                                        <td>{{ $sla->ttr }}</td>
                                        <td>
                                            <span class="badge {{ $sla->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $sla->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $sla->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Aucune règle SLA</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

    // ── Utilisateurs par rôle (camembert) ──
    new Chart(document.getElementById('usersByRoleChart'), {
        type: 'pie',
        data: {
            labels: @json($roleLabels),
            datasets: [{
                data: @json($roleData),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } }
        }
    });

    // ── Utilisateurs par agence (barres) ──
new Chart(document.getElementById('usersByAgencyChart'), {
    type: 'bar',
    data: {
        labels: @json($agencyLabels),
        datasets: [{
            label: 'Utilisateurs',
            data: @json($agencyData),
            backgroundColor: '#36b9cc',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

    // ── Types par unité (barres) ──
    new Chart(document.getElementById('typesByUnitChart'), {
        type: 'bar',
        data: {
            labels: @json($typeUnitLabels),
            datasets: [{
                label: 'Types',
                data: @json($typeUnitData),
                backgroundColor: '#f6c23e',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // ── Évolution des utilisateurs (ligne) ──
    new Chart(document.getElementById('usersEvolutionChart'), {
        type: 'line',
        data: {
            labels: @json($userMonthsLabels),
            datasets: [{
                label: 'Nouveaux utilisateurs',
                data: @json($userMonthsData),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78,115,223,0.05)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // ── SLA actifs vs inactifs (donut) ──
    new Chart(document.getElementById('slaStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Actives', 'Inactives'],
            datasets: [{
                data: [{{ $slaActiveCount }}, {{ $slaInactiveCount }}],
                backgroundColor: ['#1cc88a', '#e74a3b'],
                borderWidth: 0,
                cutout: '70%',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
            }
        }
    });

});
</script>
@endpush
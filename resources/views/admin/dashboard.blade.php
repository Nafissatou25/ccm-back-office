@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <!-- Cartes statistiques -->
        <div class="col-md-6 col-lg-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $usersCount }}</h3>
                            <p class="text-muted mb-0">Utilisateurs</p>
                        </div>
                        <i class="mdi mdi-account-group fs-1 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $unitsCount }}</h3>
                            <p class="text-muted mb-0">Unités</p>
                        </div>
                        <i class="mdi mdi-domain fs-1 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $agenciesCount }}</h3>
                            <p class="text-muted mb-0">Agences</p>
                        </div>
                        <i class="mdi mdi-bank fs-1 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $typesCount }}</h3>
                            <p class="text-muted mb-0">Types réclamations</p>
                        </div>
                        <i class="mdi mdi-tag-multiple fs-1 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Évolution des tickets</h4>
                    <canvas id="lineChart" style="height:250px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Tickets par statut</h4>
                    <canvas id="doughnutChart" style="height:250px"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Temps SLA par priorité</h4>
                    <canvas id="barChart" style="height:250px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Cumul des tickets (année)</h4>
                    <canvas id="areaChart" style="height:250px"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Taux de résolution</h4>
                    <canvas id="pieChart" style="height:250px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Temps de résolution par ticket</h4>
                    <canvas id="scatterChart" style="height:250px"></canvas>
                </div>
            </div>
        </div>
    </div> -->
<!-- </div> -->
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Line chart : tickets par mois
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: @json($monthsLabels),
                datasets: [{
                    label: 'Tickets créés',
                    data: @json($ticketsData),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78,115,223,0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });

        // Doughnut chart : statuts
        new Chart(document.getElementById('doughnutChart'), {
            type: 'doughnut',
            data: {
                labels: @json($statusLabels),
                datasets: [{
                    data: @json($statusCounts),
                    backgroundColor: ['#f6c23e', '#4e73df', '#858796', '#1cc88a', '#e74a3b', '#36b9cc', '#fd7e14']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Bar chart : SLA
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: @json($slaPriorities),
                datasets: [{
                    label: 'Minutes',
                    data: @json($slaResolutionTimes),
                    backgroundColor: '#36b9cc',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Area chart : cumul tickets
        new Chart(document.getElementById('areaChart'), {
            type: 'line',
            data: {
                labels: @json($monthsLabels),
                datasets: [{
                    label: 'Cumul tickets',
                    data: @json($cumulativeTickets),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28,200,138,0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true }
        });

        // Pie chart : taux résolution
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: ['Résolus/Clos', 'En cours/autres'],
                datasets: [{
                    data: [{{ $resolutionRate }}, {{ 100 - $resolutionRate }}],
                    backgroundColor: ['#1cc88a', '#e74a3b']
                }]
            },
            options: { responsive: true }
        });

        // Scatter chart : temps résolution par ticket
        new Chart(document.getElementById('scatterChart'), {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Heures de résolution',
                    data: @json($scatterData),
                    backgroundColor: '#4e73df'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Ticket ID' } },
                    y: { title: { display: true, text: 'Heures' }, beginAtZero: true }
                }
            }
        });
    });
</script>
@endpush
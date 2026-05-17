@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

<div class="row">

    {{-- USERS --}}
    <div class="col-md-3 mb-4">
        <div class="card card-rounded shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Utilisateurs</p>
                        <h3 class="mb-0">{{ $usersCount }}</h3>
                    </div>

                    <i class="mdi mdi-account-group text-primary"
                       style="font-size: 40px;"></i>
                </div>

            </div>
        </div>
    </div>

    {{-- TICKETS --}}
    <div class="col-md-3 mb-4">
        <div class="card card-rounded shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Tickets</p>
                        <h3 class="mb-0">{{ $ticketsCount }}</h3>
                    </div>

                    <i class="mdi mdi-ticket text-warning"
                       style="font-size: 40px;"></i>
                </div>

            </div>
        </div>
    </div>

    {{-- OPEN --}}
    <div class="col-md-3 mb-4">
        <div class="card card-rounded shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Tickets ouverts</p>
                        <h3 class="mb-0">{{ $openTickets }}</h3>
                    </div>

                    <i class="mdi mdi-alert-circle text-danger"
                       style="font-size: 40px;"></i>
                </div>

            </div>
        </div>
    </div>

    {{-- RESOLVED --}}
    <div class="col-md-3 mb-4">
        <div class="card card-rounded shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Tickets résolus</p>
                        <h3 class="mb-0">{{ $resolvedTickets }}</h3>
                    </div>

                    <i class="mdi mdi-check-circle text-success"
                       style="font-size: 40px;"></i>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- CHARTS --}}
<div class="row">

    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">
                    Tickets par statut
                </h4>

                <canvas id="barChart"></canvas>

            </div>
        </div>
    </div>

    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">
                    Répartition tickets
                </h4>

                <canvas id="doughnutChart"></canvas>

            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    // BAR CHART
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Ouverts', 'En cours', 'Résolus', 'Clôturés'],
            datasets: [{
                label: 'Tickets',
                data: [
                    {{ $openTickets }},
                    {{ $inProgressTickets }},
                    {{ $resolvedTickets }},
                    {{ $closedTickets }}
                ],
                borderWidth: 1
            }]
        }
    });

    // DOUGHNUT
    new Chart(document.getElementById('doughnutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Ouverts', 'Résolus', 'Clôturés'],
            datasets: [{
                data: [
                    {{ $openTickets }},
                    {{ $resolvedTickets }},
                    {{ $closedTickets }}
                ]
            }]
        }
    });

</script>

@endsection
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

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Règles SLA</h4>
                    <p class="mb-0">Total : <strong>{{ $slaCount }}</strong></p>
                    <p>Actives : <strong>{{ $activeSlaCount }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Bienvenue</h4>
                    <p>Cette interface permet de gérer les données de référence : utilisateurs, unités, agences, types de tickets et règles SLA.</p>
                    <p>Aucune donnée opérationnelle (tickets) n’est affichée ici.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Aucun script chart.js nécessaire --}}
@endpush
@extends('layouts.app')

@section('title', 'Créer une unité')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0 fw-bold">
            <i class="mdi mdi-domain-plus me-2 text-primary"></i>
            Créer une unité
        </h3>
        <p class="text-muted small mb-0">
            Ajoutez une nouvelle unité opérationnelle
        </p>
    </div>
    <a href="{{ route('admin.units.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="mdi mdi-arrow-left me-1"></i> Retour
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-4 shadow-sm">
        <div class="d-flex align-items-start">
            <i class="mdi mdi-alert-circle me-2" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Veuillez corriger les erreurs suivantes :</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-lg-5">

        <form method="POST" action="{{ route('admin.units.store') }}" id="createUnitForm">
            @csrf

            <div class="row g-4">

                {{-- NOM DE L'UNITÉ --}}
                <div class="col-md-8 mx-auto">
                    <div class="form-floating">
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Nom de l'unité"
                               value="{{ old('name') }}"
                               required
                               autofocus>
                        <label for="name">
                            <i class="mdi mdi-domain me-1"></i> Nom de l'unité
                        </label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">
                        Exemple : Branchement, Petites interventions, Exploitation réseau, etc.
                    </div>
                </div>

            </div>

            {{-- BOUTONS --}}
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.units.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="mdi mdi-close me-1"></i> Annuler
                </a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="mdi mdi-check me-1"></i> Créer
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('styles')
<style>
    .form-floating {
        margin-bottom: 0;
    }

    .form-floating > .form-control {
        height: calc(3.5rem + 2px);
        border-radius: 0.5rem;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-floating > .form-control:focus {
        background-color: #fff;
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .form-floating > .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .rounded-4 {
        border-radius: 0.75rem !important;
    }

    .alert .mdi {
        flex-shrink: 0;
        margin-top: 0.1rem;
    }
</style>
@endpush
@extends('layouts.app')

@section('title', 'Créer un utilisateur')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0 fw-bold">
            <i class="mdi mdi-account-plus me-2 text-primary"></i>
            Créer un utilisateur
        </h3>
        <p class="text-muted small mb-0">
            Remplissez les informations pour ajouter un nouvel utilisateur
        </p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
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

        <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
            @csrf

            <div class="row g-4">

                {{-- MATRICULE --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text"
                               name="matricule"
                               id="matricule"
                               class="form-control @error('matricule') is-invalid @enderror"
                               placeholder="Matricule"
                               value="{{ old('matricule') }}"
                               required>
                        <label for="matricule">
                            <i class="mdi mdi-badge-account me-1"></i> Matricule
                        </label>
                        @error('matricule')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- NOM --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Nom complet"
                               value="{{ old('name') }}"
                               required>
                        <label for="name">
                            <i class="mdi mdi-account me-1"></i> Nom complet
                        </label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- EMAIL --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="Email"
                               value="{{ old('email') }}"
                               required>
                        <label for="email">
                            <i class="mdi mdi-email me-1"></i> Email
                        </label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- MOT DE PASSE --}}
                <div class="col-md-6">
                    <div class="form-floating position-relative">
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Mot de passe"
                               required>
                        <label for="password">
                            <i class="mdi mdi-lock me-1"></i> Mot de passe
                        </label>
                        <button type="button"
                                class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 p-0 text-muted toggle-password"
                                data-target="#password">
                            <i class="mdi mdi-eye"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- RÔLE --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="role_id"
                                id="role_id"
                                class="form-select @error('role_id') is-invalid @enderror"
                                required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="role_id">
                            <i class="mdi mdi-shield-account me-1"></i> Rôle
                        </label>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- AGENCE --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="agency_id"
                                id="agency_id"
                                class="form-select @error('agency_id') is-invalid @enderror">
                            <option value="">-- Optionnel --</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}"
                                    {{ old('agency_id') == $agency->id ? 'selected' : '' }}>
                                    {{ $agency->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="agency_id">
                            <i class="mdi mdi-bank me-1"></i> Agence
                        </label>
                        @error('agency_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- UNITÉ --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="unit_id"
                                id="unit_id"
                                class="form-select @error('unit_id') is-invalid @enderror">
                            <option value="">-- Optionnel --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="unit_id">
                            <i class="mdi mdi-domain me-1"></i> Unité
                        </label>
                        @error('unit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ENTREPRISE --}}
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="company_id"
                                id="company_id"
                                class="form-select @error('company_id') is-invalid @enderror">
                            <option value="">-- Choisir --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="company_id">
                            <i class="mdi mdi-domain me-1"></i> Entreprise
                        </label>
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            {{-- BOUTONS --}}
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Afficher/masquer le mot de passe
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = document.querySelector(this.dataset.target);
                if (target) {
                    const isPassword = target.getAttribute('type') === 'password';
                    target.setAttribute('type', isPassword ? 'text' : 'password');
                    this.querySelector('i').classList.toggle('mdi-eye');
                    this.querySelector('i').classList.toggle('mdi-eye-off');
                }
            });
        });

        // Validation côté client : vérifier que le mot de passe fait au moins 8 caractères
        const form = document.getElementById('createUserForm');
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            if (password.value.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères.');
                password.focus();
                password.classList.add('is-invalid');
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .form-floating {
        margin-bottom: 0;
    }

    .form-floating > .form-control,
    .form-floating > .form-select {
        height: calc(3.5rem + 2px);
        border-radius: 0.5rem;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-floating > .form-control:focus,
    .form-floating > .form-select:focus {
        background-color: #fff;
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .form-floating > .form-control.is-invalid,
    .form-floating > .form-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .toggle-password {
        z-index: 10;
        background: transparent;
        border: none;
        outline: none;
        color: #6c757d;
        cursor: pointer;
    }

    .toggle-password:hover {
        color: #212529;
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
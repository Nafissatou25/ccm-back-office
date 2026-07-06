@extends('layouts.app')

@section('title', 'Modifier l\'entreprise')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h3 class="mb-0 fw-bold" style="color: #1e293b;">
            <i class="mdi mdi-handshake-edit me-2" style="color: #6f42c1;"></i>
            Modifier l'entreprise
        </h3>
        <p class="text-muted small mb-0">
            Mettez à jour les informations de l'entreprise
        </p>
    </div>
    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="mdi mdi-arrow-left me-1"></i> Retour
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4">
        <div class="d-flex align-items-start">
            <i class="mdi mdi-alert-circle me-2" style="font-size: 1.5rem; color: #dc3545;"></i>
            <div>
                <strong class="d-block">Veuillez corriger les erreurs suivantes :</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-lg-5">

        <form method="POST" action="{{ route('admin.companies.update', $company) }}">

            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Nom de l'entreprise"
                               value="{{ old('name', $company->name) }}"
                               required>
                        <label for="name"><i class="mdi mdi-handshake me-1"></i> Nom *</label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text"
                               name="contact"
                               id="contact"
                               class="form-control @error('contact') is-invalid @enderror"
                               placeholder="Personne à contacter"
                               value="{{ old('contact', $company->contact) }}">
                        <label for="contact"><i class="mdi mdi-account me-1"></i> Contact</label>
                        @error('contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text"
                               name="phone"
                               id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="Téléphone"
                               value="{{ old('phone', $company->phone) }}">
                        <label for="phone"><i class="mdi mdi-phone me-1"></i> Téléphone</label>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="Email"
                               value="{{ old('email', $company->email) }}">
                        <label for="email"><i class="mdi mdi-email me-1"></i> Email</label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-floating">
                        <textarea name="address"
                                  id="address"
                                  class="form-control @error('address') is-invalid @enderror"
                                  placeholder="Adresse"
                                  style="height: 100px;">{{ old('address', $company->address) }}</textarea>
                        <label for="address"><i class="mdi mdi-map-marker me-1"></i> Adresse</label>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.companies.index') }}"
                   class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="mdi mdi-close me-1"></i> Annuler
                </a>
                <button type="submit"
                        class="btn btn-primary rounded-pill px-4"
                        style="background-color: #6f42c1; border-color: #6f42c1;">
                    <i class="mdi mdi-check me-1"></i> Mettre à jour
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('styles')
<style>
    .card {
        border-radius: 1rem !important;
        background: #ffffff;
        transition: box-shadow 0.2s ease;
    }
    .form-floating > .form-control {
        height: calc(3.5rem + 2px);
        border-radius: 0.5rem;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-floating > .form-control:focus {
        background-color: #ffffff;
        border-color: #6f42c1;
        box-shadow: 0 0 0 4px rgba(111, 66, 193, 0.10);
    }
    .form-floating > .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.10);
    }
    .btn-primary {
        background-color: #6f42c1;
        border-color: #6f42c1;
    }
    .btn-primary:hover {
        background-color: #5a32a3;
        border-color: #5a32a3;
    }
</style>
@endpush
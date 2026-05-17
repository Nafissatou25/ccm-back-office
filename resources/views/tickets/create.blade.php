@extends('layouts.app')

@section('title', 'Créer un ticket')

@section('content')

<div class="page-header">
    <h3 class="page-title">Créer un ticket</h3>
</div>

<div class="card">
    <div class="card-body">

        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">

                {{-- UNIT --}}
                <div class="col-md-4 mb-3">
                    <label>Unité</label>
                    <select name="unit_id" class="form-control" required>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- TYPE --}}
                <div class="col-md-4 mb-3">
                    <label>Type</label>
                    <select name="type_id" class="form-control" required>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- AGENCY --}}
                <div class="col-md-4 mb-3">
                    <label>Agence</label>
                    <select name="agency_id" class="form-control" required>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- ASSIGNED TO (optionnel) --}}
                <div class="col-md-4 mb-3">
    <label>Assigné à</label>

    <select name="assigned_to" class="form-control">
        <option value="">-- Sélectionner un utilisateur --</option>

        @foreach($users as $user)
            <option value="{{ $user->id }}">
                {{ $user->name }}
            </option>
        @endforeach
    </select>
</div>

                {{-- PRIORITY --}}
                <div class="col-md-4 mb-3">
                    <label>Priorité</label>
                    <select name="priority" class="form-control" required>
                        <option value="LOW">FAIBLE</option>
                        <option value="MEDIUM">MOYENNE</option>
                        <option value="HIGH">GRANDE</option>
                        <option value="CRITICAL">CRITIQUE</option>
                    </select>
                </div>

                {{-- CONTRACT NUMBER --}}
                <div class="col-md-4 mb-3">
                    <label>Numéro contrat</label>
                    <input type="text" name="contract_number" class="form-control">
                </div>

                {{-- DESCRIPTION --}}
                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="5" required></textarea>
                </div>

                {{-- ATTACHMENT --}}
                <div class="col-md-12 mb-3">
                    <label>Fichier joint</label>
                    <input type="file" name="attachment_path" class="form-control">
                </div>

            </div>

            <button class="btn btn-primary">
                Créer le ticket
            </button>

        </form>

    </div>
</div>

@endsection
@extends('layouts.app')

@section('title', 'Tickets')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Liste des tickets</h3>

    <!-- <a href="{{ route('tickets.create') }}" class="btn btn-primary">
        + Nouveau ticket
    </a> -->

@php
    $role = strtolower(auth()->user()->role?->name);
@endphp

@if(in_array($role, ['manager', 'customer_service', 'supervisor']))
    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
        + Nouveau ticket
    </a>
@endif
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
<div style="max-height: 70vh; overflow-y: auto; border-radius: 0.5rem;">
            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Unité</th>
                        <th>Type</th>
                        <th>Descrition</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($tickets as $ticket)

                    @php

                        $statusColors = [
                            'OPEN' => 'warning',
                            'IN_PROGRESS' => 'primary',
                            'ON_HOLD' => 'secondary',
                            'RESOLVED' => 'success',
                            'CLOSED' => 'dark',
                            'ASSIGNED_TO_TECHNICIANS' => 'info',
                            'REOPENED' => 'danger',
                            'TRANSFERRED' => 'warning'
                        ];

                        $statusLabels = [
                            'OPEN' => 'Ouvert',
                            'ASSIGNED_TO_TECHNICIANS' => 'Assigné',
                            'IN_PROGRESS' => 'En cours',
                            'ON_HOLD' => 'En attente',
                            'RESOLVED' => 'Résolu',
                            'CLOSED' => 'Clôturé',
                            'REOPENED' => 'Réouvert',
                            'TRANSFERRED' => 'Transferé'
];

                        // Si pas encore pris en charge
                        $deadline = $ticket->started_at
                            ? $ticket->resolution_due_at
                            : $ticket->response_due_at;

                    @endphp

                    <tr>

                        {{-- ID --}}
                        <td>
                            <strong>#{{ $ticket->id }}</strong>
                        </td>

                        {{-- UNIT --}}
                        <td>
                            {{ $ticket->unit->name ?? '-' }}
                        </td>


                        {{-- TYPE --}}
                        <td>
                            {{ $ticket->type->name ?? '-' }}
                        </td>

                        {{-- DESCRIPTION --}}
                        <td style="max-width: 300px;">
                            <div class="text-truncate">
                                {{ $ticket->description }}
                            </div>
                        </td>

                        {{-- STATUS --}}
                        <td>
    <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
        {{ $statusLabels[$ticket->status] ?? $ticket->status }}
    </span>
</td>

                        
                        {{-- ACTION --}}
                        <td>
                            <a href="{{ route('tickets.show', $ticket->id) }}"
                               class="btn btn-sm btn-primary">
                                Voir
                            </a>
                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table></div>

        </div>

    </div>
</div>

@endsection
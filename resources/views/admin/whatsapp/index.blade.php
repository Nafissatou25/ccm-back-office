@extends('layouts.app')
@section('title', 'Demandes WhatsApp')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Demandes WhatsApp</h3>
    <!-- <span class="badge bg-success fs-6">
        {{ $requests->where('status', 'COMPLETED')->count() }} à traiter
    </span> -->
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Réf.</th>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Aperçu</th>
                        <th>Reçu le</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $wr)
                        @php
                            $badges = [
                                'IN_PROGRESS' => ['secondary', 'En cours'],
                                'COMPLETED'   => ['warning',   'À traiter'],
                                'CONVERTED'   => ['success',   'Converti'],
                                'CANCELLED'   => ['danger',    'Annulé'],
                            ];
                            [$color, $label] = $badges[$wr->status] ?? ['secondary', $wr->status];
                        @endphp
                        <tr>
                            <td><strong>#WA-{{ str_pad($wr->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ $wr->full_name }}</td>
                            <td>{{ $wr->contact_phone }}</td>
                            <td>{{ Str::limit($wr->description, 50) }}</td>
                            <td>{{ $wr->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-{{ $color }}">{{ $label }}</span></td>
                            <td>
                                <a href="{{ route('admin.whatsapp.show', $wr) }}" class="btn btn-sm btn-primary">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Aucune demande WhatsApp</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id . ' - Détail')

@section('content')
<div class="container-fluid px-0">
    {{-- En-tête et actions --}}
    <div class="px-3 px-lg-4">   {{-- seulement ici pour le padding --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

    <!-- {{-- En-tête avec statut et actions rapides --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 flex-wrap"> -->
                {{-- Bouton retour --}}
    <!-- <a href="{{ route('tickets.index') }}"
       class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center"
       style="width: 38px; height: 38px;"
       title="Retour">
        <i class="bi bi-chevron-left fs-3"></i>
    </a> -->
                <h1 class="display-6 fw-bold mb-0">
                    Ticket #{{ $ticket->id }}
                    <!-- <button class="btn btn-outline-secondary btn-sm ms-2" onclick="navigator.clipboard.writeText('{{ $ticket->id }}')" title="Copier l'ID">
                        <i class="bi bi-clipboard"></i>
                    </button> -->
                </h1>
                <div class="dropdown">
    <button class="btn btn-primary" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-three-dots-vertical me-1"></i> Actions
    </button>

    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">

        {{-- Commentaire --}}
        @if(in_array('comment', $actions))
        <li>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#commentModal">
                <i class="bi bi-chat-dots me-2"></i> Commentaire
            </button>
        </li>
        @endif

        {{-- Document --}}
        @if(in_array('document', $actions))
        <li>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#documentModal">
                <i class="bi bi-paperclip me-2"></i> Document
            </button>
        </li>
        @endif

        {{-- Assigner --}}
        @if(in_array('assign', $actions))
        <li>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#assignModal">
                <i class="bi bi-people me-2"></i> Assigner
            </button>
        </li>
        @endif

        {{-- Start / Prendre en charge --}}
        @if(in_array('start', $actions))
        <li>
            <form method="POST"
      action="{{ route('tickets.start', $ticket->id) }}"
      onsubmit="return confirm('Confirmer le début du traitement du ticket ?')">
                @csrf @method('PATCH')
                <button class="dropdown-item">
                    <i class="bi bi-play-circle me-2"></i> Démarrer le traitement
                </button>
            </form>
        </li>
        @endif

        {{-- Resolve --}}
        @if(in_array('resolve', $actions))
        <li>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#resolveModal">
                <i class="bi bi-check2-circle me-2"></i> Résoudre
            </button>
        </li>
        @endif

        {{-- Close --}}
        @if(in_array('close', $actions))
        <li>
            <form method="POST" action="{{ route('tickets.close', $ticket->id) }}">
                @csrf @method('PATCH')
                <button class="dropdown-item">
                    <i class="bi bi-lock me-2"></i> Clôturer
                </button>
            </form>
        </li>
        @endif

        {{-- Reopen --}}
        @if(in_array('reopen', $actions))
        <li>
            <form method="POST" action="{{ route('tickets.reopen', $ticket->id) }}">
                @csrf @method('PATCH')
                <button type="button" class="dropdown-item"
            data-bs-toggle="modal"
            data-bs-target="#reopenModal">
        <i class="bi bi-arrow-counterclockwise me-2"></i>
        Réouvrir
    </button>
            </form>
        </li>
        @endif

        @if(in_array('hold', $actions))
<li>
    <form method="POST"
          action="{{ route('tickets.hold', $ticket->id) }}">
        @csrf
        @method('PATCH')

        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#holdModal" type="button">
    <i class="bi bi-pause-circle me-2"></i> Mettre en attente
</button>
    </form>
</li>
@endif

@if(in_array('resume', $actions))
<li>
    <form method="POST"
          action="{{ route('tickets.resume', $ticket->id) }}">
        @csrf
        @method('PATCH')

        <button class="dropdown-item">
            <i class="bi bi-play-circle me-2"></i>
            Reprendre le traitement
        </button>
    </form>
</li>
@endif

        {{-- Transfer --}}
        @if(in_array('transfer', $actions))
        <li>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="bi bi-send me-2"></i> Transférer
            </button>
        </li>
        @endif

    </ul>
</div>
            </div>
            <p class="text-muted mt-2 mb-0">
                Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}
            </p>
            <p class="text-muted mt-2 mb-0">
                par <strong>{{ $ticket->user?->name ?? 'Utilisateur inconnu' }}</strong>
            </p>
        </div>
        

        {{-- Actions principales --}}
<!-- <div class="row mt-2 g-3 mx-0">   {{-- mx-0 supprime les marges négatives de Bootstrap --}}
        <div class="col-lg-5 px-3 px-lg-4">
        <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#commentModal">
            <i class="bi bi-chat-dots me-1"></i> Commentaire
        </button>

        <button class="btn btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#documentModal">
            <i class="bi bi-paperclip me-1"></i> Document
        </button>

        @if(!in_array($ticket->status, ['CLOSED', 'RESOLVED']))
        <button class="btn btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#assignModal">
            <i class="bi bi-people me-1"></i> Assigner
        </button>
        @endif

        @if($ticket->status === 'OPEN' || $ticket->status === 'ASSIGNED_TO_TECHNICIANS')
        <form method="POST" action="{{ route('tickets.start', $ticket->id) }}" class="d-inline-block" onsubmit="return confirm('Confirmer la prise en charge ?');">
            @csrf @method('PATCH')
            <button class="btn btn-warning rounded-pill text-dark">
                <i class="bi bi-play-circle me-1"></i> Prendre en charge
            </button>
        </form>
        @endif

        @if($ticket->status === 'IN_PROGRESS')
        <button class="btn btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#resolveModal">
            <i class="bi bi-check2-circle me-1"></i> Résoudre
        </button>
        @endif

        @if($ticket->status === 'RESOLVED')
        <form method="POST" action="{{ route('tickets.close', $ticket->id) }}" class="d-inline-block" onsubmit="return confirm('Clôturer définitivement ?');">
            @csrf @method('PATCH')
            <button class="btn btn-dark rounded-pill">
                <i class="bi bi-lock me-1"></i> Clôturer
            </button>
        </form>
        @endif
    </div>
</div> -->

    <div class="row mt-2 g-3">
        {{-- Colonne gauche : infos ticket --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 hover-shadow transition">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-primary me-2"></i> Détails</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3">
    <strong>Statut :</strong><br>

    @php
        $statusColors = [
    'OPEN' => 'warning',
    'IN_PROGRESS' => 'info',
    'TRANSFERRED' => 'secondary',
    'ON_HOLD' => 'dark',
    'REOPENED' => 'danger',
    'RESOLVED' => 'success',
    'CLOSED' => 'secondary',
    'ASSIGNED_TO_TECHNICIANS' => 'info',
];

$statusLabels = [
    'OPEN' => 'Ouvert',
    'ASSIGNED_TO_TECHNICIANS' => 'Assigné',
    'IN_PROGRESS' => 'En cours',
    'ON_HOLD' => 'En attente',
    'RESOLVED' => 'Résolu',
    'CLOSED' => 'Clôturé',
    'REOPENED' => 'Réouvert',
    'TRANSFERRED' => 'Transféré'
];

        $statusLabels = [
    'OPEN' => 'Ouvert',
    'ASSIGNED_TO_TECHNICIANS' => 'Assigné',
    'IN_PROGRESS' => 'En cours',
    'ON_HOLD' => 'En attente',
    'RESOLVED' => 'Résolu',
    'CLOSED' => 'Clôturé',
    'REOPENED' => 'Réouvert',
    'TRANSFERRED' => 'Transféré'
];
    @endphp

    <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
        {{ $statusLabels[$ticket->status] ?? $ticket->status }}
    </span>
</li>
                        <li class="mb-3"><strong>Description :</strong><br>{{ $ticket->description }}</li>
                        <li class="mb-3"><strong>Unité :</strong><br>{{ $ticket->unit->name ?? 'Non définie' }}</li>
                        <li class="mb-3">
    <strong>Pièce jointe :</strong><br>

    @if($ticket->attachment_path)
        <a href="{{ asset('storage/'.$ticket->attachment_path) }}"
           target="_blank"
           class="btn btn-sm btn-outline-primary rounded-pill mt-2">

            <i class="bi bi-paperclip me-1"></i>
            Voir le fichier
        </a>
    @else
        <span class="text-muted">Aucune pièce jointe</span>
    @endif
</li>
                       <li class="mb-3">
    <strong>Assigné à :</strong><br>
    @if($ticket->technicians->count())
        {{ $ticket->technicians->pluck('name')->implode(', ') }}
    @else
        <span class="text-muted">Non assigné</span>
    @endif
</li>
                        <!-- <li class="mb-3"><strong>Prise en charge attendue le :</strong><br>{{ $ticket->response_due_at ? \Carbon\Carbon::parse($ticket->response_due_at)->format('d/m/Y H:i') : 'Non définie' }}</li> -->
                        <li><strong>Résolution attendue le :</strong><br>{{ $ticket->resolution_due_at ? \Carbon\Carbon::parse($ticket->resolution_due_at)->format('d/m/Y H:i') : 'Non définie' }}</li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <!-- <h5 class="fw-bold mb-3"><i class="bi bi-hourglass-split text-warning me-2"></i> SLA</h5> -->
                    <div class="d-flex justify-content-between">
                        <!-- <span>TTO (prise en charge)</span>
                        <span class="fw-bold">{{ $ticket->time_to_own ?? 'En attente' }}</span> -->
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <!-- <span>TTR (résolution)</span>
                        <span class="fw-bold">{{ $ticket->time_to_resolve ?? 'En cours' }}</span> -->
                    </div>
                    @if($ticket->client)
    <li><strong>Client :</strong> {{ $ticket->client->firstname }} {{ $ticket->client->name }}</li>
    <li><strong>Téléphone :</strong> {{ $ticket->client->phone }}</li>
    <li><strong>Contrat :</strong> {{ $ticket->client->contract_number ?? '—' }}</li>
@endif
                </div>
                
            </div>
            

            {{-- Carte SLA / chrono --}}
            <!-- <div class="card border-0 shadow-sm rounded-4 mt-4 bg-light">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-hourglass-split text-warning me-2"></i> SLA</h5>
                    <div class="d-flex justify-content-between">
                        <span>TTO (prise en charge)</span>
                        <span class="fw-bold">{{ $ticket->time_to_own ?? 'En attente' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span>TTR (résolution)</span>
                        <span class="fw-bold">{{ $ticket->time_to_resolve ?? 'En cours' }}</span>
                    </div>
                </div>
            </div> -->
        </div>

        {{-- Colonne droite : timeline conversationnelle --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i> Activités</h5>
                    <div class="timeline-custom" style="max-height: 70vh; overflow-y: auto; padding-right: 8px;">
    @foreach($activities as $activity)

        {{-- RÉOUVERTURE --}}
        @if($activity['type'] === 'reopen')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-arrow-counterclockwise text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <p class="mb-1">{{ $activity['data']->message }}</p>
                @if($activity['data']->attachment_path)
                    <a href="{{ asset('storage/' . $activity['data']->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-warning rounded-pill mt-1">
                        <i class="bi bi-paperclip me-1"></i> Voir la pièce jointe
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- RÉSOLUTION --}}
        @if($activity['type'] === 'resolution')
        <div class="d-flex justify-content-end gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-check2-circle text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-success text-white rounded-4 p-3 shadow-sm">
                <small class="opacity-75 d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <div class="fw-bold mb-2">Ticket résolu</div>
                <p class="mb-2">{{ $activity['data']->message }}</p>
                @if($activity['data']->attachment_path)
                    <a href="{{ asset('storage/' . $activity['data']->attachment_path) }}" target="_blank" class="btn btn-sm btn-light rounded-pill mt-1">
                        <i class="bi bi-paperclip me-1"></i> Voir la pièce jointe
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- COMMENTAIRE --}}
        @if($activity['type'] === 'comment')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-chat text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <p class="mb-1">{{ $activity['data']->message }}</p>
                @if(isset($activity['data']->attachment_path) && $activity['data']->attachment_path)
                    <a href="{{ asset('storage/' . $activity['data']->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill mt-1">
                        <i class="bi bi-paperclip me-1"></i> Voir la pièce jointe
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- DOCUMENT --}}
        @if($activity['type'] === 'document')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-file-earmark-text text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->uploader->name }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <a href="{{ asset('storage/'.$activity['data']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill">
                    <i class="bi bi-download me-1"></i> {{ $activity['data']->file_name }}
                </a>
            </div>
        </div>
        @endif

        {{-- MISE EN ATTENTE --}}
        @if($activity['type'] === 'hold')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-pause text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <p class="mb-1">{{ $activity['data']->message }}</p>
                @if($activity['data']->attachment_path)
                    <a href="{{ asset('storage/' . $activity['data']->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-warning rounded-pill mt-1">
                        <i class="bi bi-paperclip me-1"></i> Voir la pièce jointe
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- TRANSFERT --}}
        @if($activity['type'] === 'transfer')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-send text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <p class="mb-1">{{ $activity['data']->message }}</p>
                @if($activity['data']->attachment_path)
                    <a href="{{ asset('storage/' . $activity['data']->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill mt-1">
                        <i class="bi bi-paperclip me-1"></i> Fiche problème
                    </a>
                @endif
                @if($activity['data']->attachment2_path)
                    <a href="{{ asset('storage/' . $activity['data']->attachment2_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill mt-1 ms-2">
                        <i class="bi bi-paperclip me-1"></i> Ordre de service
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ASSIGNATION TECHNICIENS --}}
        @if($activity['type'] === 'assignment')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-people text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <p class="mb-1">{{ $activity['data']->message }}</p>
            </div>
        </div>
        @endif

        {{-- DÉMARRAGE --}}
        @if($activity['type'] === 'start')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-play-circle-fill text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <p class="mb-1">Traitement démarré</p>
            </div>
        </div>
        @endif

        {{-- REPRISE --}}
        @if($activity['type'] === 'resume')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-play-circle text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <p class="mb-1">Traitement repris</p>
            </div>
        </div>
        @endif

        {{-- CLÔTURE --}}
        @if($activity['type'] === 'close')
        <div class="d-flex gap-3 mb-4">
            <div class="flex-shrink-0">
                <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                    <i class="bi bi-lock-fill text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                <small class="text-muted d-block mb-1">
                    {{ $activity['data']->user->name ?? 'Utilisateur' }}
                    <span class="float-end">{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</span>
                </small>
                <p class="mb-1">Ticket clôturé</p>
            </div>
        </div>
        @endif

    @endforeach
</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODALS ================= --}}

{{-- Modal Résolution --}}
<div class="modal fade" id="resolveModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tickets.resolve.', $ticket->id) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-check2-circle me-2"></i> Résoudre le ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description de la résolution</label>
                        <textarea name="resolution_description" class="form-control rounded-3" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pièce jointe (optionnel)</label>
                        <input type="file" name="resolution_attachment" class="form-control rounded-3">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Valider la résolution</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Commentaire --}}
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tickets.comments.store', $ticket->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-chat-dots me-2"></i> Ajouter un commentaire</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message</label>
                        <textarea name="message" class="form-control rounded-3" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pièce jointe</label>
                        <input type="file" name="attachment" class="form-control rounded-3">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Envoyer</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Transfert unifié --}}
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('tickets.transfer', $ticket->id) }}" enctype="multipart/form-data" id="transferForm">
            @csrf @method('PATCH')
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Transférer le ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Type de transfert --}}
                    <div class="mb-3">
                        <label class="form-label">Type de transfert</label>
                        <select name="target_type" id="targetType" class="form-select" required>
                            <option value="user">Autre unité ENEO</option>
                            <option value="company">Entreprise externe</option>
                        </select>
                    </div>

                    {{-- Champs communs (motif) --}}
                    <div class="mb-3">
                        <label>Motif du transfert</label>
                        <textarea name="reason" class="form-control" rows="2"></textarea>
                    </div>

                    {{-- SECTION POUR AUTRE UNITÉ ENEO --}}
                    <div id="userSection">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Unité</label>
                                <select id="unitTransfer" class="form-select">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Agence</label>
                                <select id="agencyTransfer" class="form-select">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($agencies as $agency)
                                        <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Superviseur (ENEO)</label>
                            <select name="user_id" id="supervisorTransfer" class="form-select">
                                <option value="">-- Sélectionner d'abord unité et agence --</option>
                            </select>
                        </div>
                    </div>

                    {{-- SECTION POUR ENTREPRISE EXTERNE --}}
                    <div id="companySection" style="display: none;">
                        <div class="mb-3">
                            <label>Entreprise</label>
                            <select name="company_id" id="companySelect" class="form-select">
    <option value="">-- Sélectionner --</option>
    @foreach($companies as $company)
        @if($company->name !== 'ENEO')
            <option value="{{ $company->id }}">{{ $company->name }}</option>
        @endif
    @endforeach
</select>
                        </div>
                        <div class="mb-3">
                            <label>Superviseur (entreprise)</label>
                            <select name="user_id" id="companySupervisorSelect" class="form-select" disabled>
                                <option value="">-- Sélectionnez d'abord une entreprise --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Fiche problème</label>
                            <input type="file" name="attachment1" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label>Ordre de service</label>
                            <input type="file" name="attachment2" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Transférer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="holdModal" tabindex="-1">
    <div class="modal-dialog">

        <form method="POST"
              action="{{ route('tickets.hold', $ticket->id) }}"
              enctype="multipart/form-data">

            @csrf
            @method('PATCH')

            <div class="modal-content">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Mettre en attente</h5>
                </div>

                <div class="modal-body">

                    <textarea name="reason"
                              class="form-control mb-3"
                              placeholder="Motif obligatoire"
                              required></textarea>

                    <input type="file"
                           name="attachment"
                           class="form-control">

                </div>

                <div class="modal-footer">
                    <button class="btn btn-warning">Valider</button>
                </div>

            </div>

        </form>

    </div>
</div>

<div class="modal fade" id="reopenModal" tabindex="-1">
    <div class="modal-dialog">

        <form method="POST"
              action="{{ route('tickets.reopen', $ticket->id) }}"
              enctype="multipart/form-data">

            @csrf
            @method('PATCH')

            <div class="modal-content">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Réouvrir le ticket</h5>
                </div>

                <div class="modal-body">

                    <textarea name="reason"
                              class="form-control mb-3"
                              placeholder="Motif de réouverture"
                              required></textarea>

                    <input type="file"
                           name="attachment"
                           class="form-control">

                </div>

                <div class="modal-footer">
                    <button class="btn btn-warning">Réouvrir</button>
                </div>

            </div>

        </form>

    </div>
</div>

{{-- Modal Document --}}
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tickets.documents.store', $ticket->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-info text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-paperclip me-2"></i> Ajouter un document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom du document</label>
                        <input type="text" name="name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fichier</label>
                        <input type="file" name="document" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info rounded-pill px-4 text-white">Ajouter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade"
     id="assignModal"
     tabindex="-1">

    <div class="modal-dialog">

        <form method="POST"
       action="{{ route('tickets.assign', $ticket->id) }}">

    @csrf
    @method('PATCH')

    <div class="modal-content border-0 rounded-4">

        <div class="modal-header">

            <h5 class="modal-title">
                Assigner techniciens
            </h5>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
            </button>

        </div>

        <div class="modal-body">

            <label class="form-label">
                Sélectionner techniciens
            </label>

            <select name="technicians[]"
                    class="form-select"
                    multiple
                    required>

                @foreach($technicians as $technician)

                    <option value="{{ $technician->id }}">
                        {{ $technician->name }}
                    </option>

                @endforeach

            </select>

        </div>

        <div class="modal-footer">

            <button type="submit"
                    class="btn btn-primary rounded-pill">

                Assigner

            </button>

        </div>

    </div>

</form>

    </div>

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const targetType = document.getElementById('targetType');
    const userSection = document.getElementById('userSection');
    const companySection = document.getElementById('companySection');
    const unitSelect = document.getElementById('unitTransfer');
    const agencySelect = document.getElementById('agencyTransfer');
    const supervisorSelect = document.getElementById('supervisorTransfer');
    const companySelect = document.getElementById('companySelect');
    const companySupervisorSelect = document.getElementById('companySupervisorSelect');

    const allSupervisors = @json($supervisors);
    const supervisorsByCompany = @json($supervisorsByCompany);

    // Filtrage superviseurs ENEO
    function loadSupervisors() {
        const unitId = unitSelect.value;
        const agencyId = agencySelect.value;
        supervisorSelect.innerHTML = '<option value="">-- Sélectionner --</option>';
        if (!unitId || !agencyId) return;
        const filtered = allSupervisors.filter(sup => 
            sup.unit_id == unitId && sup.agency_id == agencyId && sup.company_id == 1
        );
        filtered.forEach(sup => {
            const option = document.createElement('option');
            option.value = sup.id;
            option.textContent = sup.name;
            supervisorSelect.appendChild(option);
        });
    }

    unitSelect.addEventListener('change', loadSupervisors);
    agencySelect.addEventListener('change', loadSupervisors);

    // Chargement superviseurs d'entreprise
    function loadCompanySupervisors() {
        const companyId = companySelect.value;
        if (!companyId) {
            companySupervisorSelect.innerHTML = '<option value="">-- Sélectionnez une entreprise --</option>';
            companySupervisorSelect.disabled = true;
            return;
        }
        const supervisors = supervisorsByCompany[companyId] || [];
        companySupervisorSelect.disabled = false;
        let options = '<option value="">-- Sélectionner --</option>';
        supervisors.forEach(sup => {
            options += `<option value="${sup.id}">${sup.name}</option>`;
        });
        companySupervisorSelect.innerHTML = options;
    }

    companySelect.addEventListener('change', loadCompanySupervisors);

    // Basculer l'affichage des sections et gérer les champs requis
    function toggleSections() {
        const isUser = targetType.value === 'user';
        userSection.style.display = isUser ? 'block' : 'none';
        companySection.style.display = isUser ? 'none' : 'block';
        
        // Gérer les fichiers requis pour l'entreprise externe
        const fileInputs = document.querySelectorAll('#companySection input[type="file"]');
        if (isUser) {
            fileInputs.forEach(input => {
                input.removeAttribute('required');
                input.value = '';
            });
            // Désactiver le select superviseur entreprise
            companySupervisorSelect.disabled = true;
        } else {
            fileInputs.forEach(input => input.setAttribute('required', 'required'));
        }
    }

    targetType.addEventListener('change', toggleSections);
    toggleSections();
});
</script>
@endpush
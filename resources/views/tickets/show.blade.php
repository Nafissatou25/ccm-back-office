@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id . ' - Détail')

@section('content')
<div class="container-fluid px-0">
    {{-- En-tête --}}
    <div class="px-3 px-lg-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1 class="display-6 fw-bold mb-0">
                        <i class="mdi mdi-ticket me-2 text-primary"></i>
                        Ticket #{{ $ticket->id }}
                        @if($ticket->is_urgent == 1)
                            <span class="badge bg-danger-subtle text-danger ms-2 px-3 py-2 rounded-pill">
                                <i class="mdi mdi-alert me-1"></i> Urgent
                            </span>
                        @endif
                        @php
                            $statusColors = [
                                'OPEN' => 'warning',
                                'IN_PROGRESS' => 'info',
                                'TRANSFERRED' => 'secondary',
                                'ON_HOLD' => 'dark',
                                'REOPENED' => 'orange',
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
                            $statusColor = $statusColors[$ticket->status] ?? 'secondary';
                        @endphp
                        <!-- <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} px-3 py-2 rounded-pill ms-2">
                            {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                        </span> -->
                    </h1>
                </div>
                <p class="text-muted mt-2 mb-0">
                    <i class="mdi mdi-calendar-clock me-1"></i>
                    Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}
                    par <strong>{{ $ticket->user?->name ?? 'Utilisateur inconnu' }}</strong>
                </p>
            </div>

            {{-- Actions rapides --}}
            <div class="d-flex flex-wrap gap-2 mt-3 mt-lg-0">
                @if(in_array('assign', $actions))
                <button class="btn btn-outline-success rounded-pill btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#assignModal">
                    <i class="mdi mdi-account-plus me-1"></i> Assigner
                </button>
                @endif

                @if(in_array('start', $actions))
                <button class="btn btn-outline-info rounded-pill btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#documentModal">
                    <i class="mdi mdi-play-circle me-1"></i> Démarrer
                </button>
                @endif

                @if(in_array('hold', $actions))
                <button class="btn btn-outline-dark rounded-pill btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#holdModal">
                    <i class="mdi mdi-pause-circle me-1"></i> En attente
                </button>
                @endif

                @if(in_array('resume', $actions))
                <form method="POST" action="{{ route('tickets.resume', $ticket->id) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-info rounded-pill btn-sm px-3">
                        <i class="mdi mdi-play-circle me-1"></i> Reprendre
                    </button>
                </form>
                @endif

                @if(in_array('resolve', $actions))
                <button class="btn btn-outline-success rounded-pill btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#resolveModal">
                    <i class="mdi mdi-check-circle me-1"></i> Résoudre
                </button>
                @endif

                @if(in_array('reopen', $actions))
                <button class="btn btn-outline-warning rounded-pill btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#reopenModal">
                    <i class="mdi mdi-refresh me-1"></i> Réouvrir
                </button>
                @endif

                @if(in_array('close', $actions))
                <form method="POST" action="{{ route('tickets.close', $ticket->id) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-dark rounded-pill btn-sm px-3">
                        <i class="mdi mdi-lock me-1"></i> Clôturer
                    </button>
                </form>
                @endif

                @if(in_array('transfer', $actions))
                <button class="btn btn-outline-secondary rounded-pill btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#transferModal">
                    <i class="mdi mdi-send me-1"></i> Transférer
                </button>
                @endif

                @if(in_array('comment', $actions))
                <button class="btn btn-outline-primary rounded-pill btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#commentModal">
                    <i class="mdi mdi-chat me-1"></i> Commenter
                </button>
                @endif
            </div>
        </div>

        {{-- Contenu principal --}}
        <div class="row g-3">
            {{-- Colonne gauche : infos ticket --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="mdi mdi-information text-primary me-2"></i> Détails
                        </h5>

                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <strong>Statut</strong><br>
                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} px-3 py-2 rounded-pill">
                                    {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                                </span>
                            </li>

                            <li class="mb-3">
                                <strong>Description</strong><br>
                                <span class="text-muted">{{ $ticket->description }}</span>
                            </li>

                            <li class="mb-3">
                                <strong>Pièce jointe</strong><br>
                                @if($ticket->attachment_path)
                                    <a href="{{ asset('storage/'.$ticket->attachment_path) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary rounded-pill mt-1">
                                        <i class="mdi mdi-paperclip me-1"></i> Voir le fichier
                                    </a>
                                @else
                                    <span class="text-muted">Aucune pièce jointe</span>
                                @endif
                            </li>

                            <li class="mb-3">
                                <strong>Assigné à</strong><br>
                                @if($ticket->technicians->count())
                                    {{ $ticket->technicians->pluck('name')->implode(', ') }}
                                @else
                                    <span class="text-muted">Non assigné</span>
                                @endif
                            </li>

                            <li class="mb-3">
                                <strong>Résolution attendue le</strong><br>
                                @if($ticket->resolution_due_at)
                                    @php
                                        $isLate = $ticket->resolution_due_at && now()->greaterThan($ticket->resolution_due_at) && !in_array($ticket->status, ['RESOLVED', 'CLOSED']);
                                    @endphp
                                    <span class="{{ $isLate ? 'text-danger fw-bold' : '' }}">
                                        {{ $ticket->resolution_due_at->format('d/m/Y H:i') }}
                                        @if($isLate)
                                            <i class="mdi mdi-alarm text-danger ms-1"></i>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">Non définie</span>
                                @endif
                            </li>

                            @if($ticket->client)
                            <li class="mb-3">
                                <strong>Client</strong><br>
                                {{ $ticket->client->firstname }} {{ $ticket->client->name }}
                            </li>
                            <li class="mb-3">
                                <strong>Téléphone</strong><br>
                                {{ $ticket->client->phone }}
                            </li>
                            @endif
                        </ul>

                        {{-- Barre SLA --}}
                        @php
                            $total = $ticket->created_at->diffInMinutes($ticket->resolution_due_at ?? now()->addDays(5));
                            $elapsed = $ticket->created_at->diffInMinutes(now());
                            $percent = $total > 0 ? min(100, ($elapsed / $total) * 100) : 0;
                        @endphp
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Progression SLA</span>
                                <span class="fw-bold">{{ round($percent) }}%</span>
                            </div>
                            <div class="progress" style="height:8px; border-radius:4px;">
                                <div class="progress-bar {{ $percent > 90 ? 'bg-danger' : ($percent > 70 ? 'bg-warning' : 'bg-success') }}"
                                     style="width:{{ $percent }}%; border-radius:4px; transition: width 0.6s ease;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Colonne droite : timeline --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="mdi mdi-clock-history text-primary me-2"></i>
                            Activités
                            <span class="badge bg-light text-dark ms-2">{{ $activities->count() }}</span>
                        </h5>

                        <div class="timeline-custom" style="max-height:70vh; overflow-y:auto; padding-right:8px;">
                            @foreach($activities as $activity)
                                @php
                                    $iconMap = [
                                        'reopen' => ['icon' => 'mdi-refresh', 'color' => 'warning', 'label' => 'Réouverture'],
                                        'resolution' => ['icon' => 'mdi-check-circle', 'color' => 'success', 'label' => 'Résolution'],
                                        'comment' => ['icon' => 'mdi-chat', 'color' => 'primary', 'label' => 'Commentaire'],
                                        'document' => ['icon' => 'mdi-paperclip', 'color' => 'info', 'label' => 'Document'],
                                        'hold' => ['icon' => 'mdi-pause-circle', 'color' => 'secondary', 'label' => 'En attente'],
                                        'transfer' => ['icon' => 'mdi-send', 'color' => 'secondary', 'label' => 'Transfert'],
                                        'assignment' => ['icon' => 'mdi-account-plus', 'color' => 'success', 'label' => 'Assignation'],
                                        'start' => ['icon' => 'mdi-play-circle', 'color' => 'info', 'label' => 'Démarrage'],
                                        'resume' => ['icon' => 'mdi-play-circle', 'color' => 'info', 'label' => 'Reprise'],
                                        'close' => ['icon' => 'mdi-lock', 'color' => 'dark', 'label' => 'Clôture'],
                                    ];
                                    $type = $activity['type'];
                                    $meta = $iconMap[$type] ?? ['icon' => 'mdi-circle', 'color' => 'secondary', 'label' => $type];
                                    $isRight = in_array($type, ['resolution']);
                                    $bgColor = $meta['color'];
                                @endphp

                                <div class="d-flex gap-3 mb-4 {{ $isRight ? 'flex-row-reverse' : '' }}">
                                    {{-- Icône --}}
                                    <div class="flex-shrink-0">
                                        <div class="bg-{{ $bgColor }}-subtle rounded-circle d-flex align-items-center justify-content-center"
                                             style="width:42px;height:42px;">
                                            <i class="mdi {{ $meta['icon'] }} text-{{ $bgColor }}" style="font-size:20px;"></i>
                                        </div>
                                    </div>

                                    {{-- Contenu --}}
                                    <div class="flex-grow-1 bg-light rounded-4 p-3 shadow-sm">
                                        <small class="text-muted d-block mb-1">
                                            @php
                                                $userName = match($type) {
                                                    'document' => $activity['data']->uploader->name ?? 'Utilisateur',
                                                    default => $activity['data']->user->name ?? 'Utilisateur'
                                                };
                                            @endphp
                                            {{ $userName }}
                                            <span class="float-end">
                                                {{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}
                                            </span>
                                        </small>

                                        @if($type === 'resolution')
                                            <div class="fw-bold text-success mb-1">Ticket résolu</div>
                                        @elseif($type === 'start')
                                            <div class="fw-bold text-info mb-1">Traitement démarré</div>
                                        @elseif($type === 'resume')
                                            <div class="fw-bold text-info mb-1">Traitement repris</div>
                                        @elseif($type === 'close')
                                            <div class="fw-bold text-dark mb-1">Ticket clôturé</div>
                                        @endif

                                        @if(in_array($type, ['reopen', 'resolution', 'comment', 'hold', 'transfer', 'assignment']))
                                            <p class="mb-1">{{ $activity['data']->message }}</p>
                                        @endif

                                        @if($type === 'document')
                                            <p class="mb-1">
                                                {{ $activity['data']->description ?? $activity['data']->file_name }}
                                            </p>
                                        @endif

                                        {{-- Pièces jointes --}}
                                        @php
                                            $attachPath = $activity['data']->attachment_path ?? null;
                                            $attach2Path = $activity['data']->attachment2_path ?? null;
                                        @endphp
                                        @if($attachPath)
                                            <a href="{{ asset('storage/' . $attachPath) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-{{ $bgColor }} rounded-pill mt-1">
                                                <i class="mdi mdi-paperclip me-1"></i> Voir la pièce jointe
                                            </a>
                                        @endif
                                        @if($attach2Path)
                                            <a href="{{ asset('storage/' . $attach2Path) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-{{ $bgColor }} rounded-pill mt-1 ms-2">
                                                <i class="mdi mdi-paperclip me-1"></i> Ordre de service
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if($activities->isEmpty())
                                <div class="text-center py-4">
                                    <i class="mdi mdi-clock-alert-outline text-muted" style="font-size:48px;"></i>
                                    <p class="text-muted mt-2">Aucune activité pour ce ticket.</p>
                                </div>
                            @endif
                        </div>
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
                    <h5 class="modal-title fw-bold">
                        <i class="mdi mdi-check-circle me-2"></i> Rapport de résolution
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description des tâches effectuées</label>
                        <textarea name="resolution_description" class="form-control rounded-3" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pièce jointe (optionnel)</label>
                        <input type="file" name="resolution_attachment" class="form-control rounded-3">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="mdi mdi-check me-1"></i> Valider la résolution
                    </button>
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
                    <h5 class="modal-title fw-bold">
                        <i class="mdi mdi-chat me-2"></i> Ajouter un commentaire
                    </h5>
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
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="mdi mdi-send me-1"></i> Envoyer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Transfert --}}
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tickets.transfer', $ticket->id) }}" enctype="multipart/form-data" id="transferForm">
            @csrf @method('PATCH')
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-secondary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="mdi mdi-swap-horizontal me-2"></i> Transférer le ticket
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type de transfert</label>
                        <select name="target_type" id="targetType" class="form-select rounded-3" required>
                            <option value="user">Autre unité ENEO</option>
                            <option value="company">Entreprise externe</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motif du transfert</label>
                        <textarea name="reason" class="form-control rounded-3" rows="2" required></textarea>
                    </div>

                    <div id="userSection">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unité</label>
                                <select id="unitTransfer" class="form-select rounded-3">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Agence</label>
                                <select id="agencyTransfer" class="form-select rounded-3">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($agencies as $agency)
                                        <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Superviseur (ENEO)</label>
                            <select name="user_id" id="supervisorTransfer" class="form-select rounded-3">
                                <option value="">-- Sélectionner d'abord unité et agence --</option>
                            </select>
                        </div>
                    </div>

                    <div id="companySection" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Entreprise</label>
                            <select name="company_id" id="companySelect" class="form-select rounded-3">
                                <option value="">-- Sélectionner --</option>
                                @foreach($companies as $company)
                                    @if($company->name !== 'ENEO')
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Superviseur (entreprise)</label>
                            <select name="user_id" id="companySupervisorSelect" class="form-select rounded-3" disabled>
                                <option value="">-- Sélectionnez d'abord une entreprise --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fiche problème</label>
                            <input type="file" name="attachment1" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ordre de service</label>
                            <input type="file" name="attachment2" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="mdi mdi-check me-1"></i> Transférer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Hold --}}
<div class="modal fade" id="holdModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tickets.hold', $ticket->id) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-secondary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="mdi mdi-pause-circle me-2"></i> Mettre en attente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motif</label>
                        <textarea name="reason" class="form-control rounded-3" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pièce jointe (optionnel)</label>
                        <input type="file" name="attachment" class="form-control rounded-3">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-secondary rounded-pill px-4">
                        <i class="mdi mdi-check me-1"></i> Valider
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Reopen --}}
<div class="modal fade" id="reopenModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tickets.reopen', $ticket->id) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-warning text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="mdi mdi-refresh me-2"></i> Réouvrir le ticket
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motif de réouverture</label>
                        <textarea name="reason" class="form-control rounded-3" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pièce jointe (optionnel)</label>
                        <input type="file" name="attachment" class="form-control rounded-3">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4">
                        <i class="mdi mdi-check me-1"></i> Réouvrir
                    </button>
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
                    <h5 class="modal-title fw-bold">
                        <i class="mdi mdi-paperclip me-2"></i> Données d'inspection
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Décrivez la situation</label>
                        <input type="text" name="name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fichier</label>
                        <input type="file" name="document" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info rounded-pill px-4 text-white">
                        <i class="mdi mdi-check me-1"></i> Ajouter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Assigner --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tickets.assign', $ticket->id) }}">
            @csrf @method('PATCH')
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="mdi mdi-account-plus me-2"></i> Assigner des techniciens
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-semibold">Sélectionner les techniciens</label>
                    <select name="technicians[]" class="form-select rounded-3" multiple required>
                        @foreach($technicians as $technician)
                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs techniciens.</div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="mdi mdi-check me-1"></i> Assigner
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

        function toggleSections() {
            const isUser = targetType.value === 'user';
            userSection.style.display = isUser ? 'block' : 'none';
            companySection.style.display = isUser ? 'none' : 'block';

            const fileInputs = document.querySelectorAll('#companySection input[type="file"]');
            if (isUser) {
                fileInputs.forEach(input => {
                    input.removeAttribute('required');
                    input.value = '';
                });
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

@push('styles')
<style>
    .bg-warning-subtle { background-color: #fff3cd !important; }
    .bg-danger-subtle { background-color: #f8d7da !important; }
    .bg-success-subtle { background-color: #d1e7dd !important; }
    .bg-info-subtle { background-color: #cff4fc !important; }
    .bg-primary-subtle { background-color: #cfe2ff !important; }
    .bg-secondary-subtle { background-color: #e9ecef !important; }
    .bg-dark-subtle { background-color: #dee2e6 !important; }
    .bg-orange-subtle { background-color: #ffe5d0 !important; }

    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-success { color: #198754 !important; }
    .text-info { color: #0dcaf0 !important; }
    .text-primary { color: #0d6efd !important; }
    .text-secondary { color: #6c757d !important; }
    .text-dark { color: #212529 !important; }
    .text-orange { color: #fd7e14 !important; }

    .rounded-4 { border-radius: 0.75rem !important; }
    .rounded-top-4 { border-top-left-radius: 0.75rem !important; border-top-right-radius: 0.75rem !important; }
    .rounded-bottom-4 { border-bottom-left-radius: 0.75rem !important; border-bottom-right-radius: 0.75rem !important; }

    .timeline-custom::-webkit-scrollbar {
        width: 6px;
    }
    .timeline-custom::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .timeline-custom::-webkit-scrollbar-thumb {
        background: #ced4da;
        border-radius: 10px;
    }
    .timeline-custom::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }

    .progress-bar {
        transition: width 0.6s ease;
    }
</style>
@endpush
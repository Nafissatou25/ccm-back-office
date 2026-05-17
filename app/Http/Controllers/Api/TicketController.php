<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Sla;
use App\Services\ResponsibleResolver;
use App\Http\Controllers\Controller;
use Carbon\Carbon;


class TicketController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'unit_id' => 'required|exists:units,id',
        'type_id' => 'required|exists:types,id',
        'agency_id' => 'required|exists:agencies,id',
        'description' => 'required|string',

        'contract_number' => 'nullable|string',

        'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
    ]);

    // Vérifier cohérence type/unité
    $typeValid = \App\Models\Type::where('id', $request->type_id)
        ->where('unit_id', $request->unit_id)
        ->exists();

    if (!$typeValid) {
        return response()->json(['message' => 'Type invalide'], 422);
    }

    // Trouver responsable
    $responsible = ResponsibleResolver::resolve(
        $request->agency_id,
        $request->unit_id
    );

    if (!$responsible) {
        return response()->json([
            'message' => 'Aucun responsable trouvé'
        ], 422);
    }

    // 📎 Upload fichier
    $path = null;

    if ($request->hasFile('attachment')) {
        $path = $request->file('attachment')->store('tickets', 'public');
    }

    // Création ticket
    $ticket = Ticket::create([
        'unit_id' => $request->unit_id,
        'type_id' => $request->type_id,
        'agency_id' => $request->agency_id,
        'assigned_to' => $responsible->user_id,
        'description' => $request->description,
        'contract_number' => $request->contract_number,
        'attachment_path' => $path,
        'status' => 'OPEN',
        'priority' => $request->priority
        
    ]);
//     $sla = Sla::where('priority', $ticket->priority)
//     ->where('is_active', true)
//     ->first();
//     if ($sla) {

//     $ticket->update([

//         'sla_id' => $sla->id,

//         'response_due_at' =>
//             now()->addMinutes($sla->response_time),

//         'resolution_due_at' =>
//             now()->addMinutes($sla->resolution_time),
//     ]);
// }
// $now = Carbon::now();

// $responseDue = $now->copy()->addMinutes($sla->response_time);
// $resolutionDue = $now->copy()->addMinutes($sla->resolution_time);

//     return response()->json([
//         'message' => 'Ticket créé',
//         'ticket' => $ticket
//     ]);
}

public function assignTechnicians(Request $request, Ticket $ticket)
{
    $request->validate([
        'technicians' => 'required|array',
        'technicians.*' => 'exists:users,id',
    ]);

    // sécurité métier : seul le responsable peut assigner
    if (!auth()->check()) {
    return response()->json(['message' => 'Non authentifié'], 401);
}

$user = auth()->user();

if (!in_array($user->role_id, [3]) 
    && $ticket->assigned_to != $user->id) {
    return response()->json(['message' => 'Accès refusé'], 403);
}
    // assignation multiple
    $ticket->technicians()->sync($request->technicians);

    
    // changement de statut
    $ticket->status = 'ASSIGNED_TO_TECHNICIANS';
    $ticket->save();

    return response()->json([
        'message' => 'Techniciens assignés avec succès',
        'ticket' => $ticket->load('technicians')
    ]);
}

public function start(Ticket $ticket)
{
    $user = auth()->user();

    // Vérifier que le user est technicien du ticket
    if (!$ticket->technicians()->where('users.id', $user->id)->exists()) {
        return response()->json([
            'message' => 'Non autorisé'
        ], 403);
    }

    // Empêcher double démarrage
    if ($ticket->status === 'IN_PROGRESS') {
        return response()->json([
            'message' => 'Ticket déjà en cours'
        ], 422);
    }

    $ticket->status = 'IN_PROGRESS';
    $ticket->started_at = now();
    $ticket->taken_by = $user->id;
    $ticket->save();

    return response()->json([
        'message' => 'Ticket démarré',
        'ticket' => $ticket
    ]);
}

public function resolve(Request $request, Ticket $ticket)
{
    $request->validate([
        'resolution_note' => 'required|string|min:5'
    ]);

    $user = auth()->user();

    // Vérifier que le technicien fait partie du ticket
    if (
        !$ticket->technicians()
            ->where('users.id', $user->id)
            ->exists()
    ) {
        return response()->json([
            'message' => 'Non autorisé'
        ], 403);
    }

    // Vérifier statut
    if ($ticket->status !== 'IN_PROGRESS') {
        return response()->json([
            'message' => 'Le ticket doit être en cours'
        ], 422);
    }

    $ticket->status = 'RESOLVED';

    $ticket->resolution_note = $request->resolution_note;

    $ticket->resolved_at = now();

    $ticket->resolved_by = $user->id;

    $ticket->save();

    return response()->json([
        'message' => 'Ticket résolu avec succès',
        'ticket' => $ticket
    ]);
}
public function closeTicket(Ticket $ticket)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'Non authentifié'], 401);
    }

    // Vérifier rôle autorisé
    if (!in_array($user->role_id, [3, 6])) {
        // 3 = SUPERVISOR (exemple)
        // 5 = CUSTOMER_SERVICE (exemple)
        return response()->json(['message' => 'Non autorisé'], 403);
    }

    // Vérifier statut actuel
    if ($ticket->status !== 'RESOLVED') {
        return response()->json([
            'message' => 'Le ticket doit être RESOLVED avant clôture'
        ], 422);
    }

    $ticket->status = 'CLOSED';
    $ticket->closed_at = now(); // recommandé
    $ticket->closed_by = $user->id; // recommandé
    $ticket->save();

    return response()->json([
        'message' => 'Ticket clôturé définitivement',
        'ticket' => $ticket
    ]);
}

public function changeStatus(Request $request, Ticket $ticket)
{
    $request->validate([
        'status' => 'required|string',
        'assigned_to' => 'nullable|exists:users,id'
    ]);

    $user = auth()->user();
    $status = $request->status;

    // rôles autorisés (exemple)
    $isSupervisor = $user->role_id == 3;
    $isCustomerService = $user->role_id == 5;

    if (!$isSupervisor && !$isCustomerService) {
        return response()->json(['message' => 'Non autorisé'], 403);
    }

    switch ($status) {

        case 'REOPENED':
    if ($ticket->status !== 'CLOSED') {
        return response()->json(['message' => 'Impossible de réouvrir'], 422);
    }

    $ticket->status = 'OPEN';
    $ticket->closed_by = null;
    $ticket->closed_at = null;

    break;

        case 'CANCELLED':
            if (in_array($ticket->status, ['CLOSED', 'CANCELLED'])) {
                return response()->json(['message' => 'Action impossible'], 422);
            }
            $ticket->status = 'CANCELLED';
            break;

        case 'REJECTED':
            $ticket->status = 'REJECTED';
            break;

        case 'ON_HOLD':
            $ticket->status = 'ON_HOLD';
            break;

        case 'TRANSFERRED':
            if (!$request->assigned_to) {
                return response()->json(['message' => 'assigned_to requis'], 422);
            }

            $ticket->assigned_to = $request->assigned_to;
            $ticket->status = 'TRANSFERRED';
            break;

        default:
            return response()->json(['message' => 'Statut invalide'], 422);
    }

    $ticket->save();

    return response()->json([
        'message' => 'Statut mis à jour',
        'ticket' => $ticket
    ]);
}
}
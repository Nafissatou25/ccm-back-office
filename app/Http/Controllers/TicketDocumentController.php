<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivity;
use Illuminate\Http\Request;

class TicketDocumentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'name'        => 'required|string',
            'document'    => 'required|file|max:4096',   // champ 'document' dans le formulaire
            'description' => 'nullable|string|max:255',
        ]);

        // Sauvegarde du fichier
        $path = $request->file('document')->store('documents', 'public');

        // Création du document
        $ticket->documents()->create([
            'file_name'   => $request->name,
            'file_path'   => $path,
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
        ]);

        // ── Passage automatique en IN_PROGRESS ──────────────
        if (!in_array($ticket->status, ['RESOLVED', 'CLOSED'])) {
            $ticket->update([
                'status'     => 'IN_PROGRESS',
                'started_at' => now(),
                'taken_by'   => auth()->id(),
            ]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id'   => auth()->id(),
                'type'      => 'start',
                'message'   => 'Traitement démarré suite à l\'ajout d\'un document',
            ]);
        }

        return back()->with('success', 'Document ajouté avec succès');
    }
}
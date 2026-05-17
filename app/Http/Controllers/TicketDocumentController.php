<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketDocumentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'name' => 'required|string',
            'document' => 'required|file|max:4096'
        ]);

        $path = $request->file('document')
                        ->store('documents', 'public');

        $ticket->documents()->create([
            'file_name' => $request->name,
            'file_path' => $path,
            'uploaded_by' => auth()->id()
        ]);
        return back()->with(
            'success',
            'Document ajouté avec succès'
        );
    }
}

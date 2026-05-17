<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketDocumentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'file' => 'required|file|max:5120'
        ]);

        $file = $request->file('file');

        $path = $file->store('tickets/documents', 'public');

        $document = TicketDocument::create([
            'ticket_id' => $ticket->id,
            'uploaded_by' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize()
        ]);

        return response()->json([
            'message' => 'Document ajouté avec succès',
            'data' => $document
        ], 201);
    }

    public function index(Ticket $ticket)
    {
        $documents = $ticket->documents()->latest()->get();

        return response()->json($documents);
    }

    public function download(TicketDocument $document)
    {
        return Storage::disk('public')->download($document->file_path);
    }
}

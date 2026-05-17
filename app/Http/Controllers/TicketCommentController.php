<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketCommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|max:2048'
        ]);

        $path = null;

        if ($request->hasFile('attachment')) {

            $path = $request
                ->file('attachment')
                ->store('comments', 'public');
        }

        $ticket->comments()->create([

            'user_id' => auth()->id(),

            'message' => $request->message,

            'attachment_path' => $path
        ]);

        return back()->with(
            'success',
            'Commentaire ajouté avec succès'
        );
    }
}
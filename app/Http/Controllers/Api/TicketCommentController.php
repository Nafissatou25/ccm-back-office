<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $path = $request->file('attachment')->store('comments', 'public');
    }

    $comment = $ticket->comments()->create([
        'user_id' => auth()->id(),
        'message' => $request->message,
        'attachment_path' => $path
    ]);

    return response()->json([
        'message' => 'Commentaire ajouté',
        'comment' => $comment->load('user')
    ]);
}

public function index(Ticket $ticket)
{
    return response()->json(
        $ticket->comments()->with('user')->latest()->get()
    );
} 
}

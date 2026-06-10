<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketActivity extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'type',
        'message',
        'attachment_path',
        'attachment2_path',   // ⬅️ Ajout
        'attachment3_path',   // ⬅️ Ajout (si vous en avez besoin)
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketView extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
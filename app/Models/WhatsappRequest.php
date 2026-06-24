<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappRequest extends Model
{
    protected $fillable = [
        'wa_phone',
        'client_name',
        'client_firstname',
        'contract_number',
        'contact_phone',
        'location_hint',
        'description',
        'conversation',
        'status',
        'ticket_id',
        'converted_by',
        'converted_at',
    ];

    protected $casts = [
        'conversation' => 'array',
        'converted_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function convertedBy()
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->client_name} {$this->client_firstname}");
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
    'unit_id',
    'type_id',
    'agency_id',
    'assigned_to',
    'description',
    'priority',
    'status',
    'contract_number',
    'attachment_path',
    'sla_id',
    'response_due_at',
    'resolution_due_at',
    'is_sla_paused',
    'sla_paused_at',
    'total_pause_duration',
];

protected $casts = [
    'response_due_at' => 'datetime',
    'resolution_due_at' => 'datetime',
    'sla_paused_at' => 'datetime',
];

    // relations
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

      public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
public function technicians()
{
    return $this->belongsToMany(
        User::class,
        'ticket_technicians'
    );
}

public function comments()
{
    return $this->hasMany(TicketComment::class);
}

public function documents()
{
    return $this->hasMany(TicketDocument::class);
}
public function closedBy()
{
    return $this->belongsTo(User::class, 'closed_by');
}

public function slaRule()
{
    return $this->belongsTo(SlaRule::class, 'sla_rule');
}

public function technician()
{
    return $this->belongsTo(User::class, 'taken_by');
}
public function assigner()
{
    return $this->belongsTo(User::class, 'assigned_by');
}

public function activities()
{
    return $this->hasMany(TicketActivity::class);
}

}
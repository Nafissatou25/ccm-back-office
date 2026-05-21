<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaRule extends Model
{
    protected $table = 'sla_rules';

    protected $fillable = [
        'unit_id',
        'priority',
        'response_time',
        'resolution_time',
        'is_active'
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
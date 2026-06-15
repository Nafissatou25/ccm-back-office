<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaRule extends Model
{
    protected $table = 'sla_rules';

    protected $fillable = [
        'unit_id',
        'type_id',   // nullable = règle par défaut unité
        'is_urgent',
        'tto',
        'ttr',
        'is_active',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
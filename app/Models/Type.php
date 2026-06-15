<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = [
        'unit_id',
        'name',
        'tto_normal',
        'ttr_normal',
        'tto_urgent',
        'ttr_urgent',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // SLA par défaut hérité de l'unité (fallback)
    public function getTto(bool $urgent): int
    {
        return $urgent ? $this->tto_urgent : $this->tto_normal;
    }

    public function getTtr(bool $urgent): int
    {
        return $urgent ? $this->ttr_urgent : $this->ttr_normal;
    }
}

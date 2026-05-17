<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sla extends Model
{
    use HasFactory;

    protected $fillable = [

        'priority',

        'response_time',

        'resolution_time',

        'is_active',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'contract_number',
        'name',
        'firstname',
        'phone',
        'whatsapp',
        'delivery_point'
    ];

    public function tickets()
{
    return $this->hasMany(Ticket::class);
}
}
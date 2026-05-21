<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function users()
{
    return $this->hasMany(User::class);
}

public function tickets()
{
    return $this->hasMany(Ticket::class);
}
}

<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'agency_id',
        'unit_id'
    ];

    protected $hidden = [
        'password',
    ];

    // 🔗 relations

    public function role()
{
    return $this->belongsTo(Role::class);
}
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function comments()
{
    return $this->hasMany(TicketComment::class);
}

public function roleSlug()
{
    return $this->role?->slug;
}
}
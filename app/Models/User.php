<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'agency_id',
        'unit_id',
        'company_id',
        'onesignal_player_id', 
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

public function company()
{
    return $this->belongsTo(Company::class);
}

public function roleSlug()
{
    return $this->role?->slug;
}

public function routeNotificationForOneSignal()
{
    // OneSignal utilise le player_id (ou external_id)
    return $this->onesignal_player_id;
}

}
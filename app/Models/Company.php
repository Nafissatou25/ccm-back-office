<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact',
        'phone',
        'email',
        'address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ─── Relations ──────────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function supervisors()
    {
        return $this->hasMany(User::class)->whereHas('role', function ($q) {
            $q->where('name', 'SUPERVISOR');
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeInactive($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('phone', 'LIKE', "%{$search}%")
            ->orWhere('contact', 'LIKE', "%{$search}%");
    }

    // ─── Méthodes ────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->deleted_at === null;
    }

    public function isInactive(): bool
    {
        return $this->deleted_at !== null;
    }

    public function getUsersCount(): int
    {
        return $this->users()->count();
    }

    public function getSupervisorsCount(): int
    {
        return $this->supervisors()->count();
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->name;
        if ($this->contact) {
            $name .= " ({$this->contact})";
        }
        return $name;
    }

    public function getPrimaryContactAttribute(): ?string
    {
        return $this->email ?? $this->phone ?? null;
    }
}
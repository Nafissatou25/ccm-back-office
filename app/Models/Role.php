<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'display_name', 'description'];

    // Accesseur pour obtenir le nom affiché
    public function getDisplayNameAttribute(): string
    {
        $mapping = [
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'customer_service' => 'Chargé d\'accueil',
            'supervisor' => 'Responsable d\'unité',
            'technician' => 'Technicien',
        ];

        return $mapping[strtolower($this->name)] ?? $this->name;
    }

    // Méthode statique pour une utilisation sans instance
    public static function getDisplayName($roleName): string
    {
        $mapping = [
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'customer_service' => 'Chargé d\'accueil',
            'supervisor' => 'Responsable d\'unité',
            'technician' => 'Technicien',
        ];

        return $mapping[strtolower($roleName)] ?? $roleName;
    }
 
}


// Role::insert([
//     ['name' => 'ADMIN'],
//     ['name' => 'MANAGER'],
//     ['name' => 'SUPERVISOR'],
//     ['name' => 'AGENT'],
//     ['name' => 'CUSTOMER_SERVICE'],
// ]);

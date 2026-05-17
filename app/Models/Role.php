<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
 
}


// Role::insert([
//     ['name' => 'ADMIN'],
//     ['name' => 'MANAGER'],
//     ['name' => 'SUPERVISOR'],
//     ['name' => 'AGENT'],
//     ['name' => 'CUSTOMER_SERVICE'],
// ]);

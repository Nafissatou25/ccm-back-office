<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vérifier que la colonne n'existe pas avant de l'ajouter
        if (!Schema::hasColumn('users', 'matricule')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('matricule')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        // On peut laisser la suppression telle quelle, mais il est préférable de vérifier aussi
        if (Schema::hasColumn('users', 'matricule')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('matricule');
            });
        }
    }
};
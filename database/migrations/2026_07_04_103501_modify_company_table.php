<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vérifier si la table existe
        if (Schema::hasTable('companies')) {
            // Ajouter les colonnes manquantes
            Schema::table('companies', function (Blueprint $table) {
                if (!Schema::hasColumn('companies', 'contact')) {
                    $table->string('contact')->nullable();
                }
                if (!Schema::hasColumn('companies', 'address')) {
                    $table->text('address')->nullable();
                }
                if (!Schema::hasColumn('companies', 'deleted_at')) {
                    $table->softDeletes();
                }
                // Ajouter d'autres colonnes si nécessaires
            });
        } else {
            // Créer la table (au cas où)
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Si vous voulez rollback, supprimez les colonnes ajoutées
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['contact', 'address']);
            $table->dropSoftDeletes();
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // Supprimer la contrainte unique si elle existe encore
    try {
        Schema::table('sla_rules', function (Blueprint $table) {
            $table->dropUnique('sla_rules_unit_id_priority_unique');
        });
    } catch (\Exception $e) {
        // Contrainte déjà supprimée, on continue
    }

    // Supprimer les anciennes colonnes si elles existent encore
    Schema::table('sla_rules', function (Blueprint $table) {
        $columns = \Schema::getColumnListing('sla_rules');
        
        if (in_array('priority', $columns)) {
            $table->dropColumn('priority');
        }
        if (in_array('response_time', $columns)) {
            $table->dropColumn('response_time');
        }
        if (in_array('resolution_time', $columns)) {
            $table->dropColumn('resolution_time');
        }
    });

    // Ajouter les nouvelles colonnes si elles n'existent pas encore
    Schema::table('sla_rules', function (Blueprint $table) {
        $columns = \Schema::getColumnListing('sla_rules');

        if (!in_array('type_id', $columns)) {
            $table->foreignId('type_id')->nullable()->after('unit_id')->constrained()->nullOnDelete();
        }
        if (!in_array('is_urgent', $columns)) {
            $table->boolean('is_urgent')->default(false)->after('type_id');
        }
        if (!in_array('tto', $columns)) {
            $table->unsignedInteger('tto')->default(24)->after('is_urgent');
        }
        if (!in_array('ttr', $columns)) {
            $table->unsignedInteger('ttr')->default(72)->after('tto');
        }
    });

    // Ajouter la contrainte unique si elle n'existe pas
    try {
        Schema::table('sla_rules', function (Blueprint $table) {
            $table->unique(['unit_id', 'type_id', 'is_urgent']);
        });
    } catch (\Exception $e) {
        // Contrainte déjà présente
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

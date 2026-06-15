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
    Schema::table('types', function (Blueprint $table) {
        $table->unsignedInteger('tto_normal')->default(24)->comment('Temps de prise en charge normal (heures)');
        $table->unsignedInteger('ttr_normal')->default(72)->comment('Temps de résolution normal (heures)');
        $table->unsignedInteger('tto_urgent')->default(4)->comment('Temps de prise en charge urgent (heures)');
        $table->unsignedInteger('ttr_urgent')->default(24)->comment('Temps de résolution urgent (heures)');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

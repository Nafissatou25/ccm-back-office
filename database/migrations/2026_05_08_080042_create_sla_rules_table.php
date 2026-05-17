<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sla_rules', function (Blueprint $table) {
    $table->id();

    $table->foreignId('unit_id')
        ->constrained()
        ->onDelete('cascade');

    $table->enum('priority', [
        'low',
        'medium',
        'high',
        'critical'
    ]);

    // Temps en minutes
    $table->integer('response_time');

    $table->integer('resolution_time');

    $table->boolean('is_active')
        ->default(true);

    $table->timestamps();

    $table->unique(['unit_id', 'priority']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_rules');
    }
};

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
        Schema::table('tickets', function (Blueprint $table) {

    $table->foreignId('sla_id')
        ->nullable()
        ->constrained();

    $table->timestamp('response_due_at')
        ->nullable();

    $table->timestamp('resolution_due_at')
        ->nullable();

    $table->boolean('is_sla_paused')
        ->default(false);

    $table->timestamp('sla_paused_at')
        ->nullable();

    $table->integer('total_pause_duration')
        ->default(0);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
        });
    }
};

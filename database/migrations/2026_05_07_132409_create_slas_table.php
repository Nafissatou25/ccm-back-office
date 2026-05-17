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
       Schema::create('slas', function (Blueprint $table) {

    $table->id();

    $table->string('name');

    $table->enum('priority', [
        'low',
        'medium',
        'high',
        'critical'
    ]);

    // minutes
    $table->integer('response_time');

    // minutes
    $table->integer('resolution_time');

    $table->boolean('is_active')
        ->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slas');
    }
};

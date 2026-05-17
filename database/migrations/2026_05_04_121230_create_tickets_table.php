<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

    $table->foreignId('unit_id')->constrained();
    $table->foreignId('type_id')->constrained();
    $table->foreignId('agency_id')->constrained();

    $table->foreignId('assigned_to')->constrained('users');

    $table->text('description');
    $table->string('status')->default('OPEN');

    $table->string('contract_number')->nullable();
    $table->string('attachment_path');

    $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
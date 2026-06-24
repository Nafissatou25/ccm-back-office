<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_requests', function (Blueprint $table) {
            $table->id();
            $table->string('wa_phone');           // numéro WhatsApp expéditeur
            $table->string('client_name')->nullable();
            $table->string('client_firstname')->nullable();
            $table->string('contract_number')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('location_hint')->nullable();
            $table->text('description')->nullable();
            $table->json('conversation')->nullable();
            $table->enum('status', [
                'IN_PROGRESS',  // conversation en cours
                'COMPLETED',    // infos collectées
                'CONVERTED',    // ticket créé
                'CANCELLED',
            ])->default('IN_PROGRESS');
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_requests');
    }
};
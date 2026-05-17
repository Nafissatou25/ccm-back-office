<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type'); 
            // comment, system, assignment, transfer, resolution, document

            $table->text('message')->nullable();

            $table->string('attachment_path')->nullable();

            $table->json('meta')->nullable(); 
            // infos supplémentaires (ex: old_status, new_status)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
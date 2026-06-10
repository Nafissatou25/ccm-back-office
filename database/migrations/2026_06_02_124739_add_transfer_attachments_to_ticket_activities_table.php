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
    Schema::table('ticket_activities', function (Blueprint $table) {
        $table->string('attachment2_path')->nullable()->after('attachment_path');
        $table->string('attachment3_path')->nullable()->after('attachment2_path');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_activities', function (Blueprint $table) {
            //
        });
    }
};

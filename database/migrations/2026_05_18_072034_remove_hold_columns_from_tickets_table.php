<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {

            $table->dropColumn([
                'hold_started_at',
                'total_hold_time'
            ]);

        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {

            $table->timestamp('hold_started_at')->nullable();

            $table->integer('total_hold_time')
                  ->default(0);

        });
    }
};
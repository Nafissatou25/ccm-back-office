<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_make_contract_number_nullable_in_clients_table.php
public function up()
{
    Schema::table('clients', function (Blueprint $table) {
        $table->string('contract_number')->nullable()->change();
    });
}

public function down()
{
    Schema::table('clients', function (Blueprint $table) {
        $table->string('contract_number')->nullable(false)->change();
    });
}
};

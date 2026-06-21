<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ticket_documents', function (Blueprint $table) {
            $table->text('description')->nullable()->after('file_name');
        });
    }

    public function down()
    {
        Schema::table('ticket_documents', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};

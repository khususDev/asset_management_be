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
        Schema::table('sys_document_numbering', function (Blueprint $table) {

            $table->string('last_reset_period', 20)
                ->nullable()
                ->after('current_sequence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_document_numbering', function (Blueprint $table) {

            $table->dropColumn('last_reset_period');
        });
    }
};

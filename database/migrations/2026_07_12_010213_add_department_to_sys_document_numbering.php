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
        Schema::table('sys_document_numbering', function (Blueprint $table) {
            $table->string('department')->nullable(); // Set nullable jika ingin ada format global
        });

        // Hapus unique index lama jika ada, lalu buat baru
        // Kita harus memastikan (module, department) unik bersamaan
        Schema::table('sys_document_numbering', function (Blueprint $table) {
            $table->unique(['module', 'department']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_document_numbering', function (Blueprint $table) {
            //
        });
    }
};

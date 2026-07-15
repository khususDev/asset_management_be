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
        Schema::create('sys_document_numbering', function (Blueprint $table) {
            $table->id();
            $table->string('module', 100)->unique(); // Kode Modul (Misal: PR, PO, ASSET)
            $table->string('name', 255);             // Nama Dokumen (Misal: Purchase Request)
            $table->string('format', 255);           // Format (Misal: {PREFIX}-{YYYY}{MM}-{SEQ})
            $table->string('prefix', 50)->nullable(); // Awalan (Misal: PR)
            $table->integer('digit_length')->default(4); // Panjang nomor urut (Misal: 4 -> 0001)
            $table->bigInteger('current_sequence')->default(0); // Nomor urut terakhir yang terpakai
            $table->enum('reset_type', ['NEVER', 'YEARLY', 'MONTHLY', 'DAILY'])->default('YEARLY');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_document_numbering');
    }
};

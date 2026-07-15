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
        Schema::create('opt_asset_request', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Siapa yang minta
            $table->string('asset_name'); // Teks biasa dulu karena tabel aset belum ada
            $table->integer('quantity')->default(1);
            $table->text('reason');
            $table->date('needed_date');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'DEPLOYED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opt_asset_request');
    }
};

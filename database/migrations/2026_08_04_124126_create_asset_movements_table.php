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
        Schema::create('opt_asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('opt_assets')->cascadeOnDelete();

            // Jenis Aksi: ASSIGNMENT, RETURN, MAINTENANCE, DISPOSAL
            $table->string('action')->default('ASSIGNMENT');

            // Penerima (Polymorphic: user, department, location)
            $table->enum('assigned_type', ['user', 'department', 'location'])->nullable();
            $table->unsignedBigInteger('assigned_to_id')->nullable();

            // Detail Transaksi
            $table->date('action_date');
            $table->string('reference_number')->nullable(); // No BAST / Referensi
            $table->enum('condition', ['GOOD', 'FAIR', 'NEEDS_REPAIR', 'BROKEN'])->default('GOOD');
            $table->text('notes')->nullable();

            // Admin / Actor yang melakukan transaksi
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};

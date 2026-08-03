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
        Schema::create('opt_assignments', function (Blueprint $table) {
            $table->id();

            // Polymorphic: Menentukan tipe (Asset/License/Consumable) dan ID-nya
            // Menghasilkan kolom assignable_type (string) dan assignable_id (bigInteger)
            $table->morphs('assignable');

            // User yang menerima
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();

            // Detail Penyerahan
            $table->date('assigned_date');
            $table->date('returned_date')->nullable(); // Null berarti masih dipakai
            $table->string('status')->default('ACTIVE'); // ACTIVE, RETURNED, DISPOSED
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opt_assignments');
    }
};

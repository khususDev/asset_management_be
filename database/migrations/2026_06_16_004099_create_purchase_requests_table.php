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
        Schema::create('opt_purchase_request', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Prepared By
            $table->string('department');
            $table->text('purpose'); // Header Purpose
            $table->decimal('total_estimated_amount', 15, 2)->default(0);
            $table->enum('status', ['PENDING', 'PARTIAL_APPROVED', 'APPROVED', 'REJECTED', 'PO_CREATED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opt_purchase_request');
    }
};

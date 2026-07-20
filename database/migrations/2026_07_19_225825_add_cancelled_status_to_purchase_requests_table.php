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
        // 1. Hapus constraint lama di PostgreSQL
        DB::statement('ALTER TABLE opt_purchase_request DROP CONSTRAINT IF EXISTS opt_purchase_request_status_check');

        // 2. Buat constraint baru yang sudah mengizinkan status CANCELLED
        DB::statement("ALTER TABLE opt_purchase_request ADD CONSTRAINT opt_purchase_request_status_check CHECK (status IN ('PENDING', 'APPROVED', 'REJECTED', 'PARTIAL_APPROVED', 'CANCELLED'))");
    }

    public function down(): void
    {
        // Kembalikan seperti semula jika di-rollback
        DB::statement('ALTER TABLE opt_purchase_request DROP CONSTRAINT IF EXISTS opt_purchase_request_status_check');
        DB::statement("ALTER TABLE opt_purchase_request ADD CONSTRAINT opt_purchase_request_status_check CHECK (status IN ('PENDING', 'APPROVED', 'REJECTED', 'PARTIAL_APPROVED'))");
    }
};
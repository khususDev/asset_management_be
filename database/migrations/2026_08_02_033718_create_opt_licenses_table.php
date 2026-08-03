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
        Schema::create('opt_licenses', function (Blueprint $table) {
            $table->id();

            // Relasi Logistik & Pengadaan (Opsional / Nullable jika manual entry)
            $table->foreignId('goods_receipt_item_id')->nullable()->index();
            $table->foreignId('purchase_order_item_id')->nullable()->index();

            // Informasi Lisensi
            $table->string('license_code')->unique(); // ID unik lisensi
            $table->string('software_name');
            $table->foreignId('category_id')->nullable()->constrained('mst_asset_category')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('mst_procurement_vendor')->nullOnDelete();

            $table->text('license_key')->nullable();
            $table->string('license_type')->default('SUBSCRIPTION'); // SUBSCRIPTION, PERPETUAL, OEM, CONCURRENT
            $table->integer('total_seats')->default(1);

            // Data Finansial & Masa Berlaku
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->date('expiration_date')->nullable();

            // Status Sistem & Registrasi
            $table->string('registration_status')->default('REGISTERED'); // REGISTERED, DRAFT
            $table->string('status')->default('ACTIVE'); // ACTIVE, NEAR_EXPIRATION, EXPIRED

            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opt_licenses');
    }
};

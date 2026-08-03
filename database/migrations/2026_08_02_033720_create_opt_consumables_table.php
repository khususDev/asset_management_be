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
        Schema::create('opt_consumables', function (Blueprint $table) {
            $table->id();

            // Relasi Logistik & Pengadaan
            $table->foreignId('goods_receipt_item_id')->nullable()->index();
            $table->foreignId('purchase_order_item_id')->nullable()->index();

            // Informasi Barang Habis Pakai
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->foreignId('category_id')->nullable()->constrained('mst_asset_category')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('mst_procurement_vendor')->nullOnDelete();

            // Stok & Satuan
            $table->string('unit_of_measure'); // Pcs, Box, Roll, Pack, Cartridge, dsb.
            $table->integer('total_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->integer('min_stock_alert')->default(5); // Ambang batas peringatan stok tipis
            $table->decimal('unit_price', 15, 2)->default(0);

            $table->foreignId('location_id')->nullable()->constrained('mst_org_location')->nullOnDelete();

            // Status Sistem
            $table->string('registration_status')->default('REGISTERED');
            $table->string('status')->default('IN_STOCK'); // IN_STOCK, LOW_STOCK, OUT_OF_STOCK

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
        Schema::dropIfExists('opt_consumables');
    }
};

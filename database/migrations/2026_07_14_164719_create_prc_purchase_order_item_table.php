<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('opt_purchase_order_item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('opt_purchase_order')
                ->cascadeOnDelete();

            $table->foreignId('purchase_request_item_id')
                ->nullable()
                ->constrained('opt_purchase_request_item');

            $table->text('item_description');

            $table->decimal('quantity', 18, 2)->default(0);

            $table->foreignId('uom_id')
                ->nullable()
                ->constrained('mst_procurement_uom');

            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);

            $table->boolean('is_pkp')
                ->default(false);

            $table->boolean('price_include_ppn')
                ->default(false);

            $table->text('item_purpose')
                ->nullable();

            $table->date('expected_arrival_date')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opt_purchase_order_item');
    }
};

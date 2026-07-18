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
        Schema::create('opt_goods_receipt_item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('goods_receipt_id')
                ->constrained('opt_goods_receipt')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_item_id')
                ->constrained('opt_purchase_order_item');

            $table->text('item_description');

            $table->decimal('ordered_qty', 18, 2)
                ->default(0);

            $table->decimal('received_before_qty', 18, 2)
                ->default(0);

            $table->decimal('receive_qty', 18, 2)
                ->default(0);

            $table->decimal('accepted_qty', 18, 2)
                ->default(0);

            $table->decimal('rejected_qty', 18, 2)
                ->default(0);

            $table->decimal('return_qty', 18, 2)
                ->default(0);

            $table->decimal('replacement_qty', 18, 2)
                ->default(0);

            $table->text('remarks')
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
        Schema::dropIfExists('opt_goods_receipt_item');
    }
};

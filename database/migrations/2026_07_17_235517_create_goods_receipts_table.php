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
        Schema::create('opt_goods_receipt', function (Blueprint $table) {
            $table->id();

            $table->string('gr_number')->unique();

            $table->foreignId('purchase_order_id')
                ->constrained('opt_purchase_order');

            $table->date('received_date');

            $table->text('remarks')
                ->nullable();

            /*
                DRAFT
                POSTED
                CANCELLED
            */
            $table->string('status')
                ->default('DRAFT');

            $table->foreignId('created_by')
                ->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opt_goods_receipt');
    }
};

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
        Schema::create('opt_purchase_order', function (Blueprint $table) {
            $table->id();

            $table->string('po_number')->unique();

            // relasi ke PR
            $table->foreignId('purchase_request_id')
                ->constrained('opt_purchase_request')
                ->cascadeOnDelete();

            // vendor tujuan
            $table->foreignId('vendor_id')
                ->constrained('mst_procurement_vendor');

            // department pembuat PR
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('mst_org_department');

            // lokasi pengiriman
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('mst_org_branch');

            // payment term
            $table->foreignId('payment_term_id')
                ->nullable()
                ->constrained('mst_procurement_payment');

            $table->date('order_date');

            $table->date('expected_delivery_date')
                ->nullable();

            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('ppn_amount', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);

            $table->text('remarks')->nullable();

            /*
                DRAFT
                SENT
                PARTIAL_RECEIVED
                COMPLETED
                CANCELLED
            */
            $table->string('status')->default('DRAFT');

            $table->foreignId('created_by')
                ->constrained('users');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users');

            $table->timestamp('approved_at')
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
        Schema::dropIfExists('opt_purchase_order');
    }
};

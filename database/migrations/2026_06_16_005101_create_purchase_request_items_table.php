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
        Schema::create('opt_purchase_request_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('opt_purchase_request')->onDelete('cascade');

            // Basic Info
            $table->string('item_description');
            $table->integer('quantity');
            $table->foreignId('uom_id')->nullable(); // Master UoM (pcs, unit, dll)
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_amount', 15, 2);

            // Form Excel Details
            $table->boolean('need_to_issue_po')->default(true);
            $table->foreignId('vendor_id')->nullable(); // Master Vendor
            $table->boolean('is_pkp')->default(false); // Vendor PKP status
            $table->string('pic_contact')->nullable();
            $table->text('url')->nullable();
            $table->boolean('price_include_ppn')->default(false);
            $table->foreignId('payment_term_id')->nullable(); // Master Payment (TOP)
            $table->date('expected_arrival_date')->nullable();
            $table->foreignId('delivery_id')->nullable(); // Master Location
            $table->text('item_purpose')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opt_purchase_request_item');
    }
};

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
        Schema::table('opt_purchase_request_item', function (Blueprint $table) {
            $table->integer('received_qty')->default(0);

            $table->enum('receiving_status', [
                'NOT_RECEIVED',
                'PARTIAL',
                'COMPLETED'
            ])->default('NOT_RECEIVED');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opt_purchase_request_item', function (Blueprint $table) {
            //
        });
    }
};

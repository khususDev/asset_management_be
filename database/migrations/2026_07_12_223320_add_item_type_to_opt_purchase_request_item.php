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
            $table->enum('item_type', [
                'FIXED_ASSET',
                'CONSUMABLE',
                'SPAREPART',
                'SERVICE'
            ])->default('FIXED_ASSET')
                ->after('item_description');
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

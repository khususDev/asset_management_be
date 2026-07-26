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
        Schema::table('opt_goods_receipt', function (Blueprint $table) {

            $table
                ->enum('receipt_status', [
                    'PARTIAL',
                    'COMPLETE'
                ])
                ->default('PARTIAL')
                ->after('received_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opt_goods_receipt', function (Blueprint $table) {

            $table->dropColumn('receipt_status');
        });
    }
};

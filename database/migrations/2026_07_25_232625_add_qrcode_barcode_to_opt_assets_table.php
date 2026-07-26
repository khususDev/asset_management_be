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
        Schema::table('opt_assets', function (Blueprint $table) {

            $table->text('qr_code')->nullable();

            $table->text('barcode')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('opt_assets', function (Blueprint $table) {

            $table->dropColumn([
                'qr_code',
                'barcode'
            ]);

        });
    }
};
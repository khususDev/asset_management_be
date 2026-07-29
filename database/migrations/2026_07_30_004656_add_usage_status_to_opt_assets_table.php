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
            $table->enum('usage_status', [
                'AVAILABLE',
                'ASSIGNED',
                'RESERVED',
                'MAINTENANCE',
                'DISPOSED'
            ])->default('AVAILABLE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opt_assets', function (Blueprint $table) {
            $table->dropColumn('usage_status');
        });
    }
};

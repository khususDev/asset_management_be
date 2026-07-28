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
            $table->enum('asset_class', ['FIXED_ASSET', 'CONSUMABLE', 'LICENSE'])
                ->nullable()
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opt_assets', function (Blueprint $table) {
            $table->dropColumn('asset_class');
        });
    }
};

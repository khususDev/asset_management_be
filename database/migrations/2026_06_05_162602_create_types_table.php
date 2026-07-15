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
        Schema::create('mst_asset_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_category_id')->constrained('mst_asset_category')->onDelete('cascade');
            $table->string('code')->unique()->comment('Misal: LPT (Laptop), SRV (Server)');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_asset_type');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opt_assets', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Source Procurement
            |--------------------------------------------------------------------------
            */

            $table->foreignId('goods_receipt_item_id')
                ->nullable()
                ->constrained('opt_goods_receipt_item')
                ->nullOnDelete();

            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->constrained('opt_purchase_order_item')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Asset Identity
            |--------------------------------------------------------------------------
            */

            $table->string('asset_code')->nullable()->unique();

            $table->string('asset_name');

            $table->string('serial_number')->nullable();

            $table->string('qr_code')->nullable();

            $table->string('barcode')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Master Asset
            |--------------------------------------------------------------------------
            */

            $table->foreignId('asset_category_id')
                ->nullable()
                ->constrained('mst_asset_category')
                ->nullOnDelete();

            $table->foreignId('asset_type_id')
                ->nullable()
                ->constrained('mst_asset_type')
                ->nullOnDelete();

            $table->foreignId('brand_id')
                ->nullable()
                ->constrained('mst_asset_brand')
                ->nullOnDelete();

            $table->foreignId('model_id')
                ->nullable()
                ->constrained('mst_asset_model')
                ->nullOnDelete();

            $table->foreignId('status_id')
                ->nullable()
                ->constrained('mst_asset_status')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Organization
            |--------------------------------------------------------------------------
            */

            $table->foreignId('vendor_id')
                ->nullable()
                ->constrained('mst_procurement_vendor')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('mst_org_department')
                ->nullOnDelete();

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('mst_org_location')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Finance
            |--------------------------------------------------------------------------
            */

            $table->date('purchase_date')->nullable();

            $table->decimal('purchase_cost',18,2)->default(0);

            $table->date('warranty_start')->nullable();

            $table->date('warranty_end')->nullable();

            $table->integer('useful_life')->nullable();

            $table->decimal('salvage_value',18,2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Registration
            |--------------------------------------------------------------------------
            */

            $table->enum('registration_status',[
                'WAITING_REGISTRATION',
                'REGISTERED'
            ])->default('WAITING_REGISTRATION');

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opt_assets');
    }
};
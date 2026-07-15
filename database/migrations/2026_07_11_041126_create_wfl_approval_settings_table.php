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
        // Tabel HEADER
        Schema::create('wfl_approval_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module', 100);
            $table->integer('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Tabel DETAIL (Levels)
        Schema::create('wfl_approval_setting_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wfl_approval_setting_id')
                ->constrained('wfl_approval_settings')
                ->onDelete('cascade');
            $table->integer('level');

            // Pastikan nama tabel user Anda di database benar-benar 'users'
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->decimal('min_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wfl_approval_setting_levels');
        Schema::dropIfExists('wfl_approval_settings');
    }
};

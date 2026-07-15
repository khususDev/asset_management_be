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
        Schema::create('sys_notification_setting', function (Blueprint $table) {
            $table->id();
            $table->string('module', 100); // Misal: Purchase Request
            $table->string('event', 100);  // Misal: ON_CREATE, ON_APPROVE, ON_REJECT
            $table->string('recipient_role', 100); // Misal: CREATOR, NEXT_APPROVER, MANAGER
            $table->enum('type', ['EMAIL', 'SYSTEM', 'BOTH'])->default('SYSTEM');
            $table->boolean('is_active')->default(true);
            $table->string('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_notification_setting');
    }
};

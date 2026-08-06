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
            // Jika kolom-kolom ini belum ada di tabel assets kamu:
            if (!Schema::hasColumn('opt_assets', 'status')) {
                $table->enum('status', ['AVAILABLE', 'ASSIGNED', 'MAINTENANCE', 'DISPOSED'])->default('AVAILABLE')->after('name');
            }
            if (!Schema::hasColumn('opt_assets', 'assigned_type')) {
                $table->enum('assigned_type', ['user', 'department', 'location'])->nullable()->after('status');
                $table->unsignedBigInteger('assigned_to_id')->nullable()->after('assigned_type');
                $table->date('assigned_date')->nullable()->after('assigned_to_id');
            }
            if (!Schema::hasColumn('opt_assets', 'condition')) {
                $table->enum('condition', ['GOOD', 'FAIR', 'NEEDS_REPAIR', 'BROKEN'])->default('GOOD')->after('assigned_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['assigned_type', 'assigned_to_id', 'assigned_date']);
        });
    }
};

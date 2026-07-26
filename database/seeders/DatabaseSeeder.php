<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call([
            // 1. Tabel Mandiri
            AssetCategorySeeder::class,
            AssetBrandSeeder::class,
            AssetStatusSeeder::class,
            LicenseTypeSeeder::class,
            LicenseMetricSeeder::class,

            // 2. Tabel Berelasi (Dipanggil SETELAH tabel utamanya di-seed)
            AssetTypeSeeder::class,   // Butuh AssetCategory
            AssetModelSeeder::class,  // Butuh AssetBrand
        ]);
    }
}

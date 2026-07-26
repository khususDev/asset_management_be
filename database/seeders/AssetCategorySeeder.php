<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'code' => 'IT',  'name' => 'IT & Electronic Assets', 'description' => 'Perangkat keras komputer, jaringan, dan elektronik TI.'],
            ['id' => 2, 'code' => 'VEH', 'name' => 'Vehicles',               'description' => 'Kendaraan operasional perusahaan (roda 2, 4, atau lebih).'],
            ['id' => 3, 'code' => 'FUR', 'name' => 'Furniture & Fixture',    'description' => 'Mebel, perabot kantor, dan instalasi fisik.'],
            ['id' => 4, 'code' => 'MACH', 'name' => 'Machinery & Equipment',  'description' => 'Mesin berat, genset, dan peralatan operasional lapangan.'],
        ];

        foreach ($categories as $cat) {
            DB::table('mst_asset_category')->updateOrCreate(['code' => $cat['code']], $cat);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['id' => 1, 'code' => 'DELL', 'name' => 'Dell',           'description' => 'Dell Technologies'],
            ['id' => 2, 'code' => 'LNV',  'name' => 'Lenovo',         'description' => 'Lenovo Group Ltd.'],
            ['id' => 3, 'code' => 'AAPL', 'name' => 'Apple',          'description' => 'Apple Inc.'],
            ['id' => 4, 'code' => 'CSCO', 'name' => 'Cisco',          'description' => 'Cisco Systems'],
            ['id' => 5, 'code' => 'TOY',  'name' => 'Toyota',         'description' => 'Toyota Motor Corporation'],
            ['id' => 6, 'code' => 'HND',  'name' => 'Honda',          'description' => 'Honda Motor Co., Ltd.'],
            ['id' => 7, 'code' => 'DAIK', 'name' => 'Daikin',         'description' => 'Daikin Industries'],
            ['id' => 8, 'code' => 'HM',   'name' => 'Herman Miller',  'description' => 'Herman Miller Furniture'],
        ];

        foreach ($brands as $b) {
            DB::table('mst_asset_brand')->updateOrCreate(['code' => $b['code']], $b);
        }
    }
}

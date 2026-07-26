<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            // Dell (Brand ID: 1)
            ['brand_id' => 1, 'code' => 'DELL-LAT-5420', 'name' => 'Latitude 5420',   'description' => 'Core i5/i7 11th Gen, 14 inch'],
            ['brand_id' => 1, 'code' => 'DELL-PE-R750',  'name' => 'PowerEdge R750',  'description' => '2U Rack Server Dual Intel Xeon'],

            // Lenovo (Brand ID: 2)
            ['brand_id' => 2, 'code' => 'LNV-X1-CARB',   'name' => 'ThinkPad X1 Carbon Gen 10', 'description' => 'Ultrabook Core i7, 14 inch'],
            ['brand_id' => 2, 'code' => 'LNV-TC-M70Q',   'name' => 'ThinkCentre M70q Tiny',      'description' => 'Mini PC Desktop'],

            // Apple (Brand ID: 3)
            ['brand_id' => 3, 'code' => 'AAPL-MBP14-M3', 'name' => 'MacBook Pro 14" (M3 Pro)',  'description' => 'Apple Silicon M3 Pro 18GB/512GB'],

            // Cisco (Brand ID: 4)
            ['brand_id' => 4, 'code' => 'CSCO-C9200L',   'name' => 'Catalyst 9200L 24-Port',   'description' => 'Switch Managed Gigabit'],

            // Toyota (Brand ID: 5)
            ['brand_id' => 5, 'code' => 'TOY-AVZ-13G',   'name' => 'Avanza 1.3 G CVT',          'description' => 'MPV Operasional'],
            ['brand_id' => 5, 'code' => 'TOY-INNOVA-Z',  'name' => 'Innova Zenix 2.0 V',        'description' => 'MPV Eksekutif / Direksi'],

            // Honda (Brand ID: 6)
            ['brand_id' => 6, 'code' => 'HND-VR160',     'name' => 'Vario 160 CBS',             'description' => 'Motor matic operasional'],

            // Herman Miller (Brand ID: 8)
            ['brand_id' => 8, 'code' => 'HM-AERON-B',    'name' => 'Aeron Chair Size B',        'description' => 'Kursi Ergonomis Mesh Full Option'],
        ];

        foreach ($models as $m) {
            DB::table('mst_asset_model')->updateOrCreate(['code' => $m['code']], $m);
        }
    }
}

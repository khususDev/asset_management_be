<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            // IT Assets (Category ID: 1)
            ['asset_category_id' => 1, 'code' => 'LPT', 'name' => 'Laptop',             'description' => 'Laptop / Notebook karyawan'],
            ['asset_category_id' => 1, 'code' => 'DSK', 'name' => 'Desktop PC',         'description' => 'Komputer meja / All-in-One'],
            ['asset_category_id' => 1, 'code' => 'MON', 'name' => 'Monitor',            'description' => 'Layar monitor tambahan'],
            ['asset_category_id' => 1, 'code' => 'SRV', 'name' => 'Server',             'description' => 'Physical Server Rack/Tower'],
            ['asset_category_id' => 1, 'code' => 'NET', 'name' => 'Network Device',     'description' => 'Router, Switch, Access Point, Firewall'],
            ['asset_category_id' => 1, 'code' => 'PRN', 'name' => 'Printer & Scanner',  'description' => 'Printer multifungsi & scanner'],

            // Vehicles (Category ID: 2)
            ['asset_category_id' => 2, 'code' => 'CAR', 'name' => 'Passenger Car',      'description' => 'Mobil dinas / operasional ringan'],
            ['asset_category_id' => 2, 'code' => 'MTR', 'name' => 'Motorcycle',         'description' => 'Sepeda motor operasional kurir/lapangan'],
            ['asset_category_id' => 2, 'code' => 'TRK', 'name' => 'Commercial Truck',   'description' => 'Truk angkut / box logistik'],

            // Furniture (Category ID: 3)
            ['asset_category_id' => 3, 'code' => 'DSK_OFF', 'name' => 'Office Desk',    'description' => 'Meja kerja karyawan & eksekutif'],
            ['asset_category_id' => 3, 'code' => 'CHR_ERG', 'name' => 'Ergonomic Chair', 'description' => 'Kursi kerja ergonomis'],
            ['asset_category_id' => 3, 'code' => 'CBM',     'name' => 'Filing Cabinet', 'description' => 'Lemari arsip dokumen'],

            // Machinery (Category ID: 4)
            ['asset_category_id' => 4, 'code' => 'GNS', 'name' => 'Genset',             'description' => 'Generator set daya cadangan'],
            ['asset_category_id' => 4, 'code' => 'AC',  'name' => 'Air Conditioner',    'description' => 'AC Split / Cassette kantor'],
        ];

        foreach ($types as $type) {
            DB::table('mst_asset_type')->updateOrCreate(['code' => $type['code']], $type);
        }
    }
}

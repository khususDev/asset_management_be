<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LicenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licenseTypes = [
            [
                'code'        => 'SUB',
                'name'        => 'Subscription',
                'description' => 'Berlangganan berkala (bulanan/tahunan), contoh: Microsoft 365, Adobe CC.'
            ],
            [
                'code'        => 'PER',
                'name'        => 'Perpetual',
                'description' => 'Lisensi seumur hidup sekali beli, contoh: Windows OEM, Visio LTSC.'
            ],
            [
                'code'        => 'OEM',
                'name'        => 'OEM (Original Equipment Manufacturer)',
                'description' => 'Lisensi terikat langsung pada satu hardware fisik spesifik.'
            ],
            [
                'code'        => 'FOSS',
                'name'        => 'Free & Open Source',
                'description' => 'Perangkat lunak gratis / open source dengan lisensi MIT/GPL.'
            ],
        ];

        foreach ($licenseTypes as $lt) {
            DB::table('mst_license_type')->updateOrCreate(['code' => $lt['code']], $lt);
        }
    }
}

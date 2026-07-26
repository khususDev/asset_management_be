<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LicenseMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licenseMetrics = [
            [
                'code'        => 'USR',
                'name'        => 'Per User',
                'description' => 'Dihitung berdasarkan jumlah pengguna yang terdaftar/aktif.'
            ],
            [
                'code'        => 'DEV',
                'name'        => 'Per Device',
                'description' => 'Dihitung berdasarkan jumlah komputer/perangkat yang menginstall software.'
            ],
            [
                'code'        => 'COR',
                'name'        => 'Per CPU Core',
                'description' => 'Dihitung berdasarkan jumlah core processor server (contoh: Windows Server, Oracle DB).'
            ],
            [
                'code'        => 'STE',
                'name'        => 'Site License',
                'description' => 'Akses tak terbatas untuk seluruh karyawan di lokasi/perusahaan tertentu.'
            ],
            [
                'code'        => 'CON',
                'name'        => 'Concurrent User',
                'description' => 'Dihitung berdasarkan batas maksimum pengguna yang login secara bersamaan.'
            ],
        ];

        foreach ($licenseMetrics as $lm) {
            DB::table('mst_license_metric')->updateOrCreate(['code' => $lm['code']], $lm);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'code'        => 'READY',
                'name'        => 'Ready to Deploy',
                'color'       => '#10B981', // Emerald Green
                'description' => 'Aset tersedia di gudang/inventory dan siap diberikan ke pengguna.'
            ],
            [
                'code'        => 'ASSIGNED',
                'name'        => 'In Use / Assigned',
                'color'       => '#3B82F6', // Blue
                'description' => 'Aset sedang aktif digunakan oleh karyawan atau unit kerja.'
            ],
            [
                'code'        => 'IN_REPAIR',
                'name'        => 'In Repair / Maintenance',
                'color'       => '#F59E0B', // Amber/Yellow
                'description' => 'Aset sedang dalam perbaikan internal atau serviks vendor.'
            ],
            [
                'code'        => 'RESERVED',
                'name'        => 'Reserved',
                'color'       => '#8B5CF6', // Purple
                'description' => 'Aset disiapkan untuk kandidat baru/proyek tertentu.'
            ],
            [
                'code'        => 'DISPOSED',
                'name'        => 'Disposed / Written Off',
                'color'       => '#EF4444', // Red
                'description' => 'Aset telah dijual, dihibahkan, atau dimusnahkan.'
            ],
            [
                'code'        => 'LOST',
                'name'        => 'Lost / Stolen',
                'color'       => '#6B7280', // Gray
                'description' => 'Aset hilang atau dicuri dalam masa penggunaan.'
            ],
        ];

        foreach ($statuses as $st) {
            DB::table('mst_asset_status')->updateOrCreate(['code' => $st['code']], $st);
        }
    }
}

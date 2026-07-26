<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name'       => 'Superadmin',
                'guard_name' => 'web',
            ],
            [
                'name'       => 'admin',
                'guard_name' => 'web',
            ],
            [
                'name'       => 'Director',
                'guard_name' => 'web',
            ],
            [
                'name'       => 'IT Manager',
                'guard_name' => 'web',
            ],
            [
                'name'       => 'Finance Manager',
                'guard_name' => 'web',
            ],
            [
                'name'       => 'Staff',
                'guard_name' => 'web',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrCreate(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

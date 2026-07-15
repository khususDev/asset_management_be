<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Administration\User::create([
            'name' => 'Septian',
            'email' => 'admin@erp.com',
            'role_id' => 1,
            'password' => Hash::make('112233'),
            'is_active' => true,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run()
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'Admin',
                'created_at' => '2025-08-22 18:32:27',
                'updated_at' => '2025-08-22 18:32:27',
            ],
            [
                'id' => 2,
                'name' => 'Vendor',
                'created_at' => '2025-08-22 18:32:27',
                'updated_at' => '2025-08-22 18:32:27',
            ],
            [
                'id' => 3,
                'name' => 'Staff',
                'created_at' => '2025-08-22 18:32:27',
                'updated_at' => '2025-08-22 18:32:27',
            ],
            [
                'id' => 4,
                'name' => 'Meter Reader Clerk',
                'created_at' => '2025-08-22 18:32:27',
                'updated_at' => '2025-08-22 18:32:27',
            ],
        ]);
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Ensure Admin Role exists
        $roleId = DB::table('roles')->where('name', 'Admin')->value('id');
        
        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Created Admin Role.');
        }

        // Create or Update Admin User
        DB::table('users')->updateOrInsert(
            ['username' => 'admin'], // Search by username
            [
                'role_id' => $roleId,
                'name' => 'Andy Po',
                'password' => Hash::make('password'), // Reset password to known value
                'status' => 'active',
                'contact_number' => '09384432421',
                'created_at' => now(),
                'updated_at' => now(),
                'last_login' => now(),
            ]
        );
        
        $this->command->info('Admin user seeded successfully.');
    }
}

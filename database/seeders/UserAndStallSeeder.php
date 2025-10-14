<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Section;
use App\Models\Stall;

class UserAndStallSeeder extends Seeder
{
    public function run(): void
    {
        // Find the roles and sections we created earlier
        $vendorRole = Role::where('name', 'Vendor')->first();
        $wetSection = Section::where('name', 'Wet Section')->first();
        $dryGoodsSection = Section::where('name', 'Dry Goods')->first();
        $semiDrySection = Section::where('name', 'Semi-Dry')->first();

        // 1. Create Vendor Users
        $vendorUser1 = User::create([
            'role_id' => $vendorRole->id,
            'name' => 'Johnny Doe',
            'username' => 'johnny.doe',
            'password' => Hash::make('password'),
            'status' => 'active',
            'contact_number' => '09171234567',
            'application_date' => '2025-01-15',
        ]);

        $vendorUser2 = User::create([
            'role_id' => $vendorRole->id,
            'name' => 'Jane Smith',
            'username' => 'jane.smith',
            'password' => Hash::make('password'),
            'status' => 'active',
            'contact_number' => '09177654321',
            'application_date' => '2025-02-20',
        ]);

        $vendorUser3 = User::create([
            'role_id' => $vendorRole->id,
            'name' => 'Peter Jones',
            'username' => 'peter.jones',
            'password' => Hash::make('password'),
            'status' => 'active',
            'contact_number' => '09178901234',
            'application_date' => '2025-03-10',
        ]);

        // 2. Create Stalls and assign them to vendors
        Stall::create([
            'section_id' => $wetSection->id,
            'stall_number' => 'WS-01',
            'vendor_id' => $vendorUser1->id,
        ]);

        Stall::create([
            'section_id' => $dryGoodsSection->id,
            'stall_number' => 'DG-01',
            'vendor_id' => $vendorUser2->id,
        ]);

        Stall::create([
            'section_id' => $semiDrySection->id,
            'stall_number' => 'SD-01',
            'vendor_id' => $vendorUser3->id,
        ]);
    }
}
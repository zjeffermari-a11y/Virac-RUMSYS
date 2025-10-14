<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;

class WaitingVendorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendorRole = Role::where('name', 'Vendor')->first();

        if (!$vendorRole) {
            $this->command->error('Vendor role not found! Please run RoleSeeder first.');
            return;
        }

        $vendors = [
            'Vendor Sixteen',
            'Vendor Seventeen',
            'Vendor Eighteen',
            'Vendor Nineteen',
            'Vendor Twenty',
            'Vendor Twenty One',
            'Vendor Twenty Two',
            'Vendor Twenty Three',
            'Vendor Twenty Four',
            'Vendor Twenty Five',
            'Vendor Twenty Six',
            'Vendor Twenty Seven',
            'Vendor Twenty Eight',
            'Vendor Twenty Nine',
            'Vendor Thirty',
        ];

        $this->command->info('Creating 15 additional waiting vendor accounts (Vendor 16-30)...');
        $this->command->newLine();

        foreach ($vendors as $index => $vendorName) {
            // Generate username starting from vendor_016 (since 1-15 already exist)
            $username = 'vendor_' . str_pad($index + 16, 3, '0', STR_PAD_LEFT);
            
            // Default temporary password
            $tempPassword = 'TempPass123!';

            $user = User::create([
                'role_id' => $vendorRole->id,
                'name' => $vendorName,
                'username' => $username,
                'password' => Hash::make($tempPassword),
                'status' => 'active',
                'contact_number' => null,
                'application_date' => null,
                'password_changed_at' => null, // Force password change on first login
            ]);

            $this->command->info("✓ Created: {$vendorName}");
            $this->command->line("  Username: {$username}");
            $this->command->line("  Temp Password: {$tempPassword}");
            $this->command->newLine();
        }

        $this->command->info('=================================================');
        $this->command->info('✓ Successfully created 15 additional vendor accounts!');
        $this->command->info('=================================================');
        $this->command->newLine();
        $this->command->warn('📋 Default credentials for new vendors:');
        $this->command->line('   Usernames: vendor_016 to vendor_030');
        $this->command->line('   Password: TempPass123!');
        $this->command->newLine();
        $this->command->info('💡 These vendors can now be assigned stalls in the Staff Portal.');
        $this->command->info('💡 On first login, they must change their password and can customize their username.');
        $this->command->info('💡 Contact number and application date are set to NULL (can be added later).');
    }
}


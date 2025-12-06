<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveTestContactNumbers extends Command
{
    protected $signature = 'users:remove-test-contacts';
    protected $description = 'Removes test contact numbers (6391712345...) from vendor records.';

    public function handle()
    {
        $this->info('Searching for vendors with test contact numbers...');

        // Find users with contact numbers starting with 6391712345
        $users = User::where('contact_number', 'like', '6391712345%')->get();

        if ($users->isEmpty()) {
            $this->info('No users found with test contact numbers.');
            return 0;
        }

        $this->info("Found {$users->count()} user(s) with test contact numbers:");
        
        $tableData = [];
        foreach ($users as $user) {
            $tableData[] = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'contact_number' => $user->contact_number,
            ];
            $this->line("  - ID: {$user->id}, Name: {$user->name}, Contact: {$user->contact_number}");
        }

        if ($this->confirm('Do you want to remove these contact numbers?', true)) {
            $updated = DB::table('users')
                ->where('contact_number', 'like', '6391712345%')
                ->update(['contact_number' => null]);

            $this->info("Successfully removed contact numbers from {$updated} user(s).");
            return 0;
        }

        $this->info('Operation cancelled.');
        return 0;
    }
}


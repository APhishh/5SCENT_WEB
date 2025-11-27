<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixAdminPasswords extends Command
{
    protected $signature = 'admin:fix-passwords';
    protected $description = 'Fix unhashed admin passwords by hashing them';

    public function handle()
    {
        $this->info('=== Admin Password Fix ===');
        $this->info('Checking and fixing unhashed passwords...');
        $this->newLine();

        $admins = Admin::all();
        $fixed = 0;
        $hashed = 0;

        foreach ($admins as $admin) {
            $is_hashed = str_starts_with($admin->password, '$2y$') || 
                        str_starts_with($admin->password, '$2a$') || 
                        str_starts_with($admin->password, '$2b$');
            
            if ($is_hashed) {
                $hashed++;
                $this->line("✓ Admin #{$admin->admin_id} ({$admin->email}) - Already hashed");
            } else {
                $this->warn("✗ Admin #{$admin->admin_id} ({$admin->email}) - Not hashed, fixing...");
                $admin->password = Hash::make($admin->password);
                $admin->save();
                $fixed++;
            }
        }

        $this->newLine();
        $this->info("Fixed: $fixed | Already Hashed: $hashed");
        $this->newLine();

        // Final verification
        $this->info('Final verification:');
        Admin::all()->each(function ($admin) {
            $status = str_starts_with($admin->password, '$2y$') || 
                     str_starts_with($admin->password, '$2a$') || 
                     str_starts_with($admin->password, '$2b$') ? '✓ HASHED' : '✗ NOT HASHED';
            $this->line("Admin #{$admin->admin_id} ({$admin->email}): $status");
        });

        $this->info('Done!');
        return 0;
    }
}

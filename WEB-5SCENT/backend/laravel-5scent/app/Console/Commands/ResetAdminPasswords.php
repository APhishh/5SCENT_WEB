<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPasswords extends Command
{
    protected $signature = 'admin:reset-passwords {password?}';
    protected $description = 'Reset all admin passwords to a known value';

    public function handle()
    {
        $password = $this->argument('password') ?? 'password';

        $this->info("=== Resetting Admin Passwords ===");
        $this->line("New password will be: $password\n");

        $admins = Admin::all();
        $updated = 0;

        foreach ($admins as $admin) {
            $admin->password = Hash::make($password);
            $admin->save();
            $this->line("✓ Reset password for Admin #{$admin->admin_id} ({$admin->email})");
            $updated++;
        }

        $this->info("\n✅ Updated $updated admin passwords!");
        $this->warn("\nIMPORTANT: All admins can now login with:");
        $this->line("  Password: $password");
    }
}

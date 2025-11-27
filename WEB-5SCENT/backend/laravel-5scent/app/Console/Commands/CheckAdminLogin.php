<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CheckAdminLogin extends Command
{
    protected $signature = 'admin:check-login {email} {password}';
    protected $description = 'Check admin login credentials';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("Checking login for: $email with password: $password");

        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            $this->error("❌ Admin not found with email: $email");
            return 1;
        }

        $this->line("✓ Admin found: {$admin->name}");
        $this->line("  Email: {$admin->email}");
        $this->line("  ID: {$admin->admin_id}");
        $this->line("  Password hash (first 20 chars): " . substr($admin->password, 0, 20) . "...");
        $this->line("  Is bcrypt hashed: " . (str_starts_with($admin->password, '$2y$') ? 'YES' : 'NO'));
        $this->newLine();

        // Test password verification
        $check = Hash::check($password, $admin->password);

        if ($check) {
            $this->info("✓ Password verification: SUCCESS");
            $this->info("✓ Admin can login!");
        } else {
            $this->error("✗ Password verification: FAILED");
            $this->error("✗ The password does not match");
            
            // Additional debug info
            $this->line("\nDEBUG: Trying alternate checks...");
            
            // Check if password might be in plain text
            if ($admin->password === $password) {
                $this->warn("⚠ Password is stored in plain text (not hashed)!");
                $this->warn("  This admin account needs to be fixed.");
            }
            
            // Show all admins with their status
            $this->line("\n=== All Admin Accounts ===");
            Admin::all()->each(function ($a) {
                $status = str_starts_with($a->password, '$2y$') ? '✓ HASHED' : '✗ PLAIN TEXT';
                $this->line("Admin #{$a->admin_id} ({$a->email}): $status");
            });
            
            return 1;
        }

        return 0;
    }
}

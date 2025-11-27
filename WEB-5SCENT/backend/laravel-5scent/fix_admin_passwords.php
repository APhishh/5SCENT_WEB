<?php

/**
 * Fix Admin Passwords
 * Hashes all unhashed passwords in the admin table
 * Run with: php artisan tinker < fix_admin_passwords.php
 */

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "Starting password hash fix...\n";

try {
    // Get all admins
    $admins = Admin::all();
    
    if ($admins->isEmpty()) {
        echo "No admin accounts found.\n";
        exit;
    }
    
    $updated = 0;
    $skipped = 0;
    
    foreach ($admins as $admin) {
        // Check if password looks like it's already hashed (bcrypt hashes start with $2y$)
        if (str_starts_with($admin->password, '$2y$') || str_starts_with($admin->password, '$2a$') || str_starts_with($admin->password, '$2b$')) {
            echo "[SKIP] Admin ID {$admin->admin_id} ({$admin->email}) - Password already hashed\n";
            $skipped++;
        } else {
            // Password is not hashed, hash it now
            // Try common default passwords
            $plainPassword = $admin->password; // Use existing password as-is
            
            $admin->password = Hash::make($plainPassword);
            $admin->save();
            
            echo "[FIXED] Admin ID {$admin->admin_id} ({$admin->email}) - Password hashed successfully\n";
            $updated++;
        }
    }
    
    echo "\n✅ Fix complete!\n";
    echo "Updated: $updated\n";
    echo "Skipped: $skipped\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

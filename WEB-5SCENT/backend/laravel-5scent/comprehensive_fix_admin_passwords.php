<?php

/**
 * Comprehensive Admin Password Fix
 * This script will:
 * 1. Show current password status
 * 2. Hash all unhashed passwords
 * 3. Show results
 * 
 * Run with: php artisan tinker < comprehensive_fix_admin_passwords.php
 */

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

echo "=================================================\n";
echo "ADMIN PASSWORD FIX UTILITY\n";
echo "=================================================\n\n";

// Step 1: Check current status
echo "STEP 1: Checking current password status...\n";
echo "-------------------------------------------------\n";

$admins = Admin::all();

if ($admins->isEmpty()) {
    echo "❌ No admin accounts found!\n";
    exit;
}

$hashed_count = 0;
$unhashed_count = 0;

foreach ($admins as $admin) {
    $is_hashed = str_starts_with($admin->password, '$2y$') || 
                 str_starts_with($admin->password, '$2a$') || 
                 str_starts_with($admin->password, '$2b$');
    
    if ($is_hashed) {
        $hashed_count++;
        echo "✓ Admin ID {$admin->admin_id} - {$admin->email} - [HASHED]\n";
    } else {
        $unhashed_count++;
        echo "✗ Admin ID {$admin->admin_id} - {$admin->email} - [NOT HASHED] Password: {$admin->password}\n";
    }
}

echo "\nStatus Summary:\n";
echo "  Hashed: $hashed_count\n";
echo "  Not Hashed: $unhashed_count\n";

// Step 2: Fix unhashed passwords
if ($unhashed_count > 0) {
    echo "\nSTEP 2: Fixing unhashed passwords...\n";
    echo "-------------------------------------------------\n";
    
    $fixed = 0;
    
    foreach ($admins as $admin) {
        $is_hashed = str_starts_with($admin->password, '$2y$') || 
                     str_starts_with($admin->password, '$2a$') || 
                     str_starts_with($admin->password, '$2b$');
        
        if (!$is_hashed) {
            $plain_password = $admin->password;
            $admin->password = Hash::make($plain_password);
            $admin->save();
            
            echo "✓ Fixed Admin ID {$admin->admin_id} ({$admin->email})\n";
            echo "  Old password: {$plain_password}\n";
            echo "  New hash: {$admin->password}\n\n";
            $fixed++;
        }
    }
    
    echo "\n✅ Fixed $fixed unhashed password(s)!\n";
} else {
    echo "\n✅ All passwords are already hashed!\n";
}

// Step 3: Show final status
echo "\nSTEP 3: Final status verification...\n";
echo "-------------------------------------------------\n";

$admins = Admin::all();
$all_hashed = true;

foreach ($admins as $admin) {
    $is_hashed = str_starts_with($admin->password, '$2y$') || 
                 str_starts_with($admin->password, '$2a$') || 
                 str_starts_with($admin->password, '$2b$');
    
    if (!$is_hashed) {
        $all_hashed = false;
        echo "✗ Admin ID {$admin->admin_id} - {$admin->email} - Still not hashed!\n";
    } else {
        echo "✓ Admin ID {$admin->admin_id} - {$admin->email} - Properly hashed\n";
    }
}

if ($all_hashed) {
    echo "\n🎉 SUCCESS! All admin passwords are now properly hashed!\n";
    echo "\nYou can now login with:\n";
    
    // Show one example from the first admin
    if ($admins->count() > 0) {
        $first = $admins->first();
        echo "  Email: {$first->email}\n";
        echo "  Password: (use the plain text password you set)\n";
    }
} else {
    echo "\n❌ Some passwords are still not hashed. Please try again.\n";
}

echo "\n=================================================\n";

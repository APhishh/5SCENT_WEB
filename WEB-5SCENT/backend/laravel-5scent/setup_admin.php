<?php

/**
 * Admin Setup Script
 * Creates sample admin account for testing
 * Run this from: php artisan tinker < setup_admin.php
 */

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

// Check if admin already exists
$existingAdmin = Admin::where('email', 'admin@5scent.com')->first();

if ($existingAdmin) {
    echo "Admin account already exists!\n";
    echo "Email: " . $existingAdmin->email . "\n";
    echo "Name: " . $existingAdmin->name . "\n";
    echo "Role: " . $existingAdmin->role . "\n";
} else {
    // Create new admin account
    $admin = Admin::create([
        'name' => 'Admin User',
        'email' => 'admin@5scent.com',
        'password' => Hash::make('AdminPass123!'),
        'role' => 'admin',
    ]);

    echo "Admin account created successfully!\n";
    echo "Email: " . $admin->email . "\n";
    echo "Password: AdminPass123!\n";
    echo "Name: " . $admin->name . "\n";
    echo "Role: " . $admin->role . "\n";
}

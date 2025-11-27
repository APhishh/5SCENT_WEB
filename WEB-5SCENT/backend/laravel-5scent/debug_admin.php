<?php

require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Admin Debug Info ===\n";
echo "Checking admin accounts...\n\n";

$admins = Admin::all();

foreach ($admins as $admin) {
    echo "Admin ID: {$admin->admin_id}\n";
    echo "  Email: {$admin->email}\n";
    echo "  Name: {$admin->name}\n";
    echo "  Password Hash: " . substr($admin->password, 0, 20) . "...\n";
    echo "  Is Hashed: " . (str_starts_with($admin->password, '$2y$') ? 'YES' : 'NO') . "\n";
    echo "\n";
}

// Test password verification
echo "=== Testing Password Verification ===\n";
$testAdmin = Admin::first();
if ($testAdmin) {
    echo "Testing with admin: {$testAdmin->email}\n";
    
    // Try to verify with the plain text that's in the database
    $result = Hash::check('admin123', $testAdmin->password);
    echo "Hash::check('admin123', hash) = " . ($result ? 'TRUE' : 'FALSE') . "\n";
    
    $result2 = Hash::check($testAdmin->password, $testAdmin->password);
    echo "Hash::check(plaintext, plaintext) = " . ($result2 ? 'TRUE' : 'FALSE') . "\n";
}

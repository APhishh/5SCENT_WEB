<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Admin;

$admin = Admin::first();
if ($admin) {
    $token = $admin->createToken('admin_token')->plainTextToken;
    echo "Admin Token: " . $token . "\n";
    echo "Admin Email: " . $admin->email . "\n";
} else {
    echo "No admin found\n";
}

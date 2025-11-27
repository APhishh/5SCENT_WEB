<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$table = DB::select('DESCRIBE payments');
echo "Payments table columns:\n";
foreach ($table as $col) {
    echo $col->Field . "\n";
}

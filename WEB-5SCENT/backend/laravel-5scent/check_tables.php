<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('config');

// Get database config
$conn = config('database.connections.' . config('database.default'));
$mysql = new mysqli($conn['host'], $conn['username'], $conn['password'], $conn['database']);

if ($mysql->connect_error) {
    die("Connection failed: " . $mysql->connect_error);
}

// Check payment table
echo "=== PAYMENT TABLE COLUMNS ===\n";
$result = $mysql->query('SHOW COLUMNS FROM payment');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

// Check orders table
echo "\n=== ORDERS TABLE COLUMNS ===\n";
$result = $mysql->query('SHOW COLUMNS FROM orders');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

// Check orderdetail table
echo "\n=== ORDERDETAIL TABLE COLUMNS ===\n";
$result = $mysql->query('SHOW COLUMNS FROM orderdetail');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

$mysql->close();

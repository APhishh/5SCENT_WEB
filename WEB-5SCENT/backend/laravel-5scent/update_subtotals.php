<?php
require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=db_5scent', 'root', '');

// Update orderdetail with subtotal = price * quantity where subtotal is null
$sql = "UPDATE orderdetail SET subtotal = price * quantity WHERE subtotal IS NULL";
$result = $pdo->exec($sql);

echo "Updated $result rows with subtotal calculation\n";

// Verify
$result = $pdo->query('SELECT COUNT(*) as count FROM orderdetail WHERE subtotal IS NULL OR subtotal = 0');
$row = $result->fetch();
echo "Rows still without subtotal: " . $row['count'] . "\n";

// Show sample data
echo "\nSample orderdetail data:\n";
$result = $pdo->query('SELECT order_detail_id, price, quantity, subtotal FROM orderdetail LIMIT 5');
foreach($result as $row) {
    echo "Detail {$row['order_detail_id']}: price={$row['price']}, qty={$row['quantity']}, subtotal={$row['subtotal']}\n";
}

<?php
// This is a test to see what the API would return
require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=db_5scent', 'root', '');

// Simulate what the API returns
$orders = [];
$result = $pdo->query('SELECT * FROM orders WHERE user_id = 17 ORDER BY created_at DESC');
foreach($result as $row) {
    $orders[] = [
        'order_id' => (int)$row['order_id'],
        'user_id' => (int)$row['user_id'],
        'subtotal' => (float)$row['subtotal'],
        'total_price' => (float)$row['total_price'],
        'status' => $row['status'],
        'shipping_address' => $row['shipping_address'],
        'tracking_number' => $row['tracking_number'],
        'created_at' => $row['created_at'],
        'payment_method' => $row['payment_method'],
    ];
}

// Simulate grouping like the API does
$grouped = [
    'in_process' => [],
    'shipping' => [],
    'completed' => [],
    'canceled' => [],
];

foreach($orders as $order) {
    if (in_array($order['status'], ['Pending', 'Packaging'])) {
        $grouped['in_process'][] = $order;
    } elseif ($order['status'] === 'Shipping') {
        $grouped['shipping'][] = $order;
    } elseif ($order['status'] === 'Delivered') {
        $grouped['completed'][] = $order;
    } elseif ($order['status'] === 'Cancelled') {
        $grouped['canceled'][] = $order;
    }
}

echo "=== RAW ORDERS ===\n";
echo json_encode($orders, JSON_PRETTY_PRINT) . "\n\n";

echo "=== GROUPED ORDERS ===\n";
echo json_encode($grouped, JSON_PRETTY_PRINT) . "\n";

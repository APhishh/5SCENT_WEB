<?php
// Test what the API endpoint returns for user 17
require 'vendor/autoload.php';

// Get auth token or simulate one
// For now, let's query directly from the database what would be returned

$pdo = new PDO('mysql:host=localhost;dbname=db_5scent', 'root', '');

echo "=== SIMULATING API /orders ENDPOINT FOR USER 17 ===\n\n";

// Get all orders for user 17 with their relations
$sql = "SELECT o.*, 
               GROUP_CONCAT(od.order_detail_id) as detail_ids,
               GROUP_CONCAT(od.product_id) as product_ids
        FROM orders o
        LEFT JOIN orderdetail od ON o.order_id = od.order_id
        WHERE o.user_id = 17
        GROUP BY o.order_id
        ORDER BY o.created_at DESC";

$result = $pdo->query($sql);
$orders = [];

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

echo "Raw Orders Array:\n";
echo json_encode($orders, JSON_PRETTY_PRINT) . "\n\n";

// Now simulate the grouping logic
$grouped = [
    'in_process' => [],
    'shipping' => [],
    'completed' => [],
    'canceled' => [],
];

foreach($orders as $order) {
    echo "Processing order {$order['order_id']} with status '{$order['status']}'\n";
    
    if (in_array($order['status'], ['Pending', 'Packaging'])) {
        $grouped['in_process'][] = $order;
        echo "  → Added to in_process\n";
    } elseif ($order['status'] === 'Shipping') {
        $grouped['shipping'][] = $order;
        echo "  → Added to shipping\n";
    } elseif ($order['status'] === 'Delivered') {
        $grouped['completed'][] = $order;
        echo "  → Added to completed\n";
    } elseif ($order['status'] === 'Cancelled') {
        $grouped['canceled'][] = $order;
        echo "  → Added to canceled\n";
    } else {
        echo "  → NO MATCH! Status is neither Pending, Packaging, Shipping, Delivered, nor Cancelled\n";
    }
}

echo "\n\nGrouped Orders:\n";
echo json_encode($grouped, JSON_PRETTY_PRINT) . "\n";

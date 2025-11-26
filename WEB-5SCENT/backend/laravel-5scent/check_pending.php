<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_5scent', 'root', '');

echo "=== USER 17 ALL ORDERS WITH DETAILS ===\n";
$result = $pdo->query('SELECT order_id, status, created_at FROM orders WHERE user_id = 17 ORDER BY order_id DESC');
foreach($result as $row) {
    echo 'Order ' . $row['order_id'] . ' - Status: ' . $row['status'] . ' - Created: ' . $row['created_at'] . "\n";
}

echo "\n=== ORDER DETAILS COUNT ===\n";
$result = $pdo->query('SELECT o.order_id, o.status, COUNT(od.order_detail_id) as detail_count FROM orders o LEFT JOIN orderdetail od ON o.order_id = od.order_id WHERE o.user_id = 17 GROUP BY o.order_id ORDER BY o.order_id DESC');
foreach($result as $row) {
    echo 'Order ' . $row['order_id'] . ' - Status: ' . $row['status'] . ' - Details: ' . $row['detail_count'] . "\n";
}

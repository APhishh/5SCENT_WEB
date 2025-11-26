<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_5scent', 'root', '');

echo "=== ORDERS TABLE STRUCTURE ===\n";
$result = $pdo->query('DESCRIBE orders');
foreach($result as $row) {
    echo implode(' | ', $row) . "\n";
}

echo "\n=== ORDERDETAIL TABLE STRUCTURE ===\n";
$result = $pdo->query('DESCRIBE orderdetail');
foreach($result as $row) {
    echo implode(' | ', $row) . "\n";
}

echo "\n=== USER 17 ORDERS ===\n";
$result = $pdo->query('SELECT * FROM orders WHERE user_id = 17');
foreach($result as $row) {
    print_r($row);
}

echo "\n=== USER 17 CANCELLED ORDERS WITH STATUS ===\n";
$result = $pdo->query('SELECT order_id, status, created_at FROM orders WHERE user_id = 17 AND status = "Cancel"');
foreach($result as $row) {
    echo implode(' | ', $row) . "\n";
}

echo "\n=== ORDERDETAILS FOR USER 17 CANCELLED ORDERS ===\n";
$result = $pdo->query('SELECT od.* FROM orderdetail od JOIN orders o ON od.order_id = o.order_id WHERE o.user_id = 17 AND o.status = "Cancel"');
foreach($result as $row) {
    print_r($row);
}

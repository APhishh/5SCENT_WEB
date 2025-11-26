<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=db_5scent', 'root', '');
    
    // Check payment table
    echo "=== PAYMENT TABLE COLUMNS ===\n";
    $result = $pdo->query('SHOW COLUMNS FROM payment');
    foreach($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }

    // Check orders table
    echo "\n=== ORDERS TABLE COLUMNS ===\n";
    $result = $pdo->query('SHOW COLUMNS FROM orders');
    foreach($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }

    // Check orderdetail table
    echo "\n=== ORDERDETAIL TABLE COLUMNS ===\n";
    $result = $pdo->query('SHOW COLUMNS FROM orderdetail');
    foreach($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

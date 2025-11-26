<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_5scent', 'root', '');

echo "=== ORDERDETAIL TABLE SCHEMA ===\n";
$result = $pdo->query('DESCRIBE orderdetail');
foreach($result as $row) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== SAMPLE ORDERDETAIL DATA ===\n";
$result = $pdo->query('SELECT * FROM orderdetail LIMIT 5');
foreach($result as $row) {
    print_r($row);
}

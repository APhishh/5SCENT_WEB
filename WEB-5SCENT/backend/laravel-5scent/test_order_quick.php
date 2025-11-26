<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\Order;
use App\Models\OrderDetail;

// Test creating order
try {
    $order = Order::create([
        'user_id' => 1,
        'status' => 'Pending',
        'shipping_address' => '123 Main St, District, City, Province 12345',
        'subtotal' => 100000,
        'total_price' => 105000,
        'payment_method' => 'QRIS',
    ]);
    
    echo "✓ Order created successfully (ID: " . $order->order_id . ")\n";
    
    // Test creating order detail
    $detail = OrderDetail::create([
        'order_id' => $order->order_id,
        'product_id' => 1,
        'size' => '30ml',
        'quantity' => 1,
        'price' => 100000,
        'subtotal' => 100000,
    ]);
    
    echo "✓ OrderDetail created successfully (ID: " . $detail->order_detail_id . ")\n";
    echo "\n✅ All tests passed!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nFull Stack:\n";
    echo $e->getTraceAsString() . "\n";
}

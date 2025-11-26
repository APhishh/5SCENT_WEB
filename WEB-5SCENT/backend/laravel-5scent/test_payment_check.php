<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\Order;
use App\Models\Payment;

// Get the most recent order
$order = Order::with('payment')->latest('order_id')->first();

if (!$order) {
    echo "❌ No orders found\n";
    exit;
}

echo "Order ID: " . $order->order_id . "\n";
echo "Payment data:\n";

if ($order->payment) {
    echo "  Payment ID: " . $order->payment->payment_id . "\n";
    echo "  Method: " . $order->payment->method . "\n";
    echo "  Status: " . $order->payment->status . "\n";
    echo "  Amount: " . $order->payment->amount . "\n";
    
    if ($order->payment->method === 'QRIS') {
        echo "\n✓ Method check would PASS (method is QRIS)\n";
    } else {
        echo "\n❌ Method check would FAIL (method is: " . $order->payment->method . ")\n";
    }
} else {
    echo "❌ No payment found for order\n";
}

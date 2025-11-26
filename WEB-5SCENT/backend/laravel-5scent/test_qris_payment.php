<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\Order;

// Get the most recent order
$order = Order::with('payment')->latest('order_id')->first();

if (!$order) {
    echo "❌ No orders found\n";
    exit;
}

echo "Testing createQrisPayment endpoint...\n";
echo "Order ID: " . $order->order_id . "\n";

// Simulate the API call
$controller = new \App\Http\Controllers\PaymentController();
$request = new \Illuminate\Http\Request();
$request->merge(['order_id' => $order->order_id]);

try {
    $result = $controller->createQrisPayment($request);
    
    // Get the response data
    $data = json_decode($result->getContent(), true);
    
    echo "\n✓ Response received:\n";
    echo "  Status: " . $result->getStatusCode() . "\n";
    echo "  Token: " . ($data['token'] ?? 'N/A') . "\n";
    echo "  Redirect URL: " . ($data['redirect_url'] ?? 'N/A') . "\n";
    echo "  Message: " . ($data['message'] ?? 'N/A') . "\n";
    
    echo "\n✅ Payment creation successful!\n";
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

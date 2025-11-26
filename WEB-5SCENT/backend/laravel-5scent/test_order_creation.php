<?php
require 'vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cart, App\Models\Order, App\Models\OrderDetail;

// Get test cart items for user 1
$cartItems = Cart::with('product')->where('user_id', 1)->limit(2)->get();

if ($cartItems->isEmpty()) {
    echo "No cart items found for user 1\n";
    exit;
}

echo "Cart items found: " . $cartItems->count() . "\n";

// Try to create an order manually
try {
    $subtotal = $cartItems->sum(function($item) {
        $price = $item->size === '30ml' 
            ? $item->product->price_30ml 
            : $item->product->price_50ml;
        return $price * $item->quantity;
    });

    $tax = $subtotal * 0.05;
    $totalPrice = $subtotal + $tax;

    echo "Creating order...\n";
    echo "  Subtotal: $subtotal\n";
    echo "  Tax: $tax\n";
    echo "  Total: $totalPrice\n";

    $order = Order::create([
        'user_id' => 1,
        'status' => 'Pending',
        'shipping_address' => 'Test address, test city, test province 12345',
        'subtotal' => $subtotal,
        'total_price' => $totalPrice,
        'payment_method' => 'QRIS',
    ]);

    echo "Order created with ID: " . $order->order_id . "\n";

    foreach ($cartItems as $cartItem) {
        $price = $cartItem->size === '30ml' 
            ? $cartItem->product->price_30ml 
            : $cartItem->product->price_50ml;
        
        $subtotal = $price * $cartItem->quantity;

        echo "Creating OrderDetail for product " . $cartItem->product_id . "...\n";

        OrderDetail::create([
            'order_id' => $order->order_id,
            'product_id' => $cartItem->product_id,
            'size' => $cartItem->size,
            'quantity' => $cartItem->quantity,
            'price' => $price,
            'subtotal' => $subtotal,
        ]);

        echo "  OrderDetail created\n";
    }

    echo "Success! Order and details created\n";

    // Test retrieving the order
    $retrievedOrder = Order::with('details.product.images')->find($order->order_id);
    echo "Retrieved order: " . $retrievedOrder->order_id . "\n";
    echo "Details count: " . $retrievedOrder->details->count() . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Cart;
use App\Models\User;

// Get or create test user
$user = User::firstOrCreate(
    ['email' => 'test@example.com'],
    ['name' => 'Test User', 'phone_number' => '081234567890']
);

echo "User: " . $user->user_id . "\n";

// Get cart items
$cartItems = Cart::with('product')
    ->where('user_id', $user->user_id)
    ->get();

echo "Cart items: " . $cartItems->count() . "\n";

if ($cartItems->isEmpty()) {
    echo "❌ No cart items found\n";
    exit;
}

try {
    // Calculate totals
    $subtotal = $cartItems->sum(function($item) {
        $price = $item->size === '30ml' 
            ? $item->product->price_30ml 
            : $item->product->price_50ml;
        return $price * $item->quantity;
    });

    $tax = $subtotal * 0.05;
    $totalPrice = $subtotal + $tax;

    echo "Subtotal: " . $subtotal . "\n";
    echo "Tax: " . $tax . "\n";
    echo "Total: " . $totalPrice . "\n";

    // Create order
    $order = Order::create([
        'user_id' => $user->user_id,
        'status' => 'Pending',
        'shipping_address' => 'Jl. Test No. 123, Kelurahan, Kota, Provinsi 12345',
        'subtotal' => $subtotal,
        'total_price' => $totalPrice,
        'payment_method' => 'QRIS',
    ]);

    echo "✓ Order created: " . $order->order_id . "\n";

    // Create order details
    foreach ($cartItems as $cartItem) {
        $price = $cartItem->size === '30ml' 
            ? $cartItem->product->price_30ml 
            : $cartItem->product->price_50ml;
        
        $itemSubtotal = $price * $cartItem->quantity;

        $detail = OrderDetail::create([
            'order_id' => $order->order_id,
            'product_id' => $cartItem->product_id,
            'size' => $cartItem->size,
            'quantity' => $cartItem->quantity,
            'price' => $price,
            'subtotal' => $itemSubtotal,
        ]);

        echo "✓ OrderDetail created: " . $detail->order_detail_id . "\n";

        // Restore stock
        $stockField = $cartItem->size === '30ml' ? 'stock_30ml' : 'stock_50ml';
        $cartItem->product->decrement($stockField, $cartItem->quantity);
    }

    // Create payment
    $payment = Payment::create([
        'order_id' => $order->order_id,
        'method' => 'QRIS',
        'amount' => $totalPrice,
        'status' => 'Pending',
    ]);

    echo "✓ Payment created: " . $payment->payment_id . "\n";

    // Clear cart
    Cart::where('user_id', $user->user_id)->whereIn('cart_id', $cartItems->pluck('cart_id'))->delete();

    echo "\n✅ FULL CHECKOUT TEST PASSED!\n";
    echo "Order ID: " . $order->order_id . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

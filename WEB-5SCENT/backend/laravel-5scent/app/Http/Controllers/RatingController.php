<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Order;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:product,product_id',
            'order_id' => 'required|exists:orders,order_id',
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $order = Order::where('user_id', $request->user()->user_id)
            ->findOrFail($validated['order_id']);

        if ($order->status !== 'Delivered') {
            return response()->json([
                'message' => 'You can only rate products from delivered orders'
            ], 400);
        }

        $existingRating = Rating::where('user_id', $request->user()->user_id)
            ->where('product_id', $validated['product_id'])
            ->where('order_id', $validated['order_id'])
            ->first();

        if ($existingRating) {
            return response()->json([
                'message' => 'You have already rated this product for this order'
            ], 400);
        }

        $rating = Rating::create([
            'user_id' => $request->user()->user_id,
            'product_id' => $validated['product_id'],
            'order_id' => $validated['order_id'],
            'stars' => $validated['stars'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($rating->load('user'), 201);
    }

    public function getOrderReviews($orderId, Request $request)
    {
        $order = Order::where('user_id', $request->user()->user_id)
            ->findOrFail($orderId);

        $reviews = Rating::where('order_id', $orderId)
            ->where('user_id', $request->user()->user_id)
            ->get();

        return response()->json($reviews);
    }

    public function getFullyReviewedOrders(Request $request)
    {
        $user = $request->user();

        // Get all orders with their details for this user
        $orders = Order::with('details')
            ->where('user_id', $user->user_id)
            ->where('status', 'Delivered')
            ->get();

        // Get all ratings for this user
        $ratings = Rating::where('user_id', $user->user_id)
            ->get(['product_id', 'order_id']);

        $reviewedProductsByOrder = [];
        foreach ($ratings as $rating) {
            if (!isset($reviewedProductsByOrder[$rating->order_id])) {
                $reviewedProductsByOrder[$rating->order_id] = [];
            }
            $reviewedProductsByOrder[$rating->order_id][] = $rating->product_id;
        }

        // Check which orders have all products reviewed
        $fullyReviewedOrderIds = [];
        foreach ($orders as $order) {
            $reviewedProducts = $reviewedProductsByOrder[$order->order_id] ?? [];
            $orderProductIds = $order->details->pluck('product_id')->toArray();
            
            if (!empty($orderProductIds) && count(array_intersect($reviewedProducts, $orderProductIds)) === count($orderProductIds)) {
                $fullyReviewedOrderIds[] = $order->order_id;
            }
        }

        return response()->json(['fully_reviewed_order_ids' => $fullyReviewedOrderIds]);
    }

    public function update($ratingId, Request $request)
    {
        $validated = $request->validate([
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rating = Rating::findOrFail($ratingId);

        // Verify the rating belongs to the authenticated user
        if ($rating->user_id !== $request->user()->user_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $rating->update([
            'stars' => $validated['stars'],
            'comment' => $validated['comment'] ?? $rating->comment,
        ]);

        return response()->json($rating, 200);
    }
}


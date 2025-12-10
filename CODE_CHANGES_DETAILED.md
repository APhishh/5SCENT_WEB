# Code Changes Summary

## 1. OrderController.php - store() method

### Before:
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'cart_ids' => 'required|array',
        'cart_ids.*' => 'exists:cart,cart_id',
        'shipping_address' => 'required|string|max:255',
        'payment_method' => 'required|in:QRIS,Virtual_Account,Cash',
    ]);

    // ... calculate subtotal and tax ...

    $order = Order::create([
        'user_id' => $request->user()->user_id,
        'status' => $orderStatus,
        'shipping_address' => $validated['shipping_address'],
        'subtotal' => $subtotal,
        'total_price' => $totalPrice,
        'payment_method' => $validated['payment_method'],
    ]);
}
```

### After:
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'cart_ids' => 'required|array',
        'cart_ids.*' => 'exists:cart,cart_id',
        'address_line' => 'required|string|max:255',
        'district' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'province' => 'required|string|max:255',
        'postal_code' => 'required|string|max:20',
        'payment_method' => 'required|in:QRIS,Virtual_Account,Cash',
    ]);

    // ... calculate subtotal and tax ...

    $order = Order::create([
        'user_id' => $request->user()->user_id,
        'status' => $orderStatus,
        'address_line' => $validated['address_line'],
        'district' => $validated['district'],
        'city' => $validated['city'],
        'province' => $validated['province'],
        'postal_code' => $validated['postal_code'],
        'subtotal' => $subtotal,
        'total_price' => $totalPrice,
        'payment_method' => $validated['payment_method'],
    ]);
}
```

---

## 2. Order.php Model - $fillable array

### Before:
```php
protected $fillable = [
    'user_id',
    'subtotal',
    'total_price',
    'status',
    'shipping_address',
    'tracking_number',
    'payment_method',
];
```

### After:
```php
protected $fillable = [
    'user_id',
    'subtotal',
    'total_price',
    'status',
    'address_line',
    'district',
    'city',
    'province',
    'postal_code',
    'tracking_number',
    'payment_method',
];
```

---

## 3. checkout/page.tsx - handleCheckout() method

### Before:
```tsx
const handleCheckout = async () => {
    if (!validateForm()) {
        showToast('Please fix the highlighted fields before continuing', 'error');
        return;
    }

    setLoading(true);
    try {
        const response = await api.post('/orders', {
            cart_ids: selectedItemIds,
            shipping_address: `${formData.addressLine}, ${formData.district}, ${formData.city}, ${formData.province} ${formData.postalCode}`,
            payment_method: paymentMethod,
        });
```

### After:
```tsx
const handleCheckout = async () => {
    if (!validateForm()) {
        showToast('Please fix the highlighted fields before continuing', 'error');
        return;
    }

    setLoading(true);
    try {
        const response = await api.post('/orders', {
            cart_ids: selectedItemIds,
            address_line: formData.addressLine,
            district: formData.district,
            city: formData.city,
            province: formData.province,
            postal_code: formData.postalCode,
            payment_method: paymentMethod,
        });
```

---

## 4. orders/page.tsx - OrderData interface

### Before:
```typescript
interface OrderData {
    order_id: number;
    user_id: number;
    total_price: number;
    status: 'Pending' | 'Packaging' | 'Shipping' | 'Delivered' | 'Cancelled';
    shipping_address: string;
    tracking_number?: string;
    payment_method?: 'QRIS' | 'Virtual_Account' | 'Cash';
    created_at: string;
    updated_at: string;
    details: OrderItem[];
    user?: {
        name: string;
        phone?: string;
    };
    payment?: {
        payment_id: number;
        method: string;
        status: string;
    };
}
```

### After:
```typescript
interface OrderData {
    order_id: number;
    user_id: number;
    total_price: number;
    status: 'Pending' | 'Packaging' | 'Shipping' | 'Delivered' | 'Cancelled';
    address_line?: string;
    district?: string;
    city?: string;
    province?: string;
    postal_code?: string;
    tracking_number?: string;
    payment_method?: 'QRIS' | 'Virtual_Account' | 'Cash';
    created_at: string;
    updated_at: string;
    details: OrderItem[];
    user?: {
        name: string;
        phone?: string;
    };
    payment?: {
        payment_id: number;
        method: string;
        status: string;
    };
}
```

---

## 5. orders/page.tsx - Shipping Address display in modal

### Before:
```tsx
{/* Shipping Address */}
<div className="bg-gray-100 rounded-2xl p-6">
    <h3 className="text-base font-semibold text-gray-900 mb-4">Shipping Address</h3>
    <div className="flex gap-3">
        <IoLocationOutline className="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
        <p className="text-sm text-gray-700">{modal.order.shipping_address}</p>
    </div>
</div>
```

### After:
```tsx
{/* Shipping Address */}
<div className="bg-gray-100 rounded-2xl p-6">
    <h3 className="text-base font-semibold text-gray-900 mb-4">Shipping Address</h3>
    <div className="flex gap-3">
        <IoLocationOutline className="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
        <div className="text-sm text-gray-700">
            {modal.order.address_line ? (
                <>
                    <p>{modal.order.address_line}</p>
                    <p>{modal.order.district}</p>
                    <p>{modal.order.city}, {modal.order.province} {modal.order.postal_code}</p>
                </>
            ) : (
                <p>No address information</p>
            )}
        </div>
    </div>
</div>
```

---

## 6. admin/orders/page.tsx - Order interface

### Before:
```typescript
interface Order {
    order_id: number;
    user_id: number;
    status: string;
    tracking_number: string | null;
    subtotal: number;
    total_price: number;
    shipping_address: string;
    created_at: string;
    payment_method?: string;
    payment?: Payment;
    user?: {
        name: string;
        email: string;
        phone?: string;
        address_line?: string;
        district?: string;
        city?: string;
        province?: string;
        postal_code?: string;
    };
    details?: OrderItem[];
}
```

### After:
```typescript
interface Order {
    order_id: number;
    user_id: number;
    status: string;
    tracking_number: string | null;
    subtotal: number;
    total_price: number;
    address_line: string | null;
    district: string | null;
    city: string | null;
    province: string | null;
    postal_code: string | null;
    created_at: string;
    payment_method?: string;
    payment?: Payment;
    user?: {
        name: string;
        email: string;
        phone?: string;
    };
    details?: OrderItem[];
}
```

---

## 7. admin/orders/page.tsx - Order list address display

### Before:
```tsx
{/* Second Row */}
<div className="flex items-start justify-between">
    <div className="flex-1">
        <div className="text-xs text-gray-500 font-medium mb-1">Customer Address</div>
        <div className="text-sm text-gray-700">{order.shipping_address || 'N/A'}</div>
    </div>
```

### After:
```tsx
{/* Second Row */}
<div className="flex items-start justify-between">
    <div className="flex-1">
        <div className="text-xs text-gray-500 font-medium mb-1">Customer Address</div>
        <div className="text-sm text-gray-700">
            {order.address_line && order.city
                ? `${order.address_line}, ${order.district}, ${order.city}, ${order.province} ${order.postal_code}`
                : 'N/A'}
        </div>
    </div>
```

---

## 8. admin/orders/page.tsx - Order detail modal address display

### Before:
```tsx
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">Address</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.user?.address_line || 'N/A'}</div>
</div>
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">District</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.user?.district || 'N/A'}</div>
</div>
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">City</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.user?.city || 'N/A'}</div>
</div>
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">Province</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.user?.province || 'N/A'}</div>
</div>
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">Postal Code</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.user?.postal_code || 'N/A'}</div>
</div>
```

### After:
```tsx
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">Shipping Address</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.address_line || 'N/A'}</div>
</div>
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">District</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.district || 'N/A'}</div>
</div>
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">City</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.city || 'N/A'}</div>
</div>
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">Province</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.province || 'N/A'}</div>
</div>
<div>
    <div className="text-xs font-medium text-gray-600 mb-1">Postal Code</div>
    <div className="text-sm font-medium text-gray-900">{selectedOrder.postal_code || 'N/A'}</div>
</div>
```

---

## Migration File: 2025_12_10_refactor_orders_address_columns.php

**Status:** ✅ Created and executed successfully

**Key Features:**
- Adds 5 new address columns to orders table
- Safely parses existing shipping_address data
- Splits format: "address, district, city, province postal_code"
- Handles malformed data gracefully
- Drops old shipping_address column after migration
- Full rollback support in down() method

---

## Summary of Changes

| Component | Type | Action |
|-----------|------|--------|
| Database | Schema | ADD 5 columns, DROP 1 column |
| OrderController | Logic | Accept 5 address fields, store separately |
| Order Model | Config | Update $fillable array |
| Checkout Frontend | API | Send 5 separate fields instead of 1 string |
| Order History | Display | Read address from order, not user profile |
| Admin Orders List | Display | Read address from order, not user profile |
| Admin Order Detail | Display | Read address from order, not user profile |

All changes have been implemented and migration has been executed successfully!

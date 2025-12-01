# Orders Management Page - Admin Updates

## Summary
Updated admin orders management page to fix payment method display and add POS transactions visualization.

## Changes Made

### 1. Fixed Payment Method Display (Issue #1)

**Problem:** All orders showed "QRIS" regardless of actual payment method.

**Solution:** Updated payment method field to display actual `order.payment_method` value with proper formatting.

**Code:**
```tsx
// Before (Hardcoded)
<div className="px-3 py-1 bg-gray-100 border border-gray-300 rounded-lg text-sm font-medium text-gray-900 inline-block">
  QRIS
</div>

// After (Dynamic)
<div className="px-3 py-1 bg-gray-100 border border-gray-300 rounded-lg text-sm font-medium text-gray-900 inline-block">
  {order.payment_method ? order.payment_method.replace('_', ' ') : 'N/A'}
</div>
```

**Benefits:**
- Shows actual payment method (QRIS, Virtual_Account, Cash)
- Underscores replaced with spaces for better display (Virtual_Account → Virtual Account)
- Fallback to 'N/A' if no payment method set

---

### 2. Added POS Transactions Display (Issue #2)

**Problem:** POS transactions not visible in orders management page.

**Solution:** Fetched and displayed POS transactions as separate cards with distinct visual styling.

#### TypeScript Interfaces Added

```typescript
interface PosItem {
  pos_item_id: number;
  product_id: number;
  size: string;
  quantity: number;
  price: number;
  subtotal: number;
  product?: { name: string; product_id: number };
}

interface PosTransaction {
  transaction_id: number;
  admin_id: number;
  customer_name: string;
  phone: string;
  date: string;
  total_price: number;
  payment_method: string;
  cash_received?: number | null;
  cash_change?: number | null;
  order_id?: number | null;
  items?: PosItem[];
  admin?: { admin_id: number; name: string };
}

interface PosTransactionResponse {
  data: PosTransaction[];
  current_page: number;
  last_page: number;
  total: number;
}
```

#### State Variables Added

```typescript
const [posTransactions, setPosTransactions] = useState<PosTransaction[]>([]);
const [posPage, setPosPage] = useState(1);
const [posTotalPages, setPosTotalPages] = useState(1);
const [posTotal, setPosTotal] = useState(0);
```

#### API Integration

```typescript
const fetchPosTransactions = async () => {
  try {
    const response = await api.get<PosTransactionResponse>(
      `/admin/pos/transactions?page=${posPage}`
    );
    const data = response.data;
    
    setPosTransactions(data.data || []);
    setPosPage(data.current_page || 1);
    setPosTotalPages(data.last_page || 1);
    setPosTotal(data.total || 0);
  } catch (error) {
    console.error('Error fetching POS transactions:', error);
  }
};
```

#### POS Transaction Card Design

Each POS transaction displays as:

**Visual Style:**
- Green left border (4px) to distinguish from orders
- Green payment method badge
- Hover shadow effect
- Two-row layout similar to order cards

**Row 1 - Header:**
```
[POS Order ID: #123] | [Customer: David] | [Payment: QRIS] | [Status: Completed]
```

**Row 2 - Details:**
```
[Phone: +62 873 824 674] | [Items: 2 product(s)] | [Total: Rp277,000] | [Date: 2024-11-12]
```

#### Pagination for POS

```tsx
<button onClick={() => setPosPage(prev => Math.max(1, prev - 1))}>
  Previous POS
</button>
<button onClick={() => setPosPage(prev => Math.min(posTotalPages, prev + 1))}>
  Next POS
</button>
```

Separate from order pagination, allowing independent navigation.

---

### 3. Backend Updates

**File:** `app/Http/Controllers/DashboardController.php`

Updated orders query to explicitly select all columns:
```php
$query = Order::with('user', 'details.product.images')->select('*');
```

This ensures payment_method field is included in the response.

---

## Display Examples

### Regular Order Card (Standard):
```
Order ID: #ORD-01-12-2025-001
Customer: John Doe
Payment: Virtual Account
Status: Pending
```

### POS Transaction Card (Green):
```
POS Order ID: #123
Customer: David  
Payment: QRIS
Status: Completed
Phone: +62 873 824 674
Items: 2 product(s)
Total: Rp277,000
Date: 2024-11-12
```

---

## Payment Method Format

The system now properly handles payment method ENUM values:
- `QRIS` → "QRIS"
- `Virtual_Account` → "Virtual Account" 
- `Cash` → "Cash"

---

## Files Modified
- Shows success toast notification

**`getActionButton(order)`**
- Returns status-specific action button:
  - **Shipping**: Black "Mark as Received" button
  - **Packaging**: Red outline "Cancel Order" button
  - **Delivered**: Black "Give Review" or "Edit Review" button (based on review status)
  - **Pending**: Disabled "Processing" button
  - **Others**: No button

#### Card Layout Updates

**Tracking Row** (new)
- Displays for Shipping and Delivered orders when `tracking_number` is not null
- Located directly below order date
- Contains:
  - Package box icon (LiaBoxSolid)
  - "Tracking: {number}" text
  - Copy button to clipboard (MdOutlineContentCopy)
- Border separator after row

**Action Buttons** (updated)
- Replaced hardcoded review button with `getActionButton(order)` function
- Now renders different buttons based on order status

#### Order Details Modal Updates

**Tracking Information Section** (new)
- Added after "Shipping Address" section
- Only renders when `tracking_number` is not null
- Light purple background (`bg-purple-100`)
- Contains:
  - "Tracking Information" heading
  - Package icon with "Tracking Number" label and value
  - Copy button
- Positioned before "Order Items" section

#### Confirmation Modals (new)

**"Confirm Order Received" Modal**
- Appears when user clicks "Mark as Received" on Shipping orders
- Title: "Confirm Order Received"
- Message: "Has your order arrived correctly and in good condition?"
- Two buttons:
  - "Not Yet" - light outline style, closes modal
  - "Yes, Received" - black solid style, triggers status update

**"Cancel Order" Modal**
- Appears when user clicks "Cancel Order" on Packaging orders
- Title: "Cancel Order"
- Message: "Are you sure you want to cancel this order? This action cannot be undone."
- Two buttons:
  - "Keep Order" - light outline style, closes modal
  - "Yes, Cancel" - red solid style, triggers status update

#### UI Behaviors

**Optimistic Updates**
- Orders immediately update locally when status changes
- No need for page refresh
- Order disappears from one tab and appears in another instantly
- Tab navigation works smoothly after status change

**Review Button Behavior**
- Immediately updates from "Give Review" to "Edit Review" after successful submission
- Uses `allReviewedOrders` state set that tracks which orders have reviews
- Avoids DOM manipulation - uses React state instead

**Status-Specific Logic**
- Correctly shows appropriate button for each order status
- Disables button for Pending orders (still processing)
- Hides review button for non-Delivered orders

### 2. Backend: No Changes Required

All necessary endpoints already exist and function correctly:
- `POST /orders/{id}/finish` - Updates status from "Shipping" to "Delivered"
- `POST /orders/{id}/cancel` - Updates status from "Packaging" to "Cancel", restores stock
- `PUT /ratings/{id}` - Updates existing ratings with timestamp

### 3. Frontend: Status Filter (Previously Fixed)

The status query map now sends **lowercase** status values:
- `pending` (not `Pending`)
- `packaging` (not `Packaging`)
- `shipping` (not `Shipping`)
- `delivered` (not `Delivered`)
- `cancel` (not `Cancel`)

Backend correctly handles lowercase via `strtolower()` and switch statement.

## Testing Checklist

- [ ] All tab (shows all orders regardless of status)
- [ ] Pending tab (shows only Pending orders)
- [ ] Packaging tab (shows only Packaging orders with Cancel button)
- [ ] Shipping tab (shows only Shipping orders with Mark as Received button and tracking)
- [ ] Delivered tab (shows only Delivered orders with Give/Edit Review button and tracking)
- [ ] Cancelled tab (shows only Cancelled orders)

### Tracking Information
- [ ] Tracking row displays on card for Shipping orders
- [ ] Tracking row displays on card for Delivered orders
- [ ] Tracking row NOT shown if tracking_number is null
- [ ] Copy button works and shows toast
- [ ] Tracking Information section shows in modal for orders with tracking_number
- [ ] Copy button works in modal

### Order Status Changes
- [ ] "Mark as Received" button appears on Shipping orders
- [ ] Clicking shows confirmation modal
- [ ] "Yes, Received" updates order to Delivered status
- [ ] Order moves from Shipping tab to Delivered tab
- [ ] Order remains in All tab with new status
- [ ] No page refresh required

- [ ] "Cancel Order" button appears on Packaging orders
- [ ] Clicking shows confirmation modal
- [ ] "Yes, Cancel" updates order to Cancel status
- [ ] Order moves from Packaging tab to Cancelled tab
- [ ] Order remains in All tab with new status
- [ ] Stock is restored in backend
- [ ] No page refresh required

### Review Buttons
- [ ] "Give Review" shows for Delivered orders with no reviews
- [ ] "Edit Review" shows for Delivered orders with reviews
- [ ] Button text updates immediately after submission
- [ ] Review works correctly from Order Details modal

## File Changes Summary

**Modified Files:**
1. `/frontend/web-5scent/app/orders/page.tsx` - Complete orders page with all new features

**Backend Files:**
- No changes required (all endpoints already exist)

## Database Schema Requirements

Verify the following fields exist:
- `orders.tracking_number` (nullable string) - Already exists
- `rating.updated_at` (timestamp) - Already exists
- `orders.status` (enum: Pending, Packaging, Shipping, Delivered, Cancel) - Already exists

## Notes

- All state updates are optimistic for better UX
- Tracking number copy uses browser Clipboard API
- Confirmation modals use semi-transparent overlay (bg-black/30)
- Colors follow existing design system:
  - Pending: Yellow
  - Packaging: Blue
  - Shipping: Purple
  - Delivered: Green
  - Cancel: Red

# QRIS Expiration & Real-Time Notifications Implementation

## Overview
This implementation adds automatic QRIS transaction expiration, prevents re-generation of expired payments, and implements real-time notifications for all payment and order status changes.

## Components Implemented

### 1. Backend - Auto Expiration Job
**File:** `app/Console/Commands/ExpireQrisTransactions.php`

**Purpose:** Runs every minute to automatically expire pending QRIS transactions that have passed their `expired_at` timestamp.

**Key Features:**
- Uses database locks to prevent race conditions
- Sets `status = 'expire'` and `updated_at = expired_at` atomically
- Cancels related orders when payment expires
- Creates non-duplicate notifications for expiry events
- Logs all expiration events for debugging

**Command:** `php artisan qris:expire`

---

### 2. Backend - Enhanced NotificationService
**File:** `app/Services/NotificationService.php`

**Purpose:** Centralized notification creation with built-in de-duplication and broadcasting.

**Key Changes:**
- Added de-duplication logic: prevents duplicate notifications within 5 minutes for the same message/order combination
- Added broadcasting support for real-time updates
- Enhanced `createPaymentNotification()` with user_id resolution
- Enhanced `createOrderUpdateNotification()` with user_id resolution
- All notifications are broadcast via Laravel Broadcasting

**De-duplication Window:** 5 minutes (configurable)

---

### 3. Backend - NotificationCreated Event
**File:** `app/Events/NotificationCreated.php`

**Purpose:** Laravel broadcast event for real-time notification delivery.

**Implementation:**
- Broadcasts to private channel: `user.{user_id}`
- Includes full notification data
- Can be consumed via WebSocket (Pusher) or fallback via polling

---

### 4. Backend - Console Kernel
**File:** `app/Console/Kernel.php`

**Purpose:** Registers the schedule for automatic QRIS expiration.

**Schedule:**
```php
$schedule->command('qris:expire')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

---

### 5. Backend - QrisPaymentController Updates
**File:** `app/Http/Controllers/QrisPaymentController.php`

**Key Changes:**
1. **Check for Existing Pending QRIS** before calling Midtrans:
   - If a pending QRIS exists and hasn't expired, reuse it
   - Returns existing QR code without calling Midtrans API
   - Prevents duplicate Midtrans requests

```php
$existingPendingQris = PaymentTransaction::where('order_id', $order->order_id)
    ->where('status', 'pending')
    ->where(function ($query) {
        $query->whereNull('expired_at')
            ->orWhere('expired_at', '>', now());
    })
    ->first();

if ($existingPendingQris) {
    return response()->json([
        'success' => true,
        'message' => 'QRIS payment already exists',
        'qr_url' => $existingPendingQris->qr_url,
        'expired_at' => $existingPendingQris->expired_at,
    ]);
}
```

---

### 6. Backend - OrderQrisController Updates
**File:** `app/Http/Controllers/OrderQrisController.php`

**Key Changes:**

1. **getQrisDetail()** now returns `effective_status`:
   ```php
   $effectiveStatus = $qrisTransaction->status;
   if ($qrisTransaction->status === 'pending' && $qrisTransaction->expired_at && $qrisTransaction->expired_at <= now()) {
       $effectiveStatus = 'expire';
   }
   ```

2. **getPaymentStatus()** also returns `effective_status` for polling

**Response:**
```json
{
    "success": true,
    "qris": {
        "status": "pending",
        "effective_status": "expire",
        "expired_at": "2025-12-22T10:30:00Z"
    }
}
```

---

### 7. Frontend - Payment Page
**File:** `app/orders/[orderId]/qris/page.tsx`

**Key Changes:**
1. Checks `effective_status` on page load
2. If expired, shows expired UI without calling Midtrans
3. If valid pending exists, reuses it
4. Only creates new QRIS if none exists

**Flow:**
```
Fetch QRIS Detail
  → effective_status == 'expire' → Show Expired UI (no re-generation)
  → status == 'pending' → Reuse existing QR
  → Not found → Create new QRIS
```

---

### 8. Frontend - QrisPaymentClient
**File:** `app/orders/[orderId]/qris/QrisPaymentClient.tsx`

**Key Changes:**
1. Uses `effective_status` in polling (`getPaymentStatus`)
2. Immediately sets `isExpired` when effective_status becomes 'expire'
3. Stops polling on expiry

---

### 9. Frontend - NotificationContext
**File:** `contexts/NotificationContext.tsx`

**Key Changes:**
1. Added broadcast event listener for real-time notifications
2. Automatically adds new notifications to state
3. Increments unread count
4. Fallback to polling if WebSocket not available

**Event Listener:**
```typescript
window.addEventListener('notification:created', (event: CustomEvent) => {
    const newNotification = event.detail;
    setNotifications(prev => [newNotification, ...prev]);
    setUnreadCount(prev => prev + 1);
});
```

---

### 10. Frontend - Orders List
**File:** `app/orders/page.tsx`

**Key Changes:**
For cancelled orders, only show "View Details" button (no secondary actions)

---

## Database Changes
None required! The existing `qris_transactions` table already has the necessary fields.

## Setup Instructions

### 1. Deploy Backend Changes
```bash
# Ensure migrations are run (should already be done)
php artisan migrate

# Create the scheduled task runner
# Add to your cron or task scheduler:
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. (Optional) Set Up Real-Time Notifications
Choose one option:

**Option A: Pusher (Recommended)**
```bash
composer require pusher/pusher-php-server

# Set in .env:
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

**Option B: Laravel WebSockets**
```bash
composer require beyondcode/laravel-websockets
php artisan websockets:serve
```

**Option C: None (Fall back to polling)**
- Frontend polling will automatically detect status changes
- Less real-time but doesn't require additional services

### 3. Deploy Frontend Changes
- All changes are in-code, no environment variables needed
- Frontend will automatically listen for broadcast events if configured
- Falls back to polling if broadcasting is not available

---

## Testing

### Manual Test 1: Automatic Expiration
1. Create a QRIS payment for an order
2. Wait for expiration time (typically 1-2 minutes in sandbox)
3. Run: `php artisan qris:expire`
4. Check database:
   ```sql
   SELECT * FROM qris_transactions WHERE order_id = 79;
   -- Should show: status='expire', updated_at >= expired_at
   ```
5. Check order status:
   ```sql
   SELECT * FROM orders WHERE order_id = 79;
   -- Should show: status='Cancelled'
   ```
6. Check notification:
   ```sql
   SELECT * FROM notifications WHERE order_id = 79 AND notif_type='Payment';
   -- Should show expiry message
   ```

### Manual Test 2: Prevent Re-generation
1. Create QRIS for order
2. Refresh payment page immediately
3. Check logs - should see "Reusing existing pending QRIS"
4. Frontend should display same QR code

### Manual Test 3: No Re-generation on Expiry
1. Create QRIS
2. Wait for expiration
3. Refresh page
4. Should show "Payment Expired" UI
5. Check logs - should NOT see Midtrans API call

### Manual Test 4: Notifications
1. Make any order status change
2. Check notifications list - should appear immediately
3. If WebSocket configured, should appear in real-time
4. Check for duplicates - should not exist

---

## API Changes

### GET /api/orders/{orderId}/qris-detail
**New Response Field:**
```json
{
    "qris": {
        "status": "pending",
        "effective_status": "expire"  // NEW
    }
}
```

### GET /api/orders/{orderId}/payment-status
**New Response Field:**
```json
{
    "qris_status": "pending",
    "effective_status": "expire"  // NEW
}
```

### POST /api/payments/qris
**New Response (existing QRIS case):**
```json
{
    "success": true,
    "message": "QRIS payment already exists",
    "qr_url": "...",
    "expired_at": "...",
    "qris_transaction_id": 123
}
```

---

## Monitoring

### Check Expiration Job Health
```bash
# View recent runs
tail -f storage/logs/laravel.log | grep "QRIS"

# Manual test
php artisan qris:expire

# Schedule list
php artisan schedule:list
```

### Notification Tracking
```sql
-- Recent payment notifications
SELECT * FROM notifications 
WHERE notif_type = 'Payment' 
ORDER BY created_at DESC 
LIMIT 10;

-- Duplicate check (should be empty)
SELECT order_id, message, COUNT(*) 
FROM notifications 
WHERE notif_type IN ('Payment', 'OrderUpdate') 
AND created_at >= NOW() - INTERVAL 5 MINUTE 
GROUP BY order_id, message 
HAVING COUNT(*) > 1;
```

---

## Troubleshooting

### Scheduler Not Running
Check if Laravel scheduler is configured:
```bash
# Add to your cron tab:
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1

# Or test manually:
php artisan qris:expire
```

### QRIS Not Expiring Automatically
1. Check if scheduler is running: `php artisan schedule:list`
2. Check logs: `tail storage/logs/laravel.log`
3. Manual trigger: `php artisan qris:expire`
4. Verify database timezone matches server timezone

### Notifications Not Appearing Real-Time
1. Check if broadcasting is configured: `.env` BROADCAST_DRIVER
2. Frontend falls back to polling automatically
3. Check browser console for errors
4. Verify user is authenticated

### Duplicate Notifications Appearing
Notifications have 5-minute de-duplication window. Check if messages are different or if event fired multiple times.

---

## Performance Considerations

1. **Scheduler Frequency:** Every minute (configurable)
2. **Lock Timeout:** 5 seconds to prevent deadlocks
3. **Notification De-dup:** 5-minute window (prevents spam)
4. **Database Indexes:** Recommended on `qris_transactions(status, expired_at)`

### Recommended Database Indexes
```sql
ALTER TABLE qris_transactions ADD INDEX idx_status_expired (status, expired_at);
ALTER TABLE notifications ADD INDEX idx_order_type_time (order_id, notif_type, created_at);
```

---

## Future Enhancements

1. Webhook notification from Midtrans when QRIS actually expires
2. Email notifications for important payment status changes
3. SMS notifications for critical events
4. Admin dashboard for monitoring QRIS expiration rates
5. Configurable expiration duration per order type

# QRIS Implementation - Test Checklist

## Pre-Deployment Checklist

- [ ] Database migrations are up to date
- [ ] Laravel scheduler is configured in cron
- [ ] All code files are in place
- [ ] Environment variables are set (if using broadcasting)

## Backend Tests

### Test 1: Auto-Expiration Command
```bash
# 1. Create a QRIS payment for an order
# 2. Run the command manually
php artisan qris:expire

# 3. Verify in database
SELECT * FROM qris_transactions WHERE order_id = 79 ORDER BY created_at DESC LIMIT 1;
# Expected: status='expire', updated_at = expired_at
```
**Status:** [ ] Pass [ ] Fail

### Test 2: Related Order Cancellation
```sql
-- After running expiration command for expired payment
SELECT status FROM orders WHERE order_id = 79;
-- Expected: status = 'Cancelled'
```
**Status:** [ ] Pass [ ] Fail

### Test 3: Notification Creation
```sql
-- Check for expiry notification
SELECT * FROM notifications 
WHERE order_id = 79 AND notif_type = 'Payment' 
ORDER BY created_at DESC LIMIT 1;
-- Expected: Message about payment expiration
```
**Status:** [ ] Pass [ ] Fail

### Test 4: De-duplication
```bash
# 1. Create payment
# 2. Manually run expiration twice in quick succession
php artisan qris:expire
php artisan qris:expire

# 3. Check notifications
SELECT COUNT(*) FROM notifications 
WHERE order_id = 79 AND notif_type = 'Payment' 
AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE);
-- Expected: Should be 1 notification, not 2
```
**Status:** [ ] Pass [ ] Fail

### Test 5: Prevent QRIS Re-generation
```bash
# 1. Create fresh QRIS payment via API POST /api/payments/qris
# 2. Call same endpoint again immediately (within expiry)
# 3. Check Laravel logs
grep -i "reusing" storage/logs/laravel.log | tail -5
# Expected: Should see "Reusing existing pending QRIS"
```
**Status:** [ ] Pass [ ] Fail

### Test 6: Scheduler Registration
```bash
php artisan schedule:list
# Expected: qris:expire command listed with "Every minute"
```
**Status:** [ ] Pass [ ] Fail

---

## Frontend Tests

### Test 7: Payment Page - Fresh QRIS
1. Navigate to `/orders/[orderId]/qris`
2. Should see QR code displayed
3. Timer should count down from ~2 minutes (Sandbox limitation)
4. No errors in browser console
**Status:** [ ] Pass [ ] Fail

### Test 8: Payment Page - Reuse Existing QRIS
1. Navigate to payment page
2. Refresh page immediately (before expiry)
3. Should see same QR code
4. Check Network tab - should NOT see POST to /api/payments/qris (or should see 200 with "already exists")
**Status:** [ ] Pass [ ] Fail

### Test 9: Payment Page - Expired QRIS
1. Navigate to payment page
2. Wait for expiry (or manually run `php artisan qris:expire`)
3. Refresh page
4. Should see "Payment Expired" message
5. Should NOT see QR code or new payment attempt
6. No error toasts
**Status:** [ ] Pass [ ] Fail

### Test 10: Effective Status on Refresh
1. Create QRIS payment
2. While counting down, inspect API response: GET /api/orders/{orderId}/qris-detail
3. Should return `effective_status` field
4. If `expired_at` <= now(), `effective_status` should be 'expire'
**Status:** [ ] Pass [ ] Fail

### Test 11: Orders List - Cancelled Order Display
1. Go to `/orders` page
2. Find a cancelled order (or manually set one via SQL)
3. Should show "View Details" button only
4. Should NOT show secondary action buttons (Cancel, Mark as Received, Review, etc.)
**Status:** [ ] Pass [ ] Fail

### Test 12: Notifications Display
1. Make an order status change (e.g., admin changes to Packaging)
2. Check notification sidebar on customer account
3. New notification should appear
4. Count should increment
5. No duplicate notifications for same event
**Status:** [ ] Pass [ ] Fail

### Test 13: Real-Time Notification (if Broadcasting configured)
1. Open customer account in 2 browser tabs
2. In admin: change order status
3. Both tabs should show notification immediately (no refresh needed)
4. If WebSocket not configured, should fall back to polling
**Status:** [ ] Pass [ ] Fail

---

## Edge Cases

### Test 14: Multiple Expired QRIS Transactions
```bash
# Setup: Create 5 orders with expired QRIS
# Run: php artisan qris:expire
# Verify: All 5 are marked as expire and orders are cancelled
# Count: 5 orders should have Cancelled status
```
**Status:** [ ] Pass [ ] Fail

### Test 15: QRIS Expiry During Payment
1. Start QRIS payment
2. While timer counting, simulate manual expiration: `php artisan qris:expire`
3. Check polling response
4. Should show `effective_status = 'expire'`
5. Frontend should detect and show expired message
**Status:** [ ] Pass [ ] Fail

### Test 16: Timezone Handling
```sql
-- Check server time matches expectations
SELECT NOW(), NOW() AT TIME ZONE 'Asia/Jakarta' as jakarta_time;

-- Create QRIS and verify expired_at is set correctly
SELECT order_id, expired_at, NOW(), 
       IF(expired_at <= NOW(), 'SHOULD_EXPIRE', 'STILL_VALID') as status
FROM qris_transactions 
ORDER BY created_at DESC LIMIT 5;
```
**Status:** [ ] Pass [ ] Fail

### Test 17: Concurrent QRIS Requests
1. Open payment page in 2 browser tabs for same order
2. Both tabs simultaneously POST /api/payments/qris
3. Should result in only 1 QRIS transaction created
4. Both responses should show same QR code
**Status:** [ ] Pass [ ] Fail

---

## Performance & Monitoring

### Test 18: Scheduler Performance
```bash
# Monitor execution time
time php artisan qris:expire

# Check if overlapping runs are prevented
ps aux | grep "artisan qris:expire"
# Expected: Should see --without-overlapping flag preventing duplicates
```
**Status:** [ ] Pass [ ] Fail

### Test 19: Database Indexes
```sql
-- Check if recommended indexes exist
SHOW INDEX FROM qris_transactions WHERE Key_name LIKE '%status%expired%';
SHOW INDEX FROM notifications WHERE Key_name LIKE '%order%type%';
-- Expected: Indexes should exist
```
**Status:** [ ] Pass [ ] Fail

### Test 20: Log Analysis
```bash
# Check logs for errors or warnings
grep -i "error\|warning" storage/logs/laravel.log | grep -i "qris\|notification" | tail -20
# Expected: Should be minimal errors
```
**Status:** [ ] Pass [ ] Fail

---

## Rollback Plan

If issues found:

1. **Stop Scheduler**: Remove from cron, comment out in Kernel.php
2. **Restore Payment Logic**: Revert QrisPaymentController.php changes
3. **Notify Customers**: About any expired orders
4. **Investigate**: Check logs and database for root cause
5. **Re-Deploy**: Once issues are fixed

---

## Sign-Off

- [ ] All backend tests passed
- [ ] All frontend tests passed
- [ ] Edge cases handled correctly
- [ ] No performance degradation
- [ ] Ready for production deployment

**Tested By:** ___________________  
**Date:** ___________________  
**Notes:** ___________________


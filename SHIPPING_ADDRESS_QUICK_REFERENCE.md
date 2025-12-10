# Quick Reference: Shipping Address Refactoring

## What Changed

The shipping address storage has been refactored from a single concatenated string to 5 separate database columns:

| Old | New |
|-----|-----|
| `orders.shipping_address` = "Jl. Anta, Antapanoy, Bandung, Jawa Barat 12345" | `orders.address_line` = "Jl. Anta" |
| | `orders.district` = "Antapanoy" |
| | `orders.city` = "Bandung" |
| | `orders.province` = "Jawa Barat" |
| | `orders.postal_code` = "12345" |

## API Changes

### Checkout POST /orders

**Before:**
```json
{
  "cart_ids": [1, 2, 3],
  "shipping_address": "Jl. Anta, Antapanoy, Bandung, Jawa Barat 12345",
  "payment_method": "QRIS"
}
```

**After:**
```json
{
  "cart_ids": [1, 2, 3],
  "address_line": "Jl. Anta",
  "district": "Antapanoy",
  "city": "Bandung",
  "province": "Jawa Barat",
  "postal_code": "12345",
  "payment_method": "QRIS"
}
```

### Order Response (GET /orders, GET /admin/dashboard/orders)

**Before:**
```json
{
  "order_id": 1,
  "shipping_address": "Jl. Anta, Antapanoy, Bandung, Jawa Barat 12345",
  "user": { /* user profile address */ }
}
```

**After:**
```json
{
  "order_id": 1,
  "address_line": "Jl. Anta",
  "district": "Antapanoy",
  "city": "Bandung",
  "province": "Jawa Barat",
  "postal_code": "12345",
  "user": { /* user name, email, phone only */ }
}
```

## Files Modified

### Backend
- ✅ `database/migrations/2025_12_10_refactor_orders_address_columns.php` - CREATED & EXECUTED
- ✅ `app/Models/Order.php` - Updated $fillable array
- ✅ `app/Http/Controllers/OrderController.php` - Updated store() method validation and creation
- ✅ `app/Http/Controllers/DashboardController.php` - No changes needed (already uses select('*'))

### Frontend
- ✅ `app/checkout/page.tsx` - Updated API payload to send 5 fields
- ✅ `app/orders/page.tsx` - Updated OrderData interface and address display
- ✅ `app/admin/orders/page.tsx` - Updated Order interface and address display

## Testing Instructions

### 1. Verify Migration
```bash
# Check database schema
# The orders table should have 5 new columns: address_line, district, city, province, postal_code
# The shipping_address column should be deleted

# In MySQL:
DESCRIBE orders;
```

### 2. Test New Order Creation
1. Login as a customer
2. Add items to cart
3. Proceed to checkout
4. Enter address information (address line, district, city, province, postal code)
5. Place order with any payment method
6. **Verify:** In database, order should have data in the 5 address columns

### 3. Test Customer Order History
1. Login as a customer with existing orders
2. Go to Orders page
3. Click on an order to view details
4. **Verify:** Shipping address should display correctly from the order record
5. **Expected:** Should show the address used at checkout time, not current profile address

### 4. Test Admin Order Management
1. Login as admin
2. Go to Admin → Orders
3. View order in list
4. **Verify:** Address displays correctly in order card
5. Click to open order detail modal
6. **Verify:** Shipping address section shows all 5 address components correctly

### 5. Test with Changed Profile Address
1. Create order with Address A
2. Change user profile to Address B
3. View order history
4. **Expected:** Order should still show Address A (original shipping address)
5. **Not:** Current profile Address B

### 6. Test Migration Rollback (if needed)
```bash
php artisan migrate:rollback --path=database/migrations/2025_12_10_refactor_orders_address_columns.php --force
```
Should safely restore original schema with data intact.

## Frontend Components Using Address

### Checkout Page
- Form fields: addressLine, district, city, province, postalCode
- "Use My Data" button loads these from user profile
- Sends 5 separate fields to /orders endpoint

### Order History Modal (Customer)
- Displays address from `order.address_line`, `order.district`, etc.
- NOT from `user` profile

### Admin Order List & Detail Modal
- Displays address from `order.address_line`, `order.district`, etc.
- NOT from `user` profile (which now only has name, phone, email)

## Backward Compatibility

⚠️ **Breaking Changes:**
- Old code expecting `shipping_address` on order objects will fail
- Old API requests sending `shipping_address` string will fail
- Old database queries on `shipping_address` column will fail

✅ **Non-Breaking:**
- User profile address fields (address_line, district, city, province, postal_code) unchanged
- "Use My Data" functionality still works
- All order data preserved during migration

## Emergency Rollback

If issues arise, rollback is safe:
```bash
php artisan migrate:rollback --path=database/migrations/2025_12_10_refactor_orders_address_columns.php --force
```

Then revert code changes to use old API contract. Migration has full rollback logic implemented.

---

**Status:** ✅ Migration executed successfully | ⏳ Awaiting testing

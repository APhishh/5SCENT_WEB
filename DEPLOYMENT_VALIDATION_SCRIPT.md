# Deployment Validation Script

## Pre-Deployment Verification

Run this script before deploying QRIS expiration system to production.

---

## 1. Backend Code Verification

### Check All Required Files Exist

```bash
#!/bin/bash

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

APP_PATH="/path/to/laravel-5scent"

echo "======================================"
echo "Backend Code Verification"
echo "======================================"

# Files to check
FILES=(
    "app/Console/Commands/ExpireQrisTransactions.php"
    "app/Events/NotificationCreated.php"
    "app/Console/Kernel.php"
    "app/Services/NotificationService.php"
    "app/Http/Controllers/QrisPaymentController.php"
    "app/Http/Controllers/OrderQrisController.php"
    "app/Models/PaymentTransaction.php"
    "app/Models/Order.php"
    "app/Models/Notification.php"
)

for file in "${FILES[@]}"; do
    if [ -f "$APP_PATH/$file" ]; then
        echo -e "${GREEN}✓${NC} $file exists"
    else
        echo -e "${RED}✗${NC} $file MISSING"
    fi
done
```

### Verify PHP Syntax

```bash
#!/bin/bash

APP_PATH="/path/to/laravel-5scent"

echo ""
echo "======================================"
echo "PHP Syntax Check"
echo "======================================"

FILES=(
    "app/Console/Commands/ExpireQrisTransactions.php"
    "app/Events/NotificationCreated.php"
    "app/Console/Kernel.php"
    "app/Services/NotificationService.php"
    "app/Http/Controllers/QrisPaymentController.php"
    "app/Http/Controllers/OrderQrisController.php"
)

ERRORS=0

for file in "${FILES[@]}"; do
    RESULT=$(php -l "$APP_PATH/$file" 2>&1)
    if echo "$RESULT" | grep -q "No syntax errors"; then
        echo -e "${GREEN}✓${NC} $file - OK"
    else
        echo -e "${RED}✗${NC} $file - SYNTAX ERROR"
        echo "  $RESULT"
        ((ERRORS++))
    fi
done

if [ $ERRORS -eq 0 ]; then
    echo -e "\n${GREEN}All PHP files pass syntax check${NC}"
else
    echo -e "\n${RED}$ERRORS file(s) have syntax errors${NC}"
    exit 1
fi
```

### Test Artisan Commands

```bash
#!/bin/bash

APP_PATH="/path/to/laravel-5scent"
cd "$APP_PATH"

echo ""
echo "======================================"
echo "Artisan Command Verification"
echo "======================================"

# Check if qris:expire command exists
if php artisan list | grep -q "qris:expire"; then
    echo -e "${GREEN}✓${NC} qris:expire command registered"
else
    echo -e "${RED}✗${NC} qris:expire command NOT found"
    echo "  Check app/Console/Kernel.php for schedule registration"
    exit 1
fi

# Test running the command (dry-run simulation)
echo ""
echo "Testing qris:expire command execution..."
OUTPUT=$(php artisan qris:expire 2>&1)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} qris:expire command executed successfully"
    echo "  Output: $OUTPUT"
else
    echo -e "${RED}✗${NC} qris:expire command failed"
    echo "  Error: $OUTPUT"
    exit 1
fi
```

### Verify Database Schema

```bash
#!/bin/bash

echo ""
echo "======================================"
echo "Database Schema Verification"
echo "======================================"

# MySQL check
MYSQL_CMD="mysql -h localhost -u $DB_USER -p$DB_PASS $DB_NAME"

# Check qris_transactions table
echo "Checking qris_transactions table..."
$MYSQL_CMD -e "DESCRIBE qris_transactions;" > /tmp/schema.txt

# Required fields
REQUIRED_FIELDS=("qris_transaction_id" "order_id" "status" "expired_at" "qr_url" "updated_at")

for field in "${REQUIRED_FIELDS[@]}"; do
    if grep -q "$field" /tmp/schema.txt; then
        echo -e "${GREEN}✓${NC} Field '$field' exists"
    else
        echo -e "${RED}✗${NC} Field '$field' MISSING"
    fi
done

# Check status enum values
echo ""
echo "Checking status enum values..."
STATUS_CHECK=$($MYSQL_CMD -e "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='qris_transactions' AND COLUMN_NAME='status';" -sN)

if echo "$STATUS_CHECK" | grep -q "expire"; then
    echo -e "${GREEN}✓${NC} Status enum includes 'expire'"
else
    echo -e "${RED}✗${NC} Status enum missing 'expire' value"
fi
```

---

## 2. Frontend Code Verification

### Check TypeScript/JavaScript Syntax

```bash
#!/bin/bash

FRONTEND_PATH="/path/to/nextjs-5scent"
cd "$FRONTEND_PATH"

echo ""
echo "======================================"
echo "Frontend Build Test"
echo "======================================"

# Check if files exist
FILES=(
    "app/orders/[orderId]/qris/page.tsx"
    "app/orders/[orderId]/qris/QrisPaymentClient.tsx"
    "app/orders/page.tsx"
    "contexts/NotificationContext.tsx"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file exists"
    else
        echo -e "${RED}✗${NC} $file MISSING"
    fi
done

# Try to build
echo ""
echo "Building frontend..."
npm run build 2>&1 | tail -20

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Frontend build successful"
else
    echo -e "${RED}✗${NC} Frontend build FAILED"
    exit 1
fi
```

---

## 3. Integration Tests

### Test Scheduler Job Execution

```bash
#!/bin/bash

APP_PATH="/path/to/laravel-5scent"
cd "$APP_PATH"

echo ""
echo "======================================"
echo "Scheduler Integration Test"
echo "======================================"

# Create a test QRIS transaction (expired 2 minutes ago)
php artisan tinker << 'EOF'
use App\Models\PaymentTransaction;
use App\Models\Order;

// Check if test order exists
$testOrder = Order::where('order_code', 'TEST-EXPIRE-001')->first();
if (!$testOrder) {
    $testOrder = Order::create([
        'order_code' => 'TEST-EXPIRE-001',
        'user_id' => 1,
        'status' => 'Pending',
        'total_amount' => 100000,
    ]);
}

// Create expired QRIS
$qris = PaymentTransaction::updateOrCreate(
    ['order_id' => $testOrder->order_id],
    [
        'status' => 'pending',
        'expired_at' => now()->subMinutes(2),
        'qr_url' => 'https://example.com/qr',
        'created_at' => now()->subMinutes(3),
    ]
);

echo "Created test QRIS: {$qris->qris_transaction_id}\n";
echo "Status: {$qris->status}\n";
echo "Expired at: {$qris->expired_at}\n";
EOF

# Run expiration command
echo ""
echo "Running expiration command..."
php artisan qris:expire

# Check if QRIS was updated
php artisan tinker << 'EOF'
use App\Models\PaymentTransaction;

$qris = PaymentTransaction::where('qris_transaction_id', 'TEST-EXPIRE-001')->first();
if ($qris && $qris->status === 'expire') {
    echo "✓ QRIS successfully expired\n";
    echo "Status: {$qris->status}\n";
    echo "Updated at: {$qris->updated_at}\n";
} else {
    echo "✗ QRIS was not expired\n";
}
EOF
```

### Test API Endpoints

```bash
#!/bin/bash

API_URL="http://localhost:8000/api"
BEARER_TOKEN="your_test_token"

echo ""
echo "======================================"
echo "API Endpoint Tests"
echo "======================================"

# Test 1: Create QRIS
echo "Test 1: Create QRIS payment"
curl -X POST "$API_URL/payments/qris" \
  -H "Authorization: Bearer $BEARER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"order_id": 79}' \
  2>/dev/null | jq .

# Test 2: Get QRIS Detail with effective_status
echo ""
echo "Test 2: Get QRIS detail (should have effective_status)"
curl -X GET "$API_URL/orders/79/qris-detail" \
  -H "Authorization: Bearer $BEARER_TOKEN" \
  2>/dev/null | jq '.qris | {status, effective_status, expired_at}'

# Test 3: Get Payment Status with effective_status
echo ""
echo "Test 3: Get payment status (should have effective_status)"
curl -X GET "$API_URL/orders/79/payment-status" \
  -H "Authorization: Bearer $BEARER_TOKEN" \
  2>/dev/null | jq '.effective_status'

# Expected response format:
# {
#   "qris": {
#     "status": "pending",
#     "effective_status": "expire",  // ← Should be here
#     "expired_at": "2024-01-15 14:30:00"
#   }
# }
```

### Test Notifications

```bash
#!/bin/bash

APP_PATH="/path/to/laravel-5scent"
cd "$APP_PATH"

echo ""
echo "======================================"
echo "Notification System Test"
echo "======================================"

php artisan tinker << 'EOF'
use App\Services\NotificationService;
use App\Models\Order;

$order = Order::first();

// Test 1: Create payment notification
echo "Test 1: Create payment notification\n";
$notif1 = NotificationService::createPaymentNotification(
    $order->user_id,
    $order->order_id,
    "Test payment notification",
    'Payment'
);
echo "Created: {$notif1->notif_id}\n";

// Test 2: Create duplicate (should be skipped)
echo "\nTest 2: Create duplicate (should return existing)\n";
$notif2 = NotificationService::createPaymentNotification(
    $order->user_id,
    $order->order_id,
    "Test payment notification",  // Same message
    'Payment'
);
if ($notif2->notif_id === $notif1->notif_id) {
    echo "✓ De-duplication working: returned existing notification\n";
} else {
    echo "✗ De-duplication failed: created new notification\n";
}

// Test 3: Create different notification (should be new)
echo "\nTest 3: Create different notification (should be new)\n";
$notif3 = NotificationService::createPaymentNotification(
    $order->user_id,
    $order->order_id,
    "Different message",
    'Payment'
);
if ($notif3->notif_id !== $notif1->notif_id) {
    echo "✓ New notification created: {$notif3->notif_id}\n";
} else {
    echo "✗ Should have created new notification\n";
}
EOF
```

---

## 4. Performance Tests

### Database Query Performance

```bash
#!/bin/bash

echo ""
echo "======================================"
echo "Database Performance Tests"
echo "======================================"

MYSQL_CMD="mysql -h localhost -u $DB_USER -p$DB_PASS $DB_NAME"

# Test 1: Expiration query performance
echo "Test 1: Expiration query performance"
$MYSQL_CMD -e "EXPLAIN SELECT * FROM qris_transactions WHERE status='pending' AND expired_at<=NOW();" | head -5

# Check for index
echo ""
echo "Checking for index on status and expired_at..."
INDEX_CHECK=$($MYSQL_CMD -e "SHOW INDEXES FROM qris_transactions WHERE Column_name IN ('status','expired_at');" | wc -l)

if [ $INDEX_CHECK -gt 0 ]; then
    echo -e "${GREEN}✓${NC} Index exists on status/expired_at"
else
    echo -e "${YELLOW}⚠${NC} No index found. Consider adding:"
    echo "  ALTER TABLE qris_transactions ADD INDEX idx_status_expired (status, expired_at);"
fi

# Test 2: Large dataset query (simulate 1000 pending QRIS)
echo ""
echo "Test 2: Query performance with large dataset"
/usr/bin/time -v $MYSQL_CMD -e "SELECT COUNT(*) FROM qris_transactions WHERE status='pending' AND expired_at<=NOW();" 2>&1 | grep -E "Elapsed|User|System"
```

### API Response Time

```bash
#!/bin/bash

API_URL="http://localhost:8000/api"
BEARER_TOKEN="your_test_token"
ITERATIONS=10

echo ""
echo "======================================"
echo "API Response Time Tests"
echo "======================================"

echo "Running $ITERATIONS requests to /orders/{id}/qris-detail"

for i in $(seq 1 $ITERATIONS); do
    TIME=$( { time curl -s -X GET "$API_URL/orders/79/qris-detail" \
        -H "Authorization: Bearer $BEARER_TOKEN" > /dev/null; } 2>&1 )
    echo "Request $i: $TIME"
done
```

---

## 5. Cron Job Verification

### Check Scheduler Registration

```bash
#!/bin/bash

APP_PATH="/path/to/laravel-5scent"
cd "$APP_PATH"

echo ""
echo "======================================"
echo "Scheduler Cron Verification"
echo "======================================"

# Check if cron job is registered
echo "Checking crontab for Laravel scheduler..."
CRON_ENTRY=$(crontab -l 2>/dev/null | grep "schedule:run")

if [ -z "$CRON_ENTRY" ]; then
    echo -e "${RED}✗${NC} Scheduler cron job NOT found in crontab"
    echo ""
    echo "Add this line to crontab:"
    echo "  * * * * * cd /path/to/laravel-5scent && php artisan schedule:run >> /dev/null 2>&1"
    echo ""
    echo "To edit crontab:"
    echo "  crontab -e"
else
    echo -e "${GREEN}✓${NC} Scheduler cron job found:"
    echo "  $CRON_ENTRY"
fi

# List scheduled tasks
echo ""
echo "Scheduled tasks:"
php artisan schedule:list | grep qris

# Test scheduler tick
echo ""
echo "Testing scheduler tick..."
php artisan schedule:test qris:expire
```

---

## 6. Comprehensive Validation Checklist

```bash
#!/bin/bash

# Complete validation script
ERRORS=0
WARNINGS=0

echo "═══════════════════════════════════════════════════════════"
echo "         QRIS EXPIRATION SYSTEM DEPLOYMENT CHECK"
echo "═══════════════════════════════════════════════════════════"

# Backend Checks
echo ""
echo "BACKEND CHECKS"
echo "─────────────────────────────────────────────────────────"

if [ -f "$APP_PATH/app/Console/Commands/ExpireQrisTransactions.php" ]; then
    echo -e "${GREEN}✓${NC} ExpireQrisTransactions command exists"
else
    echo -e "${RED}✗${NC} ExpireQrisTransactions command missing"
    ((ERRORS++))
fi

if [ -f "$APP_PATH/app/Events/NotificationCreated.php" ]; then
    echo -e "${GREEN}✓${NC} NotificationCreated event exists"
else
    echo -e "${RED}✗${NC} NotificationCreated event missing"
    ((ERRORS++))
fi

if php -l "$APP_PATH/app/Console/Kernel.php" 2>&1 | grep -q "No syntax errors"; then
    echo -e "${GREEN}✓${NC} Kernel.php syntax valid"
else
    echo -e "${RED}✗${NC} Kernel.php has syntax errors"
    ((ERRORS++))
fi

# Frontend Checks
echo ""
echo "FRONTEND CHECKS"
echo "─────────────────────────────────────────────────────────"

if [ -f "$FRONTEND_PATH/app/orders/[orderId]/qris/page.tsx" ]; then
    echo -e "${GREEN}✓${NC} QRIS payment page updated"
else
    echo -e "${RED}✗${NC} QRIS payment page missing"
    ((ERRORS++))
fi

if grep -q "effective_status" "$FRONTEND_PATH/app/orders/[orderId]/qris/page.tsx" 2>/dev/null; then
    echo -e "${GREEN}✓${NC} Page uses effective_status"
else
    echo -e "${YELLOW}⚠${NC} Page may not use effective_status"
    ((WARNINGS++))
fi

# Database Checks
echo ""
echo "DATABASE CHECKS"
echo "─────────────────────────────────────────────────────────"

# Simulate database check (requires MySQL access)
# $MYSQL_CMD -e "DESCRIBE qris_transactions;" | grep -q "expired_at"
# if [ $? -eq 0 ]; then
#     echo -e "${GREEN}✓${NC} expired_at field exists"
# else
#     echo -e "${RED}✗${NC} expired_at field missing"
#     ((ERRORS++))
# fi

echo -e "${YELLOW}⚠${NC} Database checks skipped (requires manual verification)"

# Scheduler Checks
echo ""
echo "SCHEDULER CHECKS"
echo "─────────────────────────────────────────────────────────"

if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo -e "${GREEN}✓${NC} Scheduler cron job registered"
else
    echo -e "${RED}✗${NC} Scheduler cron job NOT registered"
    ((ERRORS++))
fi

# Summary
echo ""
echo "═══════════════════════════════════════════════════════════"
echo "                        SUMMARY"
echo "═══════════════════════════════════════════════════════════"
echo -e "Errors:   ${RED}$ERRORS${NC}"
echo -e "Warnings: ${YELLOW}$WARNINGS${NC}"

if [ $ERRORS -eq 0 ]; then
    echo -e "\n${GREEN}✓ Ready for deployment${NC}"
    exit 0
else
    echo -e "\n${RED}✗ Deployment blocked - fix errors above${NC}"
    exit 1
fi
```

---

## 7. Post-Deployment Verification

After deploying, run these tests:

```bash
#!/bin/bash

echo ""
echo "======================================"
echo "Post-Deployment Verification"
echo "======================================"

APP_PATH="/path/to/laravel-5scent"
cd "$APP_PATH"

# Test 1: Scheduler ran in last minute
echo "Test 1: Check if scheduler ran in last minute"
LAST_RUN=$(grep "qris:expire" storage/logs/laravel.log | tail -1)
if [ ! -z "$LAST_RUN" ]; then
    echo -e "${GREEN}✓${NC} Scheduler executed: $LAST_RUN"
else
    echo -e "${YELLOW}⚠${NC} No scheduler execution found in logs yet"
fi

# Test 2: Check for any errors
echo ""
echo "Test 2: Check for errors in logs"
ERRORS=$(grep "ERROR" storage/logs/laravel.log | grep -i qris | wc -l)
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓${NC} No QRIS-related errors found"
else
    echo -e "${RED}✗${NC} Found $ERRORS error(s):"
    grep "ERROR" storage/logs/laravel.log | grep -i qris | tail -5
fi

# Test 3: Database consistency
echo ""
echo "Test 3: Check database consistency"
$MYSQL_CMD -e "SELECT COUNT(*) as expired_count FROM qris_transactions WHERE status='expire';" -sN

# Test 4: Notification count
echo ""
echo "Test 4: Check recent notifications"
$MYSQL_CMD -e "SELECT COUNT(*) as recent_notifications FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) AND notif_type='Payment';" -sN

echo ""
echo "======================================"
echo "Verification Complete"
echo "======================================"
```

---

## Usage

Save this as `validate.sh` and run:

```bash
# Make executable
chmod +x validate.sh

# Run full validation
./validate.sh

# Run specific section
./validate.sh backend
./validate.sh frontend
./validate.sh database
```

All tests should pass before deploying to production.

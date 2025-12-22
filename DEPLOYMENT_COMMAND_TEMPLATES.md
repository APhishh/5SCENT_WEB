# QRIS Deployment - Command Templates

This document contains ready-to-use command templates for deploying the QRIS expiration system.

---

## 📋 Pre-Deployment Commands

### 1. Backup Database
```bash
# Full backup
mysqldump -h localhost -u $DB_USER -p $DB_NAME > backup_qris_$(date +%Y%m%d_%H%M%S).sql

# Or using Laravel
php artisan db:backup --filename=qris_backup_$(date +%Y%m%d_%H%M%S)
```

### 2. Check Current Status
```bash
# Check if cron is running
crontab -l | grep "schedule:run"

# Check database connection
php artisan tinker
# Inside tinker: DB::connection()->getPdo();

# Check Laravel version
php artisan --version

# Check PHP version
php --version
```

### 3. Verify Files Exist
```bash
# Backend files
ls -la app/Console/Commands/
ls -la app/Events/
ls -la app/Http/Controllers/QrisPaymentController.php
ls -la app/Services/NotificationService.php

# Frontend files
ls -la app/orders/page.tsx
ls -la app/orders/[orderId]/qris/page.tsx
ls -la contexts/NotificationContext.tsx
```

### 4. Run Syntax Check
```bash
# Check PHP files
php -l app/Console/Commands/ExpireQrisTransactions.php
php -l app/Events/NotificationCreated.php
php -l app/Console/Kernel.php

# Run all checks
php artisan lint
```

---

## 🚀 Deployment Commands

### Step 1: Copy New Backend Files

```bash
# Define paths
LARAVEL_PATH="/path/to/laravel-5scent"
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)

# Create backup
mkdir -p backups/$BACKUP_DATE
cp -r $LARAVEL_PATH/app/Console backups/$BACKUP_DATE/
cp -r $LARAVEL_PATH/app/Events backups/$BACKUP_DATE/
cp -r $LARAVEL_PATH/app/Services backups/$BACKUP_DATE/
cp -r $LARAVEL_PATH/app/Http/Controllers backups/$BACKUP_DATE/

# Copy new files
cp ExpireQrisTransactions.php $LARAVEL_PATH/app/Console/Commands/
cp NotificationCreated.php $LARAVEL_PATH/app/Events/
cp Kernel.php $LARAVEL_PATH/app/Console/

# Verify
ls -la $LARAVEL_PATH/app/Console/Commands/ExpireQrisTransactions.php
ls -la $LARAVEL_PATH/app/Events/NotificationCreated.php
ls -la $LARAVEL_PATH/app/Console/Kernel.php
```

### Step 2: Update Existing Backend Files

```bash
# These need manual review before applying
# See QRIS_CHANGES_SUMMARY.md for exact changes

# Files to update:
# 1. app/Http/Controllers/QrisPaymentController.php
# 2. app/Http/Controllers/OrderQrisController.php
# 3. app/Services/NotificationService.php

# Apply changes (manual or via patch)
# After applying, verify syntax:
php -l $LARAVEL_PATH/app/Http/Controllers/QrisPaymentController.php
php -l $LARAVEL_PATH/app/Http/Controllers/OrderQrisController.php
php -l $LARAVEL_PATH/app/Services/NotificationService.php
```

### Step 3: Update Frontend Files

```bash
# Define path
NEXTJS_PATH="/path/to/nextjs-5scent"

# Backup
mkdir -p backups/$BACKUP_DATE/frontend
cp -r $NEXTJS_PATH/app backups/$BACKUP_DATE/frontend/
cp -r $NEXTJS_PATH/contexts backups/$BACKUP_DATE/frontend/

# Copy updated files
cp qris-page.tsx $NEXTJS_PATH/app/orders/[orderId]/qris/page.tsx
cp QrisPaymentClient.tsx $NEXTJS_PATH/app/orders/[orderId]/qris/QrisPaymentClient.tsx
cp orders-page.tsx $NEXTJS_PATH/app/orders/page.tsx
cp NotificationContext.tsx $NEXTJS_PATH/contexts/NotificationContext.tsx

# Verify files
ls -la $NEXTJS_PATH/app/orders/[orderId]/qris/page.tsx
ls -la $NEXTJS_PATH/app/orders/[orderId]/qris/QrisPaymentClient.tsx
ls -la $NEXTJS_PATH/app/orders/page.tsx
ls -la $NEXTJS_PATH/contexts/NotificationContext.tsx
```

### Step 4: Clear Cache & Compile

```bash
cd /path/to/laravel-5scent

# Laravel cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check for errors
php artisan list | grep qris

# Should output:
# qris:expire                       Run QRIS expiration job
```

### Step 5: Configure Scheduler

```bash
# Check if already in crontab
crontab -l | grep "schedule:run"

# If not present, add it:
# Option 1: Manual (safe)
crontab -e
# Add line: * * * * * cd /path/to/laravel-5scent && php artisan schedule:run >> /dev/null 2>&1

# Option 2: Automated (for development)
(crontab -l 2>/dev/null; echo "* * * * * cd /path/to/laravel-5scent && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Verify it's added
crontab -l | grep "schedule:run"
```

### Step 6: Build Frontend

```bash
cd /path/to/nextjs-5scent

# Install dependencies (if needed)
npm install

# Build
npm run build

# Check for errors
echo "Build exit code: $?"

# Start development server (for testing)
# npm run dev
```

---

## ✅ Post-Deployment Verification

### Step 1: Test Scheduler

```bash
# List all scheduled tasks
php artisan schedule:list

# Should show:
# qris:expire                                   Every minute

# Test the command directly
php artisan qris:expire

# Check output
echo "Scheduler test completed"
```

### Step 2: Test API Endpoints

```bash
# Replace with actual values
API_URL="http://localhost:8000/api"
ORDER_ID="79"
BEARER_TOKEN="your_token_here"

# Test 1: Get QRIS detail
echo "Test 1: Get QRIS detail"
curl -s -X GET "$API_URL/orders/$ORDER_ID/qris-detail" \
  -H "Authorization: Bearer $BEARER_TOKEN" | jq '.qris.effective_status'

# Test 2: Get payment status
echo -e "\nTest 2: Get payment status"
curl -s -X GET "$API_URL/orders/$ORDER_ID/payment-status" \
  -H "Authorization: Bearer $BEARER_TOKEN" | jq '.effective_status'

# Test 3: Create QRIS
echo -e "\nTest 3: Create QRIS"
curl -s -X POST "$API_URL/payments/qris" \
  -H "Authorization: Bearer $BEARER_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"order_id\": $ORDER_ID}" | jq '.qr_url'
```

### Step 3: Database Verification

```bash
# Check schema
mysql -h localhost -u $DB_USER -p $DB_NAME << EOF
-- Verify tables exist
SHOW TABLES LIKE 'qris_transactions';
SHOW TABLES LIKE 'notifications';

-- Check columns
DESCRIBE qris_transactions;

-- Check status enum
SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='qris_transactions' AND COLUMN_NAME='status';

-- Check for indexes
SHOW INDEXES FROM qris_transactions WHERE Column_name IN ('status','expired_at');
EOF
```

### Step 4: Monitor Logs

```bash
# Tail logs for QRIS-related messages
tail -f storage/logs/laravel.log | grep -i qris

# Or in separate terminal, search for errors
tail -f storage/logs/laravel.log | grep "ERROR\|FAILED"

# Check for successful executions
grep "Expired.*QRIS" storage/logs/laravel.log | tail -5
```

### Step 5: Manual Test Job Execution

```bash
# Create a test QRIS that's already expired
php artisan tinker << 'EOF'
use App\Models\PaymentTransaction;
use App\Models\Order;

// Create test order
$order = Order::create([
    'order_code' => 'TEST-EXPIRE-' . time(),
    'user_id' => 1,
    'status' => 'Pending',
    'total_amount' => 50000,
]);

// Create expired QRIS
$qris = PaymentTransaction::create([
    'order_id' => $order->order_id,
    'status' => 'pending',
    'expired_at' => now()->subMinutes(5),  // Already expired
    'qr_url' => 'https://test.example.com/qr',
    'raw_notification' => json_encode(['test' => true]),
]);

echo "Created test QRIS ID: {$qris->qris_transaction_id}\n";
echo "Status: {$qris->status}\n";
echo "Expired at: {$qris->expired_at}\n";
EOF

# Run expiration job
php artisan qris:expire

# Check if it was updated
php artisan tinker << 'EOF'
use App\Models\PaymentTransaction;

$qris = PaymentTransaction::latest()->first();
echo "After expiry job:\n";
echo "Status: {$qris->status}\n";
echo "Updated at: {$qris->updated_at}\n";

if ($qris->status === 'expire') {
    echo "✅ Test PASSED: QRIS successfully expired\n";
} else {
    echo "❌ Test FAILED: QRIS was not updated\n";
}
EOF
```

---

## 🔄 Monitoring Commands

### Continuous Monitoring

```bash
# Monitor scheduler execution (run every minute)
watch -n 60 'grep "Expired" storage/logs/laravel.log | tail -1'

# Monitor errors
watch -n 30 'grep "ERROR\|FAILED" storage/logs/laravel.log | tail -5'

# Monitor database size
watch -n 300 'mysql -u $DB_USER -p $DB_NAME -e "SELECT COUNT(*) as total, SUM(CASE WHEN status=\"pending\" THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status=\"expire\" THEN 1 ELSE 0 END) as expired FROM qris_transactions;"'
```

### Check Cron Job Status

```bash
# Check if cron is running
ps aux | grep cron

# Check syslog for cron entries
tail -f /var/log/syslog | grep CRON

# Or on macOS
log stream --predicate 'process == "cron"'
```

### Health Check Script

```bash
#!/bin/bash

echo "======================================"
echo "QRIS System Health Check"
echo "======================================"

# 1. Check scheduler is registered
echo "1. Checking scheduler..."
php artisan schedule:list | grep qris

# 2. Check recent executions
echo ""
echo "2. Checking recent executions..."
grep "Expired" storage/logs/laravel.log | tail -3

# 3. Check for errors
echo ""
echo "3. Checking for errors..."
ERROR_COUNT=$(grep "ERROR\|FAILED" storage/logs/laravel.log | grep -i qris | wc -l)
echo "Recent errors: $ERROR_COUNT"

# 4. Database status
echo ""
echo "4. Database status..."
mysql -u $DB_USER -p $DB_NAME -e \
  "SELECT COUNT(*) as total_pending FROM qris_transactions WHERE status='pending';"

# 5. API connectivity
echo ""
echo "5. Testing API..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -H "Authorization: Bearer $TEST_TOKEN" \
  http://localhost:8000/api/orders/1/qris-detail)
echo "API status: $HTTP_CODE"

if [ $HTTP_CODE -eq 200 ]; then
  echo "✅ System operational"
else
  echo "❌ System issue detected"
fi
```

---

## 🆘 Rollback Commands

### If Issues Occur

```bash
# Quick rollback from backup
BACKUP_DATE="20240115_143000"  # Change to actual backup date
LARAVEL_PATH="/path/to/laravel-5scent"

# Stop scheduler temporarily
(crontab -l 2>/dev/null | grep -v "schedule:run") | crontab -

# Restore from backup
cp -r backups/$BACKUP_DATE/app/Console $LARAVEL_PATH/
cp -r backups/$BACKUP_DATE/app/Events $LARAVEL_PATH/
cp -r backups/$BACKUP_DATE/app/Services $LARAVEL_PATH/
cp -r backups/$BACKUP_DATE/app/Http/Controllers $LARAVEL_PATH/

# Clear cache
php artisan cache:clear
php artisan config:cache

# Restore database if data corruption
mysql $DB_NAME < backup_qris_$BACKUP_DATE.sql

# Restore scheduler if needed
(crontab -l 2>/dev/null; echo "* * * * * cd $LARAVEL_PATH && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo "✅ Rollback completed"
```

---

## 📊 Production Checklist Commands

```bash
#!/bin/bash

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "Production Deployment Checklist"
echo "================================"

# Check 1: Files exist
echo -n "Checking files... "
if [ -f "app/Console/Commands/ExpireQrisTransactions.php" ] && \
   [ -f "app/Events/NotificationCreated.php" ] && \
   [ -f "app/Console/Kernel.php" ]; then
  echo -e "${GREEN}✓${NC}"
else
  echo -e "${RED}✗${NC}"
fi

# Check 2: Syntax valid
echo -n "Checking syntax... "
if php -l app/Console/Commands/ExpireQrisTransactions.php >/dev/null 2>&1; then
  echo -e "${GREEN}✓${NC}"
else
  echo -e "${RED}✗${NC}"
fi

# Check 3: Scheduler registered
echo -n "Checking scheduler... "
if php artisan list | grep -q qris:expire; then
  echo -e "${GREEN}✓${NC}"
else
  echo -e "${RED}✗${NC}"
fi

# Check 4: Cron configured
echo -n "Checking cron... "
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
  echo -e "${GREEN}✓${NC}"
else
  echo -e "${YELLOW}⚠${NC} Not configured"
fi

# Check 5: Database
echo -n "Checking database... "
if mysql -h localhost -u $DB_USER -p$DB_PASS $DB_NAME -e "DESCRIBE qris_transactions" >/dev/null 2>&1; then
  echo -e "${GREEN}✓${NC}"
else
  echo -e "${RED}✗${NC}"
fi

echo ""
echo "Deployment ready!"
```

---

## 🚀 One-Command Deployment

```bash
#!/bin/bash

# Complete deployment script
set -e  # Exit on error

echo "Starting QRIS Deployment..."

LARAVEL_PATH="/path/to/laravel-5scent"
NEXTJS_PATH="/path/to/nextjs-5scent"
DB_USER="your_db_user"
DB_PASSWORD="your_db_password"
DB_NAME="your_db_name"

# Backup
echo "Creating backup..."
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -h localhost -u $DB_USER -p$DB_PASSWORD $DB_NAME > backup_$BACKUP_DATE.sql

# Backend deployment
echo "Deploying backend..."
cp ExpireQrisTransactions.php $LARAVEL_PATH/app/Console/Commands/
cp NotificationCreated.php $LARAVEL_PATH/app/Events/
cp Kernel.php $LARAVEL_PATH/app/Console/

# Frontend deployment
echo "Deploying frontend..."
cp qris-page.tsx $NEXTJS_PATH/app/orders/[orderId]/qris/page.tsx
cp QrisPaymentClient.tsx $NEXTJS_PATH/app/orders/[orderId]/qris/QrisPaymentClient.tsx
cp orders-page.tsx $NEXTJS_PATH/app/orders/page.tsx
cp NotificationContext.tsx $NEXTJS_PATH/contexts/NotificationContext.tsx

# Clear caches
echo "Clearing caches..."
cd $LARAVEL_PATH
php artisan cache:clear
php artisan config:cache

# Configure scheduler
echo "Configuring scheduler..."
(crontab -l 2>/dev/null | grep -v "schedule:run"; echo "* * * * * cd $LARAVEL_PATH && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Build frontend
echo "Building frontend..."
cd $NEXTJS_PATH
npm run build

# Verify
echo "Verifying deployment..."
php artisan schedule:list | grep qris
echo "✅ Deployment complete!"
echo "Backup saved: backup_$BACKUP_DATE.sql"
```

---

## 📝 Notes

- Replace `/path/to/laravel-5scent` with actual path
- Replace `/path/to/nextjs-5scent` with actual path
- Replace `$DB_USER`, `$DB_PASSWORD`, `$DB_NAME` with actual credentials
- Test in development/staging first
- Monitor logs for 24 hours after deployment
- Keep backups for 30 days

---

**See:** [QRIS_IMPLEMENTATION_GUIDE.md](QRIS_IMPLEMENTATION_GUIDE.md) for detailed deployment guide

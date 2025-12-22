# QRIS Implementation - Team Deployment Checklist

**Project:** QRIS Auto-Expiration System  
**Deployment Date:** [DATE]  
**Team:** Development, QA, DevOps  
**Status:** Ready for Deployment  

---

## 📋 Pre-Deployment Phase (Day 1-2)

### Documentation Review
- [ ] **Developers** read QRIS_QUICK_REFERENCE.md (30 mins)
- [ ] **QA** read QRIS_TEST_CHECKLIST.md (30 mins)
- [ ] **DevOps** read QRIS_IMPLEMENTATION_GUIDE.md (45 mins)
- [ ] **Manager** read QRIS_EXECUTIVE_SUMMARY_FINAL.md (15 mins)
- [ ] **Team** discusses DOCUMENTATION_INDEX_MASTER.md (30 mins)

**Checklist:**
- [ ] All team members reviewed assigned documents
- [ ] Questions answered
- [ ] Concerns addressed
- [ ] Timeline confirmed

### Code Review
- [ ] Review: QRIS_CHANGES_SUMMARY.md
- [ ] Review: ExpireQrisTransactions.php (3 new backend files)
- [ ] Review: QrisPaymentController.php changes
- [ ] Review: OrderQrisController.php changes
- [ ] Review: NotificationService.php changes
- [ ] Review: All 4 frontend file changes
- [ ] Sign-off: Lead developer approves

**Checklist:**
- [ ] All code changes reviewed
- [ ] No syntax errors
- [ ] Logic verified
- [ ] Performance acceptable
- [ ] Security approved
- [ ] **Code review sign-off:** _________________ (signature)

### Staging Environment Prep
- [ ] Staging environment ready
- [ ] Database staging backed up
- [ ] All dependencies installed
- [ ] Laravel version confirmed (12.x)
- [ ] PHP version confirmed (8.1+)
- [ ] Node.js version confirmed (18+)

**Checklist:**
- [ ] Staging environment verified
- [ ] Database backup taken
- [ ] No blocking issues
- [ ] Ready for testing

---

## 🧪 Testing Phase (Day 2-3)

### Setup & Deployment to Staging
- [ ] **DevOps** creates staging backup (database + files)
- [ ] **DevOps** deploys 3 new backend files
- [ ] **Developers** modify 3 existing backend files
- [ ] **Developers** deploy 4 frontend file changes
- [ ] **DevOps** runs: `php artisan cache:clear`
- [ ] **DevOps** runs: `php artisan config:cache`
- [ ] **DevOps** configures scheduler (test cron)
- [ ] **DevOps** runs: `php artisan schedule:list` (verify qris:expire)

**Checklist:**
- [ ] All files deployed
- [ ] No deployment errors
- [ ] Scheduler registered
- [ ] Caches cleared
- [ ] Frontend rebuilt

### Pre-Test Validation
- [ ] Run: `DEPLOYMENT_VALIDATION_SCRIPT.md` (All sections)
  - [ ] Backend code verification
  - [ ] PHP syntax check
  - [ ] Artisan command verification
  - [ ] Database schema verification
  - [ ] Frontend build test
  - [ ] Scheduler integration test

**Checklist:**
- [ ] All validation tests pass
- [ ] No blocking issues
- [ ] System ready for QA

### QA Testing
- [ ] **QA Lead** reviews QRIS_TEST_CHECKLIST.md
- [ ] Follow **20-point test suite:**
  - [ ] Test 1-8: Backend tests (expiration, de-duplication, etc.)
  - [ ] Test 9-15: Frontend tests (UI, polling, notifications)
  - [ ] Test 16-20: Integration & edge case tests
- [ ] **QA** documents all test results
- [ ] **QA** logs any issues found
- [ ] **QA** verifies fixes
- [ ] **QA** signs off: All tests pass

**QA Sign-off:**
- [ ] All 20 test cases passed
- [ ] No critical bugs
- [ ] No major issues
- [ ] System ready for production
- [ ] **QA Lead Sign-off:** _________________ (signature)

### Manual User Journey Testing
- [ ] Create test order
- [ ] Navigate to QRIS payment page
- [ ] Verify QR code displays
- [ ] Verify countdown timer
- [ ] Let payment expire
- [ ] Verify "Payment Expired" message
- [ ] Refresh page - verify no duplicate QR
- [ ] Verify notification appears in sidebar
- [ ] Go to order history
- [ ] Verify cancelled order shows "View Details" only
- [ ] Verify no secondary buttons for cancelled order

**Checklist:**
- [ ] Full user journey tested
- [ ] All expected UI states seen
- [ ] No unexpected errors
- [ ] Ready for production

---

## 🚀 Production Deployment Phase (Day 4)

### Pre-Deployment Checklist
- [ ] **Manager** confirms deployment window (off-peak time)
- [ ] **DevOps** confirms server access
- [ ] **DevOps** confirms database credentials
- [ ] **Developers** on standby for issues
- [ ] **QA** ready for post-deployment verification
- [ ] **Support team** notified of deployment

**Checklist:**
- [ ] Deployment window confirmed
- [ ] All team members ready
- [ ] Communication channels open
- [ ] Rollback plan reviewed

### Production Backup
- [ ] **DevOps** creates full database backup
  ```bash
  mysqldump -h localhost -u $USER -p $DB_NAME > backup_qris_$(date +%Y%m%d_%H%M%S).sql
  ```
- [ ] **DevOps** creates application backup
  ```bash
  cp -r app/ backup_app_$(date +%Y%m%d_%H%M%S)/
  ```
- [ ] Verify backup file size > 100MB
- [ ] Verify backup is readable
- [ ] Store backup in safe location

**Checklist:**
- [ ] Database backup verified
- [ ] Application backup verified
- [ ] Backup location documented
- [ ] Rollback tested (optional but recommended)

### Production Deployment
Follow: [DEPLOYMENT_COMMAND_TEMPLATES.md](DEPLOYMENT_COMMAND_TEMPLATES.md)

**Step 1: Deploy Backend**
- [ ] Copy ExpireQrisTransactions.php to app/Console/Commands/
- [ ] Copy NotificationCreated.php to app/Events/
- [ ] Copy Kernel.php to app/Console/
- [ ] Modify QrisPaymentController.php
- [ ] Modify OrderQrisController.php
- [ ] Modify NotificationService.php
- [ ] Verify all files present: `ls -la app/Console/Commands/ExpireQrisTransactions.php`

**Checklist:**
- [ ] All backend files deployed
- [ ] No errors during copy
- [ ] File permissions correct (644)

**Step 2: Deploy Frontend**
- [ ] Copy qris-page.tsx to app/orders/[orderId]/qris/page.tsx
- [ ] Copy QrisPaymentClient.tsx to app/orders/[orderId]/qris/
- [ ] Copy orders-page.tsx to app/orders/page.tsx
- [ ] Copy NotificationContext.tsx to contexts/
- [ ] Verify all files: `ls -la app/orders/[orderId]/qris/page.tsx`

**Checklist:**
- [ ] All frontend files deployed
- [ ] No errors during copy
- [ ] Build completed: `npm run build`

**Step 3: Clear Caches**
- [ ] Run: `php artisan cache:clear`
- [ ] Run: `php artisan config:cache`
- [ ] Run: `php artisan route:cache`
- [ ] Run: `php artisan view:cache`

**Checklist:**
- [ ] All caches cleared
- [ ] No errors during cache clear

**Step 4: Configure Scheduler**
- [ ] Check if already in crontab: `crontab -l | grep "schedule:run"`
- [ ] If not present, add to crontab:
  ```
  * * * * * cd /path/to/laravel && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Verify cron added: `crontab -l | grep "schedule:run"`

**Checklist:**
- [ ] Scheduler cron job added
- [ ] Cron verified running
- [ ] Logs being written

**Step 5: Verify Scheduler**
- [ ] Run: `php artisan schedule:list`
  - Should show: `qris:expire` command
- [ ] Run: `php artisan qris:expire` (manual test)
  - Should output: "Expired X QRIS transaction(s)"
- [ ] Check logs: `tail -f storage/logs/laravel.log | grep qris`

**Checklist:**
- [ ] Scheduler command registered
- [ ] Manual execution works
- [ ] Logs show execution
- [ ] No errors

### Post-Deployment Verification (Day 4)

Run: [DEPLOYMENT_VALIDATION_SCRIPT.md](DEPLOYMENT_VALIDATION_SCRIPT.md) Post-Deployment section

- [ ] **DevOps** verifies scheduler running
  ```bash
  ps aux | grep cron
  ```
- [ ] **DevOps** checks recent logs
  ```bash
  tail -100 storage/logs/laravel.log | grep qris
  ```
- [ ] **DevOps** verifies no errors
  ```bash
  grep "ERROR\|FAILED" storage/logs/laravel.log | grep -i qris
  ```
- [ ] **QA** tests critical paths manually
  - [ ] Create new order
  - [ ] Navigate to QRIS payment
  - [ ] Verify QR displays
  - [ ] Check notification appears
  - [ ] Verify order history UI

**Checklist:**
- [ ] Scheduler confirmed running
- [ ] No ERROR logs
- [ ] Manual tests pass
- [ ] System stable
- [ ] **Post-deployment sign-off:** _________________ (signature)

---

## 📊 24-Hour Monitoring Phase (Day 4-5)

### Continuous Monitoring
- [ ] **DevOps** monitors logs continuously
  ```bash
  watch -n 60 'tail -5 storage/logs/laravel.log | grep qris'
  ```
- [ ] **DevOps** checks error rate every hour
- [ ] **DevOps** verifies scheduler runs every minute
- [ ] **QA** performs spot checks every 2 hours
- [ ] **Support** monitors user reports

**Monitoring Schedule:**
- [ ] Hour 0-4: Continuous (every 15 mins)
- [ ] Hour 4-8: Frequent (every 30 mins)
- [ ] Hour 8-24: Regular (every 1-2 hours)
- [ ] Hour 24+: Standard (daily checks)

### Database Monitoring
- [ ] Monitor expired transaction count
  ```sql
  SELECT COUNT(*) FROM qris_transactions WHERE status='expire' AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR);
  ```
- [ ] Monitor notification count
  ```sql
  SELECT COUNT(*) FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR);
  ```
- [ ] Check for duplicate notifications
  ```sql
  SELECT order_id, COUNT(*) FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) GROUP BY order_id HAVING COUNT(*) > 10;
  ```

**Checklist:**
- [ ] Expired transactions increasing (expected)
- [ ] Notifications creating normally
- [ ] No duplicate notification spam
- [ ] No orphaned records

### Performance Monitoring
- [ ] Check API response times
- [ ] Monitor database query times
- [ ] Watch for slow queries
- [ ] Monitor CPU/memory usage
- [ ] Check disk space

**Checklist:**
- [ ] Response times < 200ms
- [ ] No slow queries
- [ ] CPU usage normal
- [ ] Memory usage normal
- [ ] Disk space adequate

### Issue Escalation
If issues found:
- [ ] **DevOps** analyzes error logs
- [ ] **Developer** reviews code
- [ ] **QA** reproduces issue
- [ ] Fix identified and applied
- [ ] Testing resumed
- [ ] Logs verified

**Escalation Path:**
1. **Minor Issue** → Fix + test + redeploy
2. **Major Issue** → Rollback (see below)
3. **Critical Issue** → Rollback immediately

### Rollback Plan (If Needed)
If critical issues found:
- [ ] **DevOps** stops new deployments
- [ ] **DevOps** removes scheduler cron:
  ```bash
  (crontab -l | grep -v "schedule:run") | crontab -
  ```
- [ ] **DevOps** restores from backup
  ```bash
  mysql $DB_NAME < backup_qris_TIMESTAMP.sql
  ```
- [ ] **DevOps** restores application files
- [ ] **DevOps** clears caches
- [ ] **QA** verifies rollback successful
- [ ] **Team** analyzes issue

**Checklist:**
- [ ] Rollback procedure tested
- [ ] Backup verified
- [ ] Rollback time < 30 minutes
- [ ] System recovers successfully

---

## ✅ Sign-Off Phase

### Development Team
- [ ] **Lead Developer** confirms code quality
- [ ] **Lead Developer** confirms no critical bugs
- [ ] **Signature:** _________________ **Date:** __________

### Quality Assurance
- [ ] **QA Lead** confirms all tests pass
- [ ] **QA Lead** confirms no blockers
- [ ] **Signature:** _________________ **Date:** __________

### DevOps / System Admin
- [ ] **DevOps Lead** confirms deployment successful
- [ ] **DevOps Lead** confirms system stable
- [ ] **Signature:** _________________ **Date:** __________

### Product / Management
- [ ] **Product Manager** confirms business requirements met
- [ ] **Project Manager** confirms timeline met
- [ ] **Signature:** _________________ **Date:** __________

---

## 📊 Post-Deployment Metrics (Days 5-7)

### Business Metrics
- [ ] **Support tickets** tracking (target: -40%)
- [ ] **User complaints** monitoring (target: 0)
- [ ] **Payment success rate** (target: +5%)
- [ ] **Order cancellation rate** (target: decreased)

### Technical Metrics
- [ ] **API response time** (target: < 200ms)
- [ ] **Database query time** (target: < 50ms)
- [ ] **Scheduler execution time** (target: < 1 second)
- [ ] **Error rate** (target: 0%)
- [ ] **Uptime** (target: 99.9%)

### Operational Metrics
- [ ] **Deployment time** recorded
- [ ] **Issues found** documented
- [ ] **Resolution time** tracked
- [ ] **Team feedback** collected

---

## 📝 Documentation Sign-Off

- [ ] **Documentation reviewed** for accuracy
- [ ] **All guides** accessible to team
- [ ] **Troubleshooting** guide available
- [ ] **Monitoring procedures** documented
- [ ] **Signature:** _________________ **Date:** __________

---

## 🎓 Team Knowledge Transfer

- [ ] **Developers** understand code changes
- [ ] **DevOps** understands deployment procedures
- [ ] **QA** understands test suite
- [ ] **Support** trained on new features
- [ ] **Management** briefed on status

**Training Sign-offs:**
- [ ] **Dev Lead:** _________________ **Date:** __________
- [ ] **DevOps Lead:** _________________ **Date:** __________
- [ ] **QA Lead:** _________________ **Date:** __________
- [ ] **Support Lead:** _________________ **Date:** __________

---

## 🚀 Go-Live Approval

**Overall Status:** 
- [ ] Pre-deployment: ✅ Complete
- [ ] Testing: ✅ Complete
- [ ] Deployment: ✅ Complete
- [ ] Verification: ✅ Complete
- [ ] Monitoring: ✅ Complete
- [ ] Sign-offs: ✅ Complete

**APPROVED FOR PRODUCTION DEPLOYMENT**

**Approved by:** _________________________ **Date:** __________

**Final Deployment Sign-off:**

I hereby confirm that:
1. ✅ All code has been reviewed and tested
2. ✅ All documentation is complete and accurate
3. ✅ All team members are trained and ready
4. ✅ Deployment procedures have been verified
5. ✅ Rollback plan is in place
6. ✅ Monitoring procedures are ready
7. ✅ This system is production-ready

**Project Manager:** _________________________ **Date:** __________

**Technical Lead:** _________________________ **Date:** __________

**Director/Manager:** _________________________ **Date:** __________

---

## 📞 Emergency Contacts

| Role | Name | Phone | Email |
|------|------|-------|-------|
| **Lead Dev** | | | |
| **DevOps Lead** | | | |
| **QA Lead** | | | |
| **On-Call Support** | | | |
| **Manager** | | | |

---

## 📚 Key Documentation Links

1. **Quick Start:** [DOCUMENTATION_INDEX_MASTER.md](DOCUMENTATION_INDEX_MASTER.md)
2. **Deploy:** [DEPLOYMENT_COMMAND_TEMPLATES.md](DEPLOYMENT_COMMAND_TEMPLATES.md)
3. **Test:** [QRIS_TEST_CHECKLIST.md](QRIS_TEST_CHECKLIST.md)
4. **Reference:** [QRIS_QUICK_REFERENCE.md](QRIS_QUICK_REFERENCE.md)
5. **Troubleshoot:** [QRIS_IMPLEMENTATION_GUIDE.md](QRIS_IMPLEMENTATION_GUIDE.md)

---

## ✨ Final Notes

- Keep this checklist updated throughout deployment
- Check off items as completed
- Document any deviations from plan
- Update team on progress regularly
- Maintain backup files for 30 days

**Deployment Log:**
```
Started: _________________ Date: __________
Completed: _________________ Date: __________
Issues Found: _________________ 
Issues Resolved: _________________ 
Final Status: ✅ SUCCESS
```

---

**This checklist ensures a smooth, well-coordinated deployment with proper verification and rollback plans.**

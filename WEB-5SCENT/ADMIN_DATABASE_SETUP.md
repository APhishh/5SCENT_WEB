# Admin Dashboard - Database Setup

## Database Table Description

### Admin Table Schema

```sql
SHOW CREATE TABLE admin;
```

Output:
```sql
CREATE TABLE `admin` (
  `admin_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `admin_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Column Details

| Column | Type | Null | Key | Default | Extra | Description |
|--------|------|------|-----|---------|-------|-------------|
| `admin_id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment | Unique admin identifier |
| `name` | varchar(100) | YES | | NULL | | Admin user's full name |
| `email` | varchar(100) | YES | UNI | NULL | | Admin email (unique) |
| `password` | varchar(255) | YES | | NULL | | Hashed password (bcrypt) |
| `role` | varchar(50) | YES | | NULL | | Admin role (admin/superadmin) |
| `created_at` | datetime | YES | | CURRENT_TIMESTAMP | | Account creation timestamp |
| `updated_at` | datetime | YES | | CURRENT_TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Last update timestamp |

## Creating Admin Accounts

### Method 1: Using Laravel Tinker (Recommended)

```bash
cd backend/laravel-5scent
php artisan tinker
```

Then execute:
```php
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

Admin::create([
    'name' => 'Admin User',
    'email' => 'admin@5scent.com',
    'password' => Hash::make('AdminPass123!'),
    'role' => 'admin',
]);

// Verify creation
Admin::all();
```

### Method 2: Using Setup Script

```bash
cd backend/laravel-5scent
php artisan tinker < setup_admin.php
```

### Method 3: Direct SQL (NOT RECOMMENDED - passwords won't be hashed)

```sql
-- DO NOT USE - passwords won't be hashed properly
INSERT INTO admin (name, email, password, role, created_at, updated_at)
VALUES (
    'Admin User',
    'admin@5scent.com',
    'not_hashed_password',
    'admin',
    NOW(),
    NOW()
);
```

⚠️ **Important**: Always use Method 1 or 2 to properly hash passwords!

## Querying Admin Accounts

### Get All Admins
```sql
SELECT admin_id, name, email, role, created_at, updated_at
FROM admin;
```

### Get Specific Admin
```sql
SELECT * FROM admin
WHERE email = 'admin@5scent.com';
```

### Count Total Admins
```sql
SELECT COUNT(*) as total_admins FROM admin;
```

### Get Admin by ID
```sql
SELECT * FROM admin
WHERE admin_id = 1;
```

## Modifying Admin Accounts

### Update Admin Name
```php
// Using Tinker
$admin = Admin::find(1);
$admin->update(['name' => 'New Name']);
```

### Update Admin Email
```php
$admin = Admin::find(1);
$admin->update(['email' => 'newemail@5scent.com']);
```

### Change Admin Password
```php
use Illuminate\Support\Facades\Hash;

$admin = Admin::find(1);
$admin->update(['password' => Hash::make('NewPassword123!')]);
```

### Update Admin Role
```php
$admin = Admin::find(1);
$admin->update(['role' => 'superadmin']);
```

## Deleting Admin Accounts

### Delete Specific Admin
```php
Admin::where('email', 'admin@5scent.com')->delete();
// OR
Admin::find(1)->delete();
```

### Delete All Admins (DANGEROUS!)
```php
Admin::truncate();  // Deletes all records
```

## Database Verification

### Check if admin table exists
```sql
SHOW TABLES LIKE 'admin';
```

### Check admin table structure
```sql
DESCRIBE admin;
```

### Check all indexes
```sql
SHOW INDEXES FROM admin;
```

### Check table size
```sql
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
FROM information_schema.tables
WHERE table_name = 'admin' AND table_schema = 'your_database_name';
```

## Common Issues

### Issue: "Unknown column 'admin_id' in field list"
**Solution**: Verify migration ran successfully
```bash
php artisan migrate --path=/database/migrations/2024_01_01_000002_create_admin_table.php
```

### Issue: "Unique constraint violated"
**Solution**: Email already exists
```sql
-- Check for duplicates
SELECT email, COUNT(*) FROM admin GROUP BY email HAVING COUNT(*) > 1;
```

### Issue: "No admin found"
**Solution**: Create admin account first
```php
Admin::create([
    'name' => 'Admin',
    'email' => 'admin@5scent.com',
    'password' => Hash::make('AdminPass123!'),
    'role' => 'admin',
]);
```

### Issue: "Password doesn't match"
**Solution**: Verify password was hashed correctly
```php
use Illuminate\Support\Facades\Hash;

$admin = Admin::find(1);
Hash::check('AdminPass123!', $admin->password);  // Should return true
```

## Default Admin Account

After running setup script or Method 1, you'll have:

```
admin_id: 1
name: Admin User
email: admin@5scent.com
password: (hashed) AdminPass123!
role: admin
created_at: (current timestamp)
updated_at: (current timestamp)
```

## Password Security

### Password Requirements
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character

### Example Strong Passwords
- AdminPass123!
- SecureAdmin@2025
- MyAdm!n123

### Weak Passwords (Will fail)
- admin123 (no uppercase, special char)
- ADMIN123 (no lowercase)
- Admin (no number, special char)
- password (too generic)

## Hashing Algorithm

The admin passwords are hashed using:
- **Algorithm**: bcrypt
- **Cost**: 10 (default)
- **Output**: 60-character hash

Example hash:
```
$2y$10$abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
```

## Backup

### Backup admin table
```sql
-- Backup to file
mysqldump -u username -p database_name admin > admin_backup.sql

-- Restore from backup
mysql -u username -p database_name < admin_backup.sql
```

## Useful Queries

### Get admin with creation date
```sql
SELECT 
    admin_id,
    name,
    email,
    role,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created,
    DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') as updated
FROM admin
ORDER BY created_at DESC;
```

### List all admin emails
```sql
SELECT email FROM admin;
```

### Admin created in last 7 days
```sql
SELECT * FROM admin
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;
```

### Get admin login history (if tracking)
```sql
-- Note: Requires additional login_attempts table
SELECT admin_id, ip_address, attempt_time
FROM admin_login_attempts
WHERE admin_id = 1
ORDER BY attempt_time DESC
LIMIT 10;
```

---

**Database Setup Date**: November 27, 2025
**Version**: 1.0
**Status**: ✅ Ready for Use

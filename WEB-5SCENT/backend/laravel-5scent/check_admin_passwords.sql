-- Check Admin Table Password Status
-- This script helps identify and fix unhashed passwords

-- 1. View all admin accounts and their password hash status
SELECT 
    admin_id,
    name,
    email,
    password,
    CASE 
        WHEN password LIKE '$2%' THEN 'HASHED (bcrypt)'
        ELSE 'NOT HASHED'
    END as password_status,
    created_at
FROM admin
ORDER BY admin_id;

-- 2. Count hashed vs unhashed passwords
SELECT 
    CASE 
        WHEN password LIKE '$2%' THEN 'HASHED'
        ELSE 'NOT HASHED'
    END as status,
    COUNT(*) as count
FROM admin
GROUP BY status;

-- 3. Find specific unhashed passwords (admin IDs 1-4)
SELECT 
    admin_id,
    name,
    email,
    password as plain_password
FROM admin
WHERE admin_id IN (1, 2, 3, 4)
AND password NOT LIKE '$2%';

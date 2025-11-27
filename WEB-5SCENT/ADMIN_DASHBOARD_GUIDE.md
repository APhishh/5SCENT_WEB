# Admin Dashboard Implementation Guide

## Overview
The admin dashboard has been fully implemented with authentication, layout, and a comprehensive dashboard view that matches the reference designs (monthly, weekly, and yearly views).

## Database Schema - Admin Table

```sql
DESCRIBE admin;

+----------+---------------------+------+-----+---------+-------+
| Field    | Type                | Null | Key | Default | Extra |
+----------+---------------------+------+-----+---------+-------+
| admin_id | bigint(20) unsigned | NO   | PRI | NULL    | auto  |
| name     | varchar(100)        | YES  |     | NULL    |       |
| email    | varchar(100)        | YES  | UNI | NULL    |       |
| password | varchar(255)        | YES  |     | NULL    |       |
| role     | varchar(50)         | YES  |     | NULL    |       |
| created_at | datetime          | YES  |     | NULL    |       |
| updated_at | datetime          | YES  |     | NULL    |       |
+----------+---------------------+------+-----+---------+-------+
```

### Table Description:
- **admin_id**: Primary key, auto-incrementing unique identifier
- **name**: Admin user's full name (max 100 characters)
- **email**: Admin user's email address (unique, max 100 characters)
- **password**: Hashed password using Laravel's password hashing
- **role**: Admin role type (e.g., 'admin', 'superadmin')
- **created_at**: Timestamp when admin account was created
- **updated_at**: Timestamp when admin account was last updated

## Setup Instructions

### 1. Create Admin Account

Use Laravel Tinker to create an admin account:

```bash
cd backend/laravel-5scent
php artisan tinker
```

Then run:
```php
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

Admin::create([
    'name' => 'Admin User',
    'email' => 'admin@5scent.com',
    'password' => Hash::make('AdminPass123!'),
    'role' => 'admin',
]);
```

Or use the setup script:
```bash
php artisan tinker < setup_admin.php
```

### 2. Default Admin Credentials
- **Email**: admin@5scent.com
- **Password**: AdminPass123!

## Frontend Implementation

### File Structure
```
frontend/web-5scent/
├── app/
│   ├── admin/
│   │   ├── login/
│   │   │   └── page.tsx          # Admin login page
│   │   └── dashboard/
│   │       └── page.tsx          # Admin dashboard (main page)
│   └── layout.tsx                # Updated with AdminProvider
├── components/
│   └── AdminLayout.tsx           # Admin sidebar and header
├── contexts/
│   └── AdminContext.tsx          # Admin authentication context
└── lib/
    └── api.ts                    # Updated with admin token support
```

### Key Components

#### 1. **AdminContext.tsx** (`contexts/AdminContext.tsx`)
- Manages admin authentication state
- Handles login/logout logic
- Stores admin token in localStorage with key: `admin_token`
- Stores admin data in localStorage with key: `admin`

#### 2. **AdminLayout.tsx** (`components/AdminLayout.tsx`)
- Responsive sidebar navigation
- Header with current date display
- Menu items:
  - Dashboard (active on /admin/dashboard)
  - Products
  - Orders
  - POS Tool
  - Sales Reports
  - Reviews
  - Settings
  - View Store (opens customer site)
  - Logout

#### 3. **Admin Login Page** (`app/admin/login/page.tsx`)
- Clean login form with email/password validation
- Error messages for incorrect credentials
- Redirects to dashboard on successful login
- Auto-redirects if already logged in

#### 4. **Admin Dashboard** (`app/admin/dashboard/page.tsx`)
- **Key Metrics Cards (Top Row)**:
  - Total Orders with percentage change
  - Packaging (orders being packed)
  - Shipping (orders in transit)
  - Delivered with percentage change
  - Cancelled orders

- **Financial & Inventory Cards**:
  - Total Revenue (Rp display format)
  - Average Order Value
  - Total Products in stock

- **Sales Overview Chart**:
  - Three time frame options: This Week, This Month, This Year
  - Visual bar chart with color-coded bars
  - Displays revenue values in Rp format

- **Best Sellers Section**:
  - Top 5 products by sales
  - Shows stock count
  - Star ratings with half-star support
  - Ranking numbers (1-5)

- **Recent Orders Table**:
  - Latest 3 orders
  - Order ID, Customer, Items, Total, Date, Status
  - Color-coded status badges:
    - Green: Delivered
    - Purple: Shipping
    - Blue: Packaging
    - Red: Cancelled
  - "View All" link to full orders page

## API Endpoints

### Admin Authentication
- `POST /api/admin/login` - Admin login
  - Request: `{ email, password }`
  - Response: `{ admin: {...}, token, type: 'admin' }`

- `GET /api/admin/me` - Get current admin info (requires auth token)

- `POST /api/admin/logout` - Logout (requires auth token)

## Dashboard Features

### Time Frame Selector
- Dropdown to switch between:
  - **This Week**: Daily breakdown (Mon-Sun)
  - **This Month**: Weekly breakdown (Week 1-4)
  - **This Year**: Monthly breakdown (Jan-Dec)

### Chart Colors
- Week 1/Mon: Blue (#3B82F6)
- Week 2/Tue: Purple (#A855F7)
- Week 3/Wed: Pink (#EC4899)
- Week 4/Thu: Orange (#F97316)
- Fri: Green (#22C55E)
- Sat: Cyan (#06B6D4)
- Sun/Dec: Black (#000000)

### Data Display
- All currency values displayed in Rp (Rupiah)
- Revenue formatted as "Rp X.XM" for millions
- Order totals as "Rp XK" for thousands
- Star ratings with half-star support (e.g., 4.5 ⭐)

## Protected Routes

Admin routes are protected and will redirect to login if:
- No `admin_token` in localStorage
- Token has expired
- User is not authenticated as admin

Protected routes:
- `/admin/dashboard` - Dashboard page
- `/admin/products` - Products management (placeholder)
- `/admin/orders` - Orders management (placeholder)
- `/admin/pos` - POS tool (placeholder)
- `/admin/reports` - Sales reports (placeholder)
- `/admin/reviews` - Customer reviews (placeholder)
- `/admin/settings` - Store settings (placeholder)

## User Flow

1. User visits `/admin/login`
2. Enters email and password
3. Click "Login to Admin"
4. System calls `/api/admin/login` endpoint
5. On success:
   - Stores `admin_token` in localStorage
   - Stores `admin` object in localStorage
   - Redirects to `/admin/dashboard`
6. Dashboard displays:
   - Sidebar with navigation
   - Header with current date
   - All metrics and charts
   - Recent orders
   - Best sellers

## Authentication Flow

```
Frontend → POST /admin/login → Backend
                                  ↓
                        Verify credentials
                        Generate token
                        Return admin data + token
                                  ↓
         Frontend stores token + admin data
         Sets up axios interceptor with Bearer token
         Redirects to dashboard
```

## Notes

- Admin token is automatically included in all API requests via axios interceptor
- The dashboard uses mock data for demonstration
- In production, connect to real API endpoints for orders, revenue, products, etc.
- All timestamps use EST timezone (configurable in Next.js/Laravel config)
- Dashboard is fully responsive (mobile, tablet, desktop)

## Next Steps

To fully integrate with backend data:
1. Create `/api/admin/dashboard/stats` endpoint
2. Create `/api/admin/dashboard/sales` endpoint
3. Create `/api/admin/dashboard/best-sellers` endpoint
4. Create `/api/admin/dashboard/recent-orders` endpoint
5. Update dashboard page to fetch from these endpoints instead of mock data

## Troubleshooting

### Admin Login Not Working
- Verify admin account exists in database
- Check if email/password are correct
- Ensure Laravel API is running on `http://localhost:8000`

### Dashboard Not Displaying
- Check browser console for errors
- Verify `admin_token` is in localStorage
- Check if `/api/admin/me` endpoint is working

### Styling Issues
- Ensure Tailwind CSS is properly configured
- Check if react-icons/fa6 is installed
- Verify all dependencies are up to date

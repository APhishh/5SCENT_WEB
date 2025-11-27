# Admin Dashboard - Quick Reference

## 🔐 Admin Login Credentials

**URL**: `http://localhost:3000/admin/login`

```
Email: admin@5scent.com
Password: AdminPass123!
```

## 📊 Dashboard Access

Once logged in, visit: `http://localhost:3000/admin/dashboard`

## 🎯 Key Features Implemented

### ✅ Authentication
- Admin login with email/password validation
- Secure token-based authentication (Bearer token)
- Auto-redirect to login if not authenticated
- Auto-redirect to dashboard if already logged in

### ✅ Admin Layout
- Responsive sidebar navigation (collapses on mobile)
- Current date display in header
- Welcome message with admin name
- Mobile hamburger menu

### ✅ Dashboard Overview
- **3 Time Frame Filters**:
  - This Week (daily breakdown)
  - This Month (weekly breakdown)
  - This Year (monthly breakdown)

- **Key Metrics** (Top Row):
  - Total Orders (with % change)
  - Packaging count
  - Shipping count
  - Delivered (with % change)
  - Cancelled orders

- **Financial Cards**:
  - Total Revenue (Rp format with % change)
  - Average Order Value (Rp per transaction)
  - Total Products (active listings)

- **Sales Chart**:
  - Color-coded bars by time period
  - Displays revenue in Rp millions
  - Interactive hover tooltips
  - Dynamic height based on max value

- **Best Sellers**:
  - Top 5 products by sales
  - Ranking numbers (1-5)
  - Stock count
  - Star ratings (with half-star support)

- **Recent Orders**:
  - Latest 3 orders table
  - Order ID, Customer, Items, Total, Date, Status
  - Color-coded status badges:
    - 🟢 Green: Delivered
    - 🟣 Purple: Shipping
    - 🔵 Blue: Packaging
    - 🔴 Red: Cancelled
  - "View All" link to full orders page

### ✅ Sidebar Navigation
1. Dashboard ⭐ (Current)
2. Products
3. Orders
4. POS Tool
5. Sales Reports
6. Reviews
7. Settings
8. View Store (opens main site in new tab)
9. Logout (red button at bottom)

## 📁 File Locations

```
Frontend Structure:
├── app/
│   ├── admin/
│   │   ├── login/page.tsx                    # Login page
│   │   └── dashboard/page.tsx                # Dashboard (NEW)
│   └── layout.tsx                            # Added AdminProvider
├── components/
│   └── AdminLayout.tsx                       # Layout with sidebar (NEW)
├── contexts/
│   └── AdminContext.tsx                      # Auth context (NEW)
└── lib/
    └── api.ts                                # Updated with admin token

Backend Structure:
├── app/Http/Controllers/
│   └── AdminAuthController.php               # Already exists
├── Models/
│   └── Admin.php                             # Already exists
└── database/migrations/
    └── 2024_01_01_000002_create_admin_table.php
```

## 🔌 API Endpoints Used

- `POST /api/admin/login` - Admin login
- `GET /api/admin/me` - Get current admin
- `POST /api/admin/logout` - Admin logout

## 🎨 Design Details

### Color Scheme
- Primary: Black (#000000)
- Background: Light Gray (#F3F4F6)
- Text: Dark Gray (#111827, #374151, #6B7280)
- Success: Green (#22C55E)
- Warning: Purple (#A855F7)
- Info: Blue (#3B82F6)
- Danger: Red (#EF4444)

### Charts
- Week 1/Mon: Blue (#3B82F6)
- Week 2/Tue: Purple (#A855F7)
- Week 3/Wed: Pink (#EC4899)
- Week 4/Thu: Orange (#F97316)
- Fri: Green (#22C55E)
- Sat: Cyan (#06B6D4)
- Sun/Dec: Black (#000000)

### Typography
- Headers: Bold (font-weight: 700)
- Labels: Medium (font-weight: 500)
- Body: Regular (font-weight: 400)

## 📱 Responsive Behavior

- **Desktop** (>= 1024px): Full layout with sidebar always visible
- **Tablet** (768px - 1023px): Sidebar visible, single column
- **Mobile** (< 768px): Collapsible sidebar, hamburger menu

## 🔄 Data Refresh

Dashboard currently uses mock data. To integrate real data:

1. Create backend endpoints:
   - `/api/admin/dashboard/stats`
   - `/api/admin/dashboard/sales`
   - `/api/admin/dashboard/best-sellers`
   - `/api/admin/dashboard/recent-orders`

2. Update `app/admin/dashboard/page.tsx`:
   - Fetch from API instead of mock data
   - Use `setDashboardData()` with API response

## 🛠️ Troubleshooting

| Issue | Solution |
|-------|----------|
| Login fails | Verify admin account in DB, check API is running |
| Dashboard blank | Check browser console for errors, verify admin token |
| Styling broken | Clear cache, run `npm run build`, verify Tailwind |
| 401 errors | Token expired, clear localStorage and re-login |
| API not connecting | Verify Laravel API running on `http://localhost:8000` |

## 📝 Admin Credentials Database Query

```sql
SELECT * FROM admin WHERE email = 'admin@5scent.com';
```

Expected output:
```
admin_id | name        | email              | role  | created_at | updated_at
---------|-------------|-------------------|-------|------------|----------
1        | Admin User  | admin@5scent.com   | admin | [datetime] | [datetime]
```

## 🚀 Next Steps

1. ✅ Admin dashboard created
2. ✅ Login page styled
3. ✅ Sidebar navigation ready
4. ⏳ Connect to real API endpoints (Products, Orders, Reports pages)
5. ⏳ Add Products management interface
6. ⏳ Add Orders management interface
7. ⏳ Add POS Tool interface
8. ⏳ Add Sales Reports page
9. ⏳ Add Reviews management page
10. ⏳ Add Settings page

## 💡 Tips

- **For Testing**: Use admin credentials to login and test dashboard
- **For Development**: Mock data is in `app/admin/dashboard/page.tsx` - easy to replace
- **For Production**: Update API endpoints when backend endpoints are ready
- **For Mobile**: Test on mobile device or use browser DevTools device emulation

---

**Last Updated**: Nov 27, 2025
**Status**: ✅ Fully Implemented

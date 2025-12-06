# Build Mate - Database Setup Guide

## Quick Fix (2 Minutes!)

### ✅ What I Fixed:
1. **Updated `settings/config.php`** with correct XAMPP defaults:
   - Port: `3306` (was 3307)
   - Database: `buildmate_db` (was goa)
   - User: `root` (was goa)
   - Password: empty (was Osimorendes1)

### 🚀 Setup Steps:

#### Option 1: Using phpMyAdmin (Easiest)

1. **Open phpMyAdmin**
   - Go to: http://localhost/phpmyadmin
   - Login with:
     - Username: `root`
     - Password: (leave blank)

2. **Import Database**
   - Click "Import" tab
   - Click "Choose File"
   - Select: `/Applications/XAMPP/xamppfiles/htdocs/build_mate/db/setup_local.sql`
   - Click "Go" button at bottom
   - Wait for success message

3. **Done!** Refresh your Build Mate page

#### Option 2: Using MySQL Command Line

```bash
# Navigate to project
cd /Applications/XAMPP/xamppfiles/htdocs/build_mate

# Run setup script
/Applications/XAMPP/xamppfiles/bin/mysql -u root < db/setup_local.sql
```

### 🔐 Default Admin Login

After database setup, login with:
- **Email:** `admin@buildmate.com`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Change this password after first login!

### 🎯 What the Setup Creates:

- ✅ Database: `buildmate_db`
- ✅ All required tables (users, products, orders, etc.)
- ✅ Admin user account
- ✅ Proper foreign key relationships
- ✅ UTF-8 character encoding

### 🔧 Troubleshooting:

#### If you still get database errors:

1. **Check MySQL is running:**
   - Open XAMPP Control Panel
   - Make sure MySQL shows "Running" (green)
   - If not, click "Start"

2. **Verify port 3306:**
   ```bash
   netstat -an | grep 3306
   ```
   Should show LISTEN on port 3306

3. **Check MySQL password:**
   - In phpMyAdmin, go to "User accounts"
   - Find "root" user
   - Click "Edit privileges"
   - Set password to empty if needed

4. **Restart MySQL:**
   - In XAMPP Control Panel
   - Click "Stop" for MySQL
   - Wait 5 seconds
   - Click "Start"

### 📝 Database Configuration Files:

- **Main config:** `settings/config.php` (✅ Fixed)
- **Example env:** `.env.example` (reference only)
- **Setup script:** `db/setup_local.sql` (✅ Created)

### 🎉 After Setup:

Your Build Mate application will have:
- Working admin dashboard
- User authentication
- Product catalog
- Order management
- All features enabled

### Need Help?

If you still have issues:
1. Check XAMPP error logs: `/Applications/XAMPP/xamppfiles/logs/mysql_error.log`
2. Check PHP error log in your browser console
3. Verify MySQL is on port 3306 (not 3307)

---

**Last Updated:** December 3, 2025
**Status:** ✅ Ready to use

# BuildMate Migration Guide

## Migrating to: http://hc.cetplus.com/build_mate

### Step 1: Update `.env` File

Edit your `.env` file on the production server with these values:

```env
# Application Configuration
APP_NAME="Build Mate Ghana"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://hc.cetplus.com
APP_BASE_PATH=/build_mate/

# Database Configuration (Update with your production DB credentials)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=buildmate_db
DB_USER=your_production_db_user
DB_PASS=your_production_db_password

# Payment Configuration
PAYMENT_MODE=live
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key

# Email Configuration (Update with your SMTP credentials)
EMAIL_FROM=noreply@buildmate.gh
EMAIL_FROM_NAME="Build Mate Ghana"
SMTP_HOST=your_smtp_host
SMTP_PORT=587
SMTP_USER=your_smtp_username
SMTP_PASS=your_smtp_password
SMTP_ENCRYPTION=tls

# Security Configuration
ADMIN_EMAIL=your_admin@email.com

# Other settings remain the same
DEFAULT_CURRENCY=GHS
USD_TO_GHS_RATE=12.5
SESSION_LIFETIME=1800
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_TIME=900
MAX_UPLOAD_SIZE=5242880
ALLOWED_UPLOAD_TYPES=jpg,jpeg,png,pdf
```

### Step 2: Upload Files

Upload all files to: `/path/to/hc.cetplus.com/build_mate/`

### Step 3: Set Permissions

```bash
chmod 755 build_mate
chmod 644 build_mate/.env
chmod 755 build_mate/uploads
chmod 755 build_mate/assets
```

### Step 4: Import Database

1. Export your local database:
   ```bash
   mysqldump -u root buildmate_db > buildmate_backup.sql
   ```

2. Import to production:
   ```bash
   mysql -u your_user -p buildmate_db < buildmate_backup.sql
   ```

### Step 5: Test the System

Visit: `http://hc.cetplus.com/build_mate/`

Test these URLs:
- Homepage: `http://hc.cetplus.com/build_mate/`
- Login: `http://hc.cetplus.com/build_mate/login`
- Catalog: `http://hc.cetplus.com/build_mate/catalog`
- Contact: `http://hc.cetplus.com/build_mate/contact`

### What's Already Configured

✅ **URL Generation**: All `View::relUrl()` and `View::url()` calls automatically use `APP_BASE_PATH`
✅ **Asset Loading**: All `View::asset()` calls use the correct base path
✅ **Router**: Handles the base path automatically
✅ **Email Links**: Use `APP_URL` from config
✅ **Forms**: All form actions use relative URLs

### No Code Changes Needed!

The system is fully configured to work with the new URL. Just update the `.env` file and upload!

### Troubleshooting

**If URLs are broken:**
1. Check `.env` file has correct `APP_BASE_PATH=/build_mate/`
2. Verify `.htaccess` is uploaded
3. Check Apache mod_rewrite is enabled

**If database connection fails:**
1. Verify DB credentials in `.env`
2. Check database exists
3. Ensure DB user has proper permissions

**If assets don't load:**
1. Check file permissions (755 for directories, 644 for files)
2. Verify `APP_BASE_PATH` in `.env`
3. Clear browser cache

### Security Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Update all database credentials
- [ ] Set strong `DB_PASS`
- [ ] Configure SMTP for emails
- [ ] Set `ADMIN_EMAIL`
- [ ] Enable HTTPS (recommended)
- [ ] Set proper file permissions
- [ ] Keep `.env` file secure (not web-accessible)

### Post-Migration

1. Test all major features
2. Create test orders
3. Verify email notifications
4. Test payment flow (use test mode first)
5. Check mobile responsiveness
6. Monitor error logs

---

**Need Help?** Check the logs in your server's error log or contact support.

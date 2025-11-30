# Build Mate Server Setup Guide

## Server Information
- **Server IP**: 169.239.251.102
- **SSH Port**: 422
- **Web Access**: http://169.239.251.102:442/~griselda.owusu
- **phpMyAdmin**: http://169.239.251.102:442/phpmyadmin
- **Username**: griselda.owusu
- **MySQL Password**: Jytc1101$ (change on first use)

## Step 1: SSH into Server

```bash
ssh -C griselda.owusu@169.239.251.102 -p 422
# Password: 77042026 (change on first login)
```

## Step 2: Change MySQL Password

1. SSH into server
2. Read the instructions:
   ```bash
   cat ~/mysql_instructions.txt
   ```
3. Follow the instructions to change MySQL password
4. **New password**: Jytc1101$

## Step 3: Clone Repository (if not already done)

```bash
cd ~/public_html
# Use your GitHub Personal Access Token (get from https://github.com/settings/tokens)
git clone https://YOUR_TOKEN@github.com/griselda-oa/build_mate.git
cd build_mate
```

## Step 4: Install Dependencies

```bash
cd ~/public_html/build_mate
composer install --no-dev --optimize-autoloader
```

## Step 5: Configure Environment

```bash
# Copy example file
cp .env.example .env

# Edit .env file
nano .env
```

**Update these values in .env:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://169.239.251.102:442/~griselda.owusu/build_mate

DB_HOST=localhost
DB_PORT=3306
DB_NAME=buildmate_db
DB_USER=griselda.owusu
DB_PASS=Jytc1101$

# Add your API keys
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key
OPENAI_API_KEY=your_openai_key
```

## Step 6: Create Database in phpMyAdmin

1. Go to: http://169.239.251.102:442/phpmyadmin
2. Login with:
   - Username: `griselda.owusu`
   - Password: `Jytc1101$`
3. Click "New" to create database
4. Database name: `buildmate_db`
5. Collation: `utf8mb4_unicode_ci`
6. Click "Create"

## Step 7: Import Database

1. In phpMyAdmin, select `buildmate_db` database
2. Click "Import" tab
3. Choose file: `db/complete_database.sql`
4. Click "Go"

**Then import additional migrations (in order):**
1. `db/add_premium_system.sql`
2. `db/add_ad_payment_reference.sql`
3. `db/add_sentiment_to_reviews.sql`
4. `db/add_reviews_waitlist.sql`

## Step 8: Set Permissions

```bash
cd ~/public_html/build_mate
chmod -R 755 storage
chmod -R 755 vendor
chmod 644 .env
```

## Step 9: Test the Application

Visit: http://169.239.251.102:442/~griselda.owusu/build_mate

**Default Admin Login:**
- Email: `admin@buildmate.com`
- Password: `Admin123`

## Step 10: Future Updates

To pull latest changes from GitHub:

```bash
cd ~/public_html/build_mate
git pull origin main
```

## Troubleshooting

### If you get 500 errors:
- Check `.env` file exists and has correct values
- Check database connection in `.env`
- Check file permissions: `chmod -R 755 storage`

### If assets don't load:
- Check `.htaccess` file exists
- Check Apache mod_rewrite is enabled
- Check file permissions

### If database connection fails:
- Verify MySQL password is correct
- Check database name matches in `.env`
- Verify database exists in phpMyAdmin


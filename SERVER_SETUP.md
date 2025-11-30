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
DB_NAME=ecommerce_2025A_griselda_owusu
DB_USER=griselda.owusu
DB_PASS=Jytc1101$

# Add your API keys
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key
OPENAI_API_KEY=your_openai_key
```

## Step 6: Create Database

**If you CAN create databases in phpMyAdmin:**

1. Go to: http://169.239.251.102:442/phpmyadmin
2. Login with:
   - Username: `griselda.owusu`
   - Password: `Jytc1101$`
3. Click "New" to create database
4. Database name: `buildmate_db`
5. Collation: `utf8mb4_unicode_ci`
6. Click "Create"

**If you CANNOT create databases (permission denied - ERROR 1044):**

This is normal on shared hosting. Your MySQL user doesn't have CREATE DATABASE privileges.

### Option A: Ask Administrator to Create Database (RECOMMENDED)
Contact your administrator (FIs, Lecturers, or CSIS coordinators) and ask them to:
- Create database: `buildmate_db`
- Grant full privileges to user: `griselda.owusu`
- Collation: `utf8mb4_unicode_ci`

**Email/Message Template:**
```
Hello,

I need a MySQL database created for my Build Mate project.

Database Name: buildmate_db
Database User: griselda.owusu
Collation: utf8mb4_unicode_ci
Privileges: ALL (SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX)

Thank you!
```

### Option B: Use Existing Database ✅ (YOUR CASE)
**Your existing database:** `ecommerce_2025A_griselda_owusu`

1. Use this database name in your `.env` file (see Step 5 below)
2. Select this database in phpMyAdmin before importing
3. Import the SQL file into this database

### Option C: Check for Database Creation Tool
Some hosting providers have a control panel (cPanel, Plesk, etc.) where you can create databases. Check:
- http://169.239.251.102:442/ (for control panel)
- Or ask your administrator about database creation tools

## Step 7: Import Database

**IMPORTANT:** Use the file `db/complete_database_no_create.sql` (NOT `complete_database.sql`) because it doesn't try to create the database.

**CRITICAL:** You MUST select a database first before importing!

### How to Select Database in phpMyAdmin:

1. Go to: http://169.239.251.102:442/phpmyadmin
2. Log in with: `griselda.owusu` / `Jytc1101$`
3. **Look at the LEFT SIDEBAR** - you should see a list of databases
4. **Click on a database name** to select it (it will turn blue/highlighted)
   - If `buildmate_db` exists, click it
   - If you see another database (like `griselda_owusu_buildmate` or similar), click that one
   - If NO databases are listed, you need to ask admin to create one first
5. Once a database is selected, click the **"Import"** tab at the top
6. Click "Choose File" and select: `db/complete_database_no_create.sql`
7. Scroll down and click **"Go"** button

**If you see "No database selected" error:**
- You forgot to click a database in the left sidebar first
- Go back and click a database name, then try importing again

**If the file is too large and times out:**
- Try importing in smaller chunks
- Or use SSH to import:
```bash
cd ~/public_html/build_mate
mysql -u griselda.owusu -p'Jytc1101$' ecommerce_2025A_griselda_owusu < db/complete_database_no_create.sql
```

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


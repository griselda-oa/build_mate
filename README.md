# Build Mate Ghana Ltd - E-commerce Platform

A clean, simple PHP 8.2 MVC e-commerce site for construction materials. Runs on localhost with MySQL.

## Features

- **Auth & Roles**: Register, login, logout. Roles: buyer, supplier, logistics, admin
- **Catalog**: List products, search, filter by category and price
- **Cart**: Add, update qty, remove, totals
- **Checkout (Escrow mock)**: Create order, mark as paid_escrow, show success page, generate invoice PDF
- **Orders & Tracking**: 
  - Buyer: see orders; statuses = pending → paid_escrow → in_transit → delivered → completed
  - Logistics: set in_transit and delivered
  - Buyer: "Confirm delivery" sets completed
- **Supplier area**: KYC page (upload docs), add/edit products. Admin can approve supplier → show "Verified" badge
- **Admin area**: Dashboard cards (totals), approve suppliers, manage users/products/orders
- **UI**: Bootstrap 5, clean navbar, footer, mobile-friendly. USD/GHS toggle (simple JS rate)

## Requirements

- PHP 8.2+
- MySQL 8.0+
- Composer
- XAMPP/LAMP/WAMP (for localhost)
- Apache with mod_rewrite enabled

## Installation

### 1. Install Dependencies

```bash
composer require vlucas/phpdotenv dompdf/dompdf
```

### 2. Configure Environment

```bash
cp .env.example .env
# Edit .env with your database credentials
```

Required `.env` variables:
- `DB_HOST` (default: localhost)
- `DB_PORT` (default: 3306)
- `DB_NAME` (default: buildmate_db)
- `DB_USER` (default: root)
- `DB_PASS` (your MySQL password)
- `APP_URL` (e.g., `http://localhost/buildmate`)

### 3. Create Database

```bash
mysql -u root -p < database/migrations.sql
mysql -u root -p < database/seed.sql
```

Or via phpMyAdmin:
1. Import `database/migrations.sql`
2. Import `database/seed.sql`

### 4. Configure Web Server

#### Apache (.htaccess already configured)

Point document root to `/public` or set up virtual host:

```apache
<VirtualHost *:80>
    ServerName buildmate.local
    DocumentRoot /path/to/build_mate/public
    <Directory /path/to/build_mate/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Or simply place project in your webroot and access via:
```
http://localhost/build_mate/
```

### 5. Access the Site

Visit: `http://localhost/build_mate/`

**Default Admin Login:**
- Email: `admin@buildmate.com`
- Password: `Admin123`

**Note**: Only the seeded admin account (`admin@buildmate.com`) can access `/admin/*` routes.

## Project Structure

```
buildmate/
├── app/
│   ├── Controllers/     # Auth, Product, Cart, Order, Supplier, Logistics, Admin
│   ├── Models/          # User, Product, Category, Order, OrderItem, Supplier, Delivery, Invoice
│   ├── Core/            # Router, Controller, Model, View, DB, Auth, Csrf, Middleware
│   ├── Views/           # layouts, auth, home, product, cart, order, supplier, logistics, admin
│   ├── Middleware/      # Auth, Role, CSRF, RateLimit
│   └── Helpers/         # Validator, Security, Money, etc.
├── public/              # index.php, assets/, .htaccess
├── database/            # migrations.sql, seed.sql
├── vendor/              # Composer dependencies
├── .env.example
├── composer.json
└── README.md
```

## Routes

### Public
- `/` - Homepage
- `/catalog` - Product catalog
- `/product/{id}` - Product detail

### Auth
- `/login` - Login page
- `/register` - Registration page
- `/logout` - Logout

### Cart
- `/cart` - View cart
- `/cart/add/{id}` - Add to cart
- `/cart/update` - Update quantity
- `/cart/remove/{id}` - Remove from cart

### Checkout
- `/checkout` - Checkout page
- `/checkout/confirm` - Confirm order (sets paid_escrow, generates invoice PDF)

### Buyer
- `/orders` - My orders
- `/orders/{id}` - Order details
- `/orders/{id}/confirm-delivery` - Confirm delivery (sets completed)

### Supplier
- `/supplier/dashboard` - Supplier dashboard
- `/supplier/kyc` - KYC submission page
- `/supplier/products` - Product CRUD
- `/supplier/orders` - Supplier orders

### Logistics
- `/logistics/dashboard` - Logistics dashboard
- `/logistics/assignments` - Delivery assignments

### Admin
- `/admin/dashboard` - Admin dashboard
- `/admin/suppliers` - Approve suppliers
- `/admin/users` - Manage users
- `/admin/products` - Manage products
- `/admin/orders` - Manage orders

## Security Features

- Password hashing (`password_hash`/`password_verify`)
- CSRF tokens on all POST forms
- Prepared statements (PDO) for all DB queries
- Output escaping in views (`View::e()`)
- Session: regenerate ID on login; HttpOnly cookie; idle timeout
- File uploads (KYC): jpg/png/pdf only, size limit, random filename
- Admin access restricted to `admin@buildmate.com` only

## Database Schema

Simple schema with:
- `users` (id, name, email, password_hash, role, created_at)
- `suppliers` (id, user_id, business_name, kyc_status, verified_badge)
- `categories` (id, name)
- `products` (id, supplier_id, category_id, name, price_cents, currency, stock, verified)
- `orders` (id, buyer_id, status, total_cents, currency, created_at)
- `order_items` (id, order_id, product_id, qty, price_cents)
- `deliveries` (id, order_id, logistics_id, status)
- `invoices` (id, order_id, invoice_no, pdf_path)

## Seed Data

- Admin: `admin@buildmate.com` / `Admin123`
- 3 buyers
- 2-3 suppliers
- 6 categories
- ~20 products
- 2 logistics users
- Sample orders

## Development

### Code Standards
- PSR-12 coding standard
- Strict MVC architecture
- Clean, readable code
- Well-commented

### Testing
Test the following flows:
1. Register → Login → Browse catalog
2. Add to cart → Checkout → Confirm order
3. Admin: Approve supplier → Verify badge shows
4. Logistics: Update delivery status
5. Buyer: Confirm delivery → Order completed

## License

This is a project for educational purposes.

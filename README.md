# Second-Hand Marketplace

A full-stack web application to buy and sell items like, electronics, clothing, books, vinyl, collectibles, and more.

Stack: HTML5 · CSS/Bootstrap 5 · JavaScript · PHP · MySQL · XAMPP


Features

- User registration, login, logout
- Browse, search, and filter products by category and condition
- Product detail pages with stock indicator
- Shopping cart with add, remove, and quantity tracking
- Checkout with order placement and stock auto-decrement
- Order history with breakdown of order info
- Seller dashboard, to list items, upload images, manage stock
- Admin dashboard, to manage all products and monitor orders
- Client-side validation (JS) and server-side validation (PHP)
- SQL injection protection via prepared statements
- Secure password hashing with `password_hash`


Setup

1. Place the project folder in `C:\xampp\htdocs\second-marketplace\`
2. Start Apache and MySQL in XAMPP Control Panel
3. Open `http://localhost/phpmyadmin` → create database `secondhand_marketplace`
4. Import `secondhand_marketplace.sql` via the Import tab
5. Verify `includes/db.php` credentials (default: host `localhost`, user `root`, no password)
6. Create the uploads folder if it doesn't exist: `images/uploads/`
7. Visit `http://127.0.0.1/second-marketplace/`

**To set admin access:**
```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```


---
Database Schema

users        (id, name, email, password, is_admin)

products     (id, name, description, price, image_url, category, condition_of_product, stock, seller_id)

cart         (id, user_id, product_id, quantity)

orders       (id, user_id, total_price, order_date, status)

order_items  (id, order_id, product_id, quantity, price_at_purchase)

## Project Structure

```
second-marketplace/
├── admin/              => dashboard.php, manage_products.php
├── css/                => bootstrap.css.map, bootstrap.css, style.css 
├── images/uploads/     => uploaded product images
├── includes/           => db.php, header.php, footer.php
├── js/                 => bootstrap.bundle.min.js, validation.js
├── scss/               => bootstrap.scss
├── index.php           
├── products.php        
├── product.php         
├── cart.php            
├── checkout.php        
├── sell.php            
├── my_listings.php     
├── order_history.php   
├── login.php
├── register.php
├── logout.php
└── secondhand_marketplace.sql ==> SQL FILE 
```

# Project Video Demo Link

https://youtu.be/_Sge3S8YXlA

---

Student Info.

- Kazura Kenzo. 

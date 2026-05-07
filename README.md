# 🛒 Second-Hand Marketplace

A full-stack buy-and-sell web application for electronics, clothing, books, vinyl, collectibles, and more. Built with a PHP/MySQL backend and Bootstrap 5 frontend.

> 📺 **[Watch the demo video →](https://youtu.be/_Sge3S8YXlA)**

---

## ✨ Features

**Buyers**
- Browse, search, and filter by category and condition
- Product detail pages with live stock indicators
- Shopping cart with add/remove/quantity tracking
- Checkout with automatic stock decrement
- Order history with full item breakdown

**Sellers**
- Dashboard to list items, upload images, and manage stock
- Per-listing management with edit and delete controls

**Admin**
- Full product oversight and order monitoring via admin dashboard

**Security**
- SQL injection protection via PDO prepared statements
- Passwords hashed with PHP `password_hash()`
- Client-side (JS) and server-side (PHP) validation

---

## 🖥 Demo

> ⚠️ **Live deployment not available** — this project runs locally via XAMPP due to PHP/MySQL hosting requirements. A full walkthrough is available in the video below.

[![Demo Video](https://img.shields.io/badge/▶%20Watch%20Demo-red?style=for-the-badge&logo=youtube&logoColor=white)](https://youtu.be/_Sge3S8YXlA)

**Test credentials (after local setup):**

| Role | Email | Password |
|------|-------|----------|
| Buyer | `buyer@test.com` | `password123` |
| Seller | `seller@test.com` | `password123` |
| Admin | set via SQL (see setup) | — |

---

## 🗃 Database Schema

```
users         (id, name, email, password, is_admin)
products      (id, name, description, price, image_url, category, condition_of_product, stock, seller_id)
cart          (id, user_id, product_id, quantity)
orders        (id, user_id, total_price, order_date, status)
order_items   (id, order_id, product_id, quantity, price_at_purchase)
```

---

## 🚀 Local Setup

**Requirements:** [XAMPP](https://www.apachefriends.org/) (Apache + MySQL)

```bash
# 1. Clone the repo into your XAMPP htdocs folder
git clone https://github.com/kenzokaz/second-marketplace.git C:/xampp/htdocs/second-marketplace

# 2. Start Apache and MySQL in XAMPP Control Panel

# 3. Create the database
#    Open http://localhost/phpmyadmin
#    Create a database named: secondhand_marketplace
#    Import: secondhand_marketplace.sql

# 4. Verify DB credentials in includes/db.php
#    Default: host=localhost, user=root, password=(empty)

# 5. Create the uploads folder if missing
mkdir C:/xampp/htdocs/second-marketplace/images/uploads

# 6. Visit the app
open http://127.0.0.1/second-marketplace/
```

**To grant admin access:**
```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

---

## 📁 Project Structure

```
second-marketplace/
├── admin/              # Admin dashboard & product management
├── css/                # Bootstrap + custom styles
├── images/uploads/     # Uploaded product images
├── includes/           # db.php, header.php, footer.php
├── js/                 # Bootstrap bundle + validation
├── scss/               # Bootstrap source
├── index.php           # Homepage / product listing
├── products.php        # Browse & filter
├── product.php         # Product detail page
├── cart.php            # Shopping cart
├── checkout.php        # Order placement
├── sell.php            # Seller listing form
├── my_listings.php     # Seller dashboard
├── order_history.php   # Buyer order history
├── login.php / register.php / logout.php
└── secondhand_marketplace.sql
```

---

## 🛠 Stack

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap%205-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)

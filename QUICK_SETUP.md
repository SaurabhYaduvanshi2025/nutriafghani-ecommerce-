# 🚀 Quick Setup Guide - Add Product Form

## Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP/LAMP/LEMP with Apache running
- Modern web browser

---

## Step 1: Database Setup (5 minutes)

### Option A: Using phpMyAdmin (Recommended)

1. Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
2. Select the `nutriafghan` database
3. Click on the "SQL" tab
4. Open the file: `config/schema.sql`
5. Copy **entire** SQL content
6. Paste into phpMyAdmin SQL editor
7. Click "Go" button

### Option B: Using MySQL Command Line

```bash
cd C:\xampp\mysql\bin
mysql -u root -p nutriafghan < "C:\xampp\htdocs\nutriafghan\config\schema.sql"
```

### Verify Installation
In phpMyAdmin, you should see these tables:
- ✅ `categories` (with 5 sample categories)
- ✅ `products` (with 1 sample product)
- ✅ `product_variants` (with weight variants)

---

## Step 2: File Setup (2 minutes)

### 1. Create Uploads Directory
```
C:\xampp\htdocs\nutriafghan\
└── uploads/
    └── products/
```

Create these folders manually or they'll be created automatically on first upload.

### 2. Set Directory Permissions
Right-click `uploads` folder → Properties → Security → Edit:
- ✅ Full Control (for XAMPP user)
- ✅ Modify permissions

**On Linux:**
```bash
chmod 755 uploads
chmod 755 uploads/products
```

### 3. Verify Files Exist
Check these files are in place:
- ✅ `admin/add-product.php`
- ✅ `admin/process-add-product.php`
- ✅ `config/db.php`
- ✅ `config/schema.sql`

---

## Step 3: Verify Database Connection (2 minutes)

Check `config/db.php` credentials match your setup:

```php
define('DB_HOST', 'localhost');    // Default for XAMPP
define('DB_USER', 'root');         // Default for XAMPP
define('DB_PASS', '');             // Empty for XAMPP
define('DB_NAME', 'nutriafghan');  // Your database name
```

---

## Step 4: Test the Form (5 minutes)

1. **Start XAMPP**
   - Start Apache
   - Start MySQL

2. **Access the form**
   - URL: `http://localhost/nutriafghan/admin/add-product.php`
   - (You may need to login first via admin/login.php)

3. **Try adding a product**
   - Product Name: "Test Saffron"
   - Category: Select "Spices"
   - Description: "Test product description"
   - Original Price: 1000
   - Discount Price: 800
   - Select weight: Click "500g" button
   - Prices should update dynamically ✅
   - Upload a test image
   - Click "Add Product"

4. **Verify success**
   - You should see success message
   - Should redirect to product-list.php
   - Check database: new product should appear in products table

---

## Step 5: Key Features to Test

### ✅ Feature 1: Weight Options
- Click different weight buttons
- Observe price updates dynamically
- Prices should match formula: `base_price × multiplier`

### ✅ Feature 2: Quantity Selector
- Click `+` button to increase quantity
- Click `-` button to decrease quantity
- Final price should update correctly
- Example: 500g @ 800 discount × 2 qty = 1600

### ✅ Feature 3: Category Integration
- Select a category
- Slug should appear below dropdown
- Try different categories
- All should be SEO-friendly slugs

### ✅ Feature 4: Real-time Validation
- Try submitting with empty fields
- Try entering price less than discount
- Try uploading large files (>5MB)
- All should show error messages

### ✅ Feature 5: Image Upload
- Upload images in JPEG/PNG format
- Check `uploads/products/2026/04/` folder
- Images should be there with unique names

---

## Database Schema Overview

```
┌─────────────┐
│ CATEGORIES  │
├─────────────┤
│ id (PK)     │
│ name        │
│ slug ✨     │  ← SEO-friendly
│ description │
├─────────────┘
│
├────────┐
│        └──────────────────────────┐
│                                   │
│   ┌──────────────────────┐        │
│   │   PRODUCTS           │        │
│   ├──────────────────────┤        │
│   │ id (PK)              │        │
│   │ category_id (FK)─────┘        │
│   │ category_slug ✨              │ ← Links to category
│   │ name                 │        │
│   │ slug ✨              │ ← SEO  │
│   │ original_price       │        │
│   │ discount_price       │        │
│   │ images               │        │
│   │ sku                  │        │
│   │ ...                  │        │
│   ├──────────────────────┤        │
│   │                      │        │
│   └──────┬───────────────┘        │
│          │                        │
│   ┌──────v────────────────────┐   │
│   │ PRODUCT_VARIANTS          │   │
│   ├───────────────────────────┤   │
│   │ id (PK)                   │   │
│   │ product_id (FK)───────────┘   │
│   │ weight_label (250g, 500g, 1kg)│
│   │ weight_grams              │   │
│   │ variant_price             │   │
│   │ variant_discount_price    │   │
│   │ variant_sku               │   │
│   └───────────────────────────┘   │
│                                   │
└───────────────────────────────────┘
```

---

## File Structure

```
nutriafghan/
├── admin/
│   ├── add-product.php              ← Frontend form
│   ├── process-add-product.php       ← Backend processor
│   ├── product-list.php
│   ├── auth.php                     ← Authentication
│   ├── includes/
│   │   ├── header-link.php
│   │   ├── sidebar.php
│   │   └── ...
│   └── ...
├── config/
│   ├── db.php                       ← Database config
│   └── schema.sql                   ← Setup script
├── uploads/
│   └── products/                    ← Product images
│       └── 2026/
│           └── 04/
│               └── [images here]
├── css/
├── js/
├── includes/
└── ADD_PRODUCT_DOCUMENTATION.md     ← Full documentation
```

---

## Troubleshooting

### Issue: "Database connection failed"
**Solution:**
- Ensure MySQL is running in XAMPP
- Check DB credentials in `config/db.php`
- Verify database `nutriafghan` exists

### Issue: "No categories appear in dropdown"
**Solution:**
- Run schema.sql to insert sample categories
- Check categories table: `SELECT * FROM categories`

### Issue: "Images won't upload"
**Solution:**
- Create `uploads/products/` directory
- Check folder permissions (755)
- Verify file size < 5MB
- Ensure file is JPG/PNG

### Issue: "Weight buttons not working"
**Solution:**
- Open browser console (F12)
- Check for JavaScript errors
- Ensure JavaScript is enabled
- Clear browser cache and refresh

### Issue: "Form submits but redirects incorrectly"
**Solution:**
- Check `product-list.php` exists
- Verify admin authentication is working
- Check JavaScript console for errors

### Issue: "Products not saving to database"
**Solution:**
- Run schema.sql to create tables
- Check file `process-add-product.php` exists
- Check MySQL error log
- Verify table structures match schema

---

## Testing Checklist

- [ ] Database tables created
- [ ] `uploads/products/` directory exists
- [ ] Can access `add-product.php` without errors
- [ ] Categories appear in dropdown
- [ ] Weight buttons respond to clicks
- [ ] Prices update dynamically
- [ ] Form validates (reject invalid inputs)
- [ ] Images upload successfully
- [ ] Product saves to database
- [ ] Variant data created
- [ ] Can view new product in product-list.php

---

## Next Steps

1. **Customize weight options**
   - Edit multipliers in `add-product.php`
   - Change button labels and values
   - Update schema.sql sample data

2. **Customize pricing rules**
   - Modify price calculation formula
   - Add bulk discounts
   - Implement tiered pricing

3. **Add more features**
   - Multiple weight options
   - Bundle discounts
   - Subscription pricing
   - Pre-order options

4. **SEO optimization**
   - Add meta descriptions
   - Optimize slug generation
   - Add canonical URLs
   - Implement schema.org markup

5. **Front-end integration**
   - Display products with variants
   - Show price for selected weight
   - Implement add-to-cart with variants
   - Display product reviews

---

## Support & Resources

**Files to Reference:**
- Database Schema: `config/schema.sql`
- Full Documentation: `ADD_PRODUCT_DOCUMENTATION.md`
- Form Code: `admin/add-product.php`
- Backend Code: `admin/process-add-product.php`

**Common Queries:**
- View all products: `SELECT * FROM products`
- View categories: `SELECT * FROM categories`
- View variants: `SELECT * FROM product_variants`
- Check uploads: `uploads/products/` folder

---

## Success Indicators ✅

After setup, you should see:
1. ✅ Add Product form loads without errors
2. ✅ Categories dropdown populated
3. ✅ Weight buttons are clickable
4. ✅ Prices update dynamically on weight selection
5. ✅ Form validates before submission
6. ✅ Images upload successfully
7. ✅ New products appear in product list
8. ✅ Database contains product variants
9. ✅ Category slug appears in product record
10. ✅ URLs are SEO-friendly

---

**Estimated Total Setup Time: 15 minutes**

Once setup is complete, the system is ready for production use!

# Add Product Form - Complete Implementation Guide

## Overview
This is a comprehensive "Add Product" system for the Nutri Afghan e-commerce platform with:
- Dynamic pricing based on weight selection
- Quantity selector with real-time price calculation
- Category and slug integration
- Image upload handling
- AJAX form submission
- Complete database structure with variants

---

## 🗄️ Database Structure

### 1. **CATEGORIES TABLE**
Stores product categories with SEO-friendly slugs.

```sql
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `image` VARCHAR(255),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Key Features:**
- Unique slug for SEO-friendly URLs
- `is_active` flag for managing visibility
- Timestamps for auditing

---

### 2. **PRODUCTS TABLE**
Main products table storing general product information.

```sql
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `category_slug` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `short_description` VARCHAR(500),
    `original_price` DECIMAL(10, 2),
    `discount_price` DECIMAL(10, 2),
    `discount_percentage` INT DEFAULT 0,
    `main_image` VARCHAR(255),
    `gallery_image_1` VARCHAR(255),
    `gallery_image_2` VARCHAR(255),
    `gallery_image_3` VARCHAR(255),
    `sku` VARCHAR(100) UNIQUE,
    `stock_quantity` INT DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
);
```

**Key Features:**
- `category_slug` for direct mapping without ID lookups
- Unique slug for product URLs
- Multiple image fields (main + 3 gallery images)
- Discount percentage calculated automatically
- SKU for inventory tracking

---

### 3. **PRODUCT_VARIANTS TABLE**
Stores weight/size options with individual pricing.

```sql
CREATE TABLE `product_variants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `weight_label` VARCHAR(50) NOT NULL,
    `weight_value` VARCHAR(50) NOT NULL,
    `weight_grams` INT,
    `variant_price` DECIMAL(10, 2) NOT NULL,
    `variant_discount_price` DECIMAL(10, 2),
    `variant_sku` VARCHAR(100) UNIQUE,
    `stock_quantity` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_variant` (`product_id`, `weight_label`)
);
```

**Key Features:**
- One-to-many relationship with products
- Individual pricing per weight
- Weight values in grams for consistency
- Unique variant SKU for inventory tracking
- `unique_variant` constraint prevents duplicate weights

---

## 📝 Setup Instructions

### Step 1: Import Database Schema
1. Open phpMyAdmin
2. Select your `nutriafghan` database
3. Go to SQL tab
4. Copy the content from [config/schema.sql](../config/schema.sql)
5. Execute the SQL

### Step 2: Verify Directory Structure
Create the uploads directory for images:
```
uploads/
└── products/
    └── (Will auto-create with Year/Month subdirectories)
```

### Step 3: Files Included
- **admin/add-product.php** - Frontend form with AJAX
- **admin/process-add-product.php** - Backend processor
- **config/schema.sql** - Database schema and sample data

---

## 🎨 Frontend Form Features

### Form Sections

#### **1. Basic Information**
- Product Name (255 char max)
- Category Selection (dynamic from database)
- Description (1000 char max)
- Short Description (500 char max)

#### **2. Pricing & Weight Options**
- Original Price (base price)
- Discount Price (sale price)
- Weight Selection Buttons:
  - 250g (0.5x price multiplier)
  - 500g (1x price multiplier)
  - 1kg (2x price multiplier)

#### **3. Price Display Section** (Dynamic)
Updates in real-time when weight is selected:
- Original price for selected weight
- Discount price for selected weight
- Savings amount
- Discount percentage badge
- Quantity selector (+/-)
- Final price calculation

#### **4. Image Upload**
- Main Image (required)
- Gallery Image 1-3 (optional)
- Max 5MB per image
- Supported formats: JPG, PNG, WebP

#### **5. Additional Information**
- SKU (unique identifier)
- Stock Quantity
- Featured Product checkbox

---

## ⚙️ JavaScript/AJAX Logic

### Weight Selection Handler
```javascript
// Clicking a weight button:
1. Sets active state (visual feedback)
2. Stores weight data (weight, grams, multiplier)
3. Updates price display with calculations
```

### Dynamic Price Calculation
```javascript
variantPrice = basePrice × weightMultiplier
// Example: 1000 × 0.5 = 500 for 250g option
```

### Quantity Selector
- Plus/Minus buttons
- Direct input (min: 1, max: 999)
- Calculates final total price

### Form Validation
- Required fields check
- Price logic validation
- File size verification
- Weight selection confirmation
- Prevents duplicate SKUs

### AJAX Submission
```javascript
fetch('process-add-product.php', {
    method: 'POST',
    body: formData (includes files)
})
// Redirects to product-list.php on success
```

---

## 🔧 Backend Processing

### File: process-add-product.php

#### Input Validation
✓ Product name (length check)
✓ Category existence verification
✓ Price logic (discount < original)
✓ SKU uniqueness
✓ Stock quantity validation
✓ File uploads (MIME type, size)

#### Database Operations
1. **Verify category** - Check if category exists and is active
2. **Validate inputs** - All form data validation
3. **Upload images** - Save to `uploads/products/YYYY/MM/` directory
4. **Generate slug** - Creates SEO-friendly product slug, ensures uniqueness
5. **Insert product** - Adds to products table
6. **Insert variant** - Creates weight option with calculated pricing

#### Response Format
```json
{
    "success": true,
    "message": "Product 'Name' has been added successfully!",
    "product_id": 123,
    "product_slug": "product-name"
}
```

---

## 📊 Category & Slug Integration

### How Slug Logic Works

**Category Selection:**
```php
// When category is selected, slug is fetched from database
<select id="categorySelect" name="category_id">
    <option data-slug="<?php echo $category['slug']; ?>">
        <?php echo $category['name']; ?>
    </option>
</select>
```

**Product Slug Generation:**
```php
// Automatic slug creation from product name
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// Ensures uniqueness by appending counter if needed
// Example: "Saffron" → "premium-saffron-1"
```

**Database Storage:**
Both `category_slug` and `product_slug` are stored for:
- SEO-friendly URLs: `/shop/spices/premium-saffron`
- Direct database queries without JOIN on IDs
- Faster category filtering

---

## 🖼️ Image Upload Details

### Upload Directory Structure
```
uploads/
└── products/
    └── 2026/
        └── 04/
            ├── product_xyz123_1609459200.jpg
            ├── product_abc456_1609459201.jpg
            └── ...
```

### Validation
- **File Types:** JPG, PNG, WebP (MIME type verified)
- **File Size:** Max 5MB per image
- **Directory:** Auto-created with YYYY/MM structure
- **Naming:** Unique ID + timestamp to prevent collisions

### Database Storage
- Paths stored relative to uploads directory
- Example: `products/2026/04/product_xyz123_1609459200.jpg`
- Easy to regenerate full paths in frontend

---

## 💾 Sample Data

### Pre-loaded Categories
```sql
-- Spices (slug: spices)
-- Dry Fruits (slug: dry-fruits)
-- Grains (slug: grains)
-- Herbs (slug: herbs)
-- Oils (slug: oils)
```

### Sample Product Creation
The schema includes a sample "Premium Saffron" product with three weight variants to demonstrate the system.

---

## 🚀 Usage Workflow

### For Admin Users

1. **Navigate to:** Admin Dashboard → Products → Add Product
2. **Fill Basic Information:**
   - Enter product name
   - Select category (slug auto-displays)
   - Add description and short description

3. **Set Pricing:**
   - Enter original price
   - Enter discount price
   - Select a weight option to see calculated prices

4. **Select Weight Option:**
   - Click 250g, 500g, or 1kg button
   - Prices update dynamically
   - Adjust quantity using +/- buttons

5. **Upload Images:**
   - Main image (required)
   - Up to 3 gallery images (optional)

6. **Additional Info:**
   - Enter SKU (optional but recommended)
   - Set stock quantity
   - Mark as featured if needed

7. **Submit:**
   - Click "Add Product"
   - Form validates client-side
   - AJAX submits to backend
   - Redirects to product list on success

### For Frontend Users

The variant data allows:
- **Weight Selection:** Different sizes available
- **Dynamic Pricing:** Prices change based on weight
- **Quantity Control:** Easy adjustment
- **SEO Navigation:** Products found via category slugs

---

## 🔐 Security Features

✓ **SQL Injection Prevention:** Prepared statements used throughout
✓ **File Upload Security:** MIME type verification, file size limits
✓ **Input Sanitization:** All inputs trimmed and validated
✓ **CSRF Protection:** Works with existing auth.php system
✓ **Error Handling:** Generic messages shown to users, detailed logs internally
✓ **Database Constraints:** Unique slugs, unique SKUs, foreign keys

---

## 🐛 Troubleshooting

### Issue: "Database connection failed"
- Check [config/db.php](../config/db.php) credentials
- Verify MySQL is running
- Check database exists: `nutriafghan`

### Issue: Categories not loading
- Run schema.sql to insert sample categories
- Check categories table has data
- Verify `is_active = 1`

### Issue: Images not uploading
- Check `uploads/` directory exists and is writable
- Verify file size < 5MB
- Check file format is JPG/PNG/WebP

### Issue: "SKU already exists"
- Each product needs unique SKU
- Use different SKU or leave blank
- Check for typos in existing SKUs

### Issue: Slug not unique
- System auto-appends numbers (e.g., `product-name-1`)
- Verify product name is different
- Check products table for duplicates

---

## 📚 Related Files

- **Database Config:** [config/db.php](../config/db.php)
- **Authentication:** [admin/auth.php](auth.php)
- **Product List:** [admin/product-list.php](product-list.php)
- **Schema File:** [config/schema.sql](../config/schema.sql)

---

## 🎯 Key Features Summary

| Feature | Implementation |
|---------|-----------------|
| Weight Options | 3 buttons (250g, 500g, 1kg) with price multipliers |
| Price Calculation | Real-time updates based on weight selection |
| Quantity Selector | +/- buttons with direct input |
| Category Integration | Dropdown fetched from database with slug mapping |
| Slug Management | Auto-generated, unique, and SEO-friendly |
| Image Upload | MIME verification, size limits, organized storage |
| Form Validation | Client-side (JavaScript) + Server-side (PHP) |
| Database Structure | Normalized tables with proper relationships |
| AJAX Submission | No page reload, JSON response handling |
| Error Handling | User-friendly messages with detailed logging |

---

## 📞 Support

For issues or customization:
1. Check logs in PHP error log
2. Verify database schema is correct
3. Ensure uploads directory is writable
4. Test with sample category first
5. Review JavaScript console for frontend errors

---

**Last Updated:** April 30, 2026
**Version:** 1.0
**Compatibility:** PHP 7.4+, MySQL 5.7+, Modern Browsers

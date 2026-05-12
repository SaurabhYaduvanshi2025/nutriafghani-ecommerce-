# 🧪 Add Product Form - Testing Guide

## Overview
Comprehensive testing guide for the Add Product form system. Test each feature systematically to ensure everything works correctly.

---

## ✅ Pre-Testing Checklist

Before starting tests, verify:
- [ ] MySQL is running
- [ ] Apache is running in XAMPP
- [ ] Database schema imported (schema.sql)
- [ ] `uploads/products/` directory exists and is writable
- [ ] Can access `http://localhost/nutriafghan/admin/add-product.php`
- [ ] Browser developer tools available (F12)

---

## 🧪 Test Suite 1: Form Rendering

### Test 1.1: Page Loads Without Errors
```
Steps:
1. Navigate to http://localhost/nutriafghan/admin/add-product.php
2. Open browser console (F12 → Console)
3. Check for any JavaScript errors

Expected:
✅ Page loads completely
✅ No console errors
✅ All form sections visible
✅ Categories dropdown populated
✅ Weight buttons visible
```

### Test 1.2: Categories Display Correctly
```
Steps:
1. Check the category dropdown
2. Click to expand dropdown

Expected:
✅ All 5 sample categories visible:
   - Spices
   - Dry Fruits
   - Grains
   - Herbs
   - Oils
✅ Dropdown shows "Choose category" as default
```

### Test 1.3: Weight Buttons Display
```
Steps:
1. Scroll to "Select Weight Options" section
2. Observe the buttons

Expected:
✅ 3 buttons visible: 250g, 500g, 1kg
✅ Buttons have white background initially
✅ Buttons are clickable
```

### Test 1.4: Form Sections Are Organized
```
Steps:
1. Scroll through entire form

Expected:
✅ Basic Information section
✅ Pricing & Weight Options section
✅ Upload Images section
✅ Additional Information section
✅ Submit button at bottom
```

---

## 🧪 Test Suite 2: Weight Selection & Dynamic Pricing

### Test 2.1: Click Weight Buttons
```
Steps:
1. Click "250g" button
2. Observe visual change

Expected:
✅ Button background turns green (#7cb342)
✅ Text color turns white
✅ Price display section appears
```

### Test 2.2: Price Display Section Shows
```
Prerequisites: Select 250g weight

Steps:
1. After clicking 250g, scroll down
2. Look for price display section

Expected:
✅ Section titled "Selected Weight: 250g"
✅ Original Price row
✅ Discount Price row
✅ You Save row (in red)
✅ Discount % badge
✅ Quantity selector
✅ Variant info box
```

### Test 2.3: Price Calculation for 250g
```
Prerequisites: 
- Original Price: 1000
- Discount Price: 800
- Selected Weight: 250g

Steps:
1. Enter Original Price: 1000
2. Enter Discount Price: 800
3. Click 250g button

Expected Calculation:
✅ Original Price for 250g: 1000 × 0.5 = 500
✅ Discount Price for 250g: 800 × 0.5 = 400
✅ You Save: 500 - 400 = 100
✅ Discount %: (1000-800)/1000 × 100 = 20%
```

### Test 2.4: Price Calculation for 500g
```
Prerequisites:
- Original Price: 1000
- Discount Price: 800
- Selected Weight: 500g

Steps:
1. Keep prices same
2. Click 500g button

Expected Calculation:
✅ Original Price for 500g: 1000 × 1 = 1000
✅ Discount Price for 500g: 800 × 1 = 800
✅ You Save: 1000 - 800 = 200
✅ Discount %: 20% (same as before)
```

### Test 2.5: Price Calculation for 1kg
```
Prerequisites:
- Original Price: 1000
- Discount Price: 800
- Selected Weight: 1kg

Steps:
1. Keep prices same
2. Click 1kg button

Expected Calculation:
✅ Original Price for 1kg: 1000 × 2 = 2000
✅ Discount Price for 1kg: 800 × 2 = 1600
✅ You Save: 2000 - 1600 = 400
✅ Discount %: 20% (same as before)
```

### Test 2.6: Real-time Price Updates
```
Steps:
1. Click 500g button
2. Change Original Price to 2000
3. Observe price updates

Expected:
✅ Prices update immediately
✅ Original Price for 500g: 2000 × 1 = 2000
✅ If discount price unchanged:
   Discount Price for 500g: updates accordingly
```

### Test 2.7: Switch Between Weights
```
Steps:
1. Click 250g button (observe prices)
2. Click 500g button (observe prices)
3. Click 1kg button (observe prices)
4. Click 500g again

Expected:
✅ Prices recalculate for each weight
✅ Button highlight changes
✅ Variant info updates
✅ Switching back shows correct prices again
```

---

## 🧪 Test Suite 3: Quantity Selector

### Test 3.1: Increment Button
```
Prerequisites:
- Select any weight option
- Initial quantity: 1

Steps:
1. Click + button multiple times
2. Observe quantity value

Expected:
✅ Quantity increases: 1 → 2 → 3 → ...
✅ Final price updates: multiplied by quantity
✅ Max limit: 999 (won't go higher)
```

### Test 3.2: Decrement Button
```
Prerequisites:
- Quantity is 5
- Weight selected

Steps:
1. Click - button multiple times
2. Observe quantity value

Expected:
✅ Quantity decreases: 5 → 4 → 3 → ...
✅ Min limit: 1 (won't go lower)
✅ Final price updates correctly
```

### Test 3.3: Direct Input
```
Prerequisites:
- Weight selected (e.g., 500g)
- Original Price: 1000, Discount: 800

Steps:
1. Click in quantity field
2. Clear current value
3. Type "5"
4. Press Enter

Expected:
✅ Quantity changes to 5
✅ Final Price: 800 × 1 × 5 = 4000
✅ Value locked between 1-999
```

### Test 3.4: Invalid Quantity Input
```
Steps:
1. Try typing "0" in quantity
2. Try typing "1000" in quantity
3. Try typing "-5" in quantity
4. Try typing "abc"

Expected:
✅ Values < 1 corrected to 1
✅ Values > 999 capped at 999
✅ Non-numeric values handled gracefully
```

### Test 3.5: Quantity × Weight × Price
```
Test Case: 
- Weight: 500g (1x multiplier)
- Original: 1000, Discount: 800
- Quantity: 3

Steps:
1. Set up the above values
2. Check final price display

Expected Calculation:
✅ Variant Price = 800 × 1 = 800
✅ Final Price = 800 × 3 = 2400
✅ Display shows: "Final Price: Rs. 2400"
```

---

## 🧪 Test Suite 4: Category & Slug Integration

### Test 4.1: Category Selection
```
Steps:
1. Click category dropdown
2. Select "Spices"

Expected:
✅ "Spices" appears as selected
✅ Slug info box appears below dropdown
✅ Slug shows: "spices"
```

### Test 4.2: View Different Category Slugs
```
Steps:
1. Select "Dry Fruits"
   Expected slug: "dry-fruits"
2. Select "Grains"
   Expected slug: "grains"
3. Select "Herbs"
   Expected slug: "herbs"
4. Select "Oils"
   Expected slug: "oils"

Expected:
✅ Each category shows correct slug
✅ Slugs are SEO-friendly (lowercase, hyphens)
```

### Test 4.3: Category Info Box Visibility
```
Steps:
1. Default page load (no category selected)
   Observe: Category info box
2. Select a category
   Observe: Category info box
3. Click "Choose category" again
   Observe: Category info box

Expected:
✅ Initially hidden (blue box not visible)
✅ Shows when category selected
✅ Hides when "Choose category" selected
```

---

## 🧪 Test Suite 5: Form Validation

### Test 5.1: Product Name Validation
```
Test Cases:

1. Submit with empty name:
   ✅ Error: "Product name is required"

2. Submit with 256+ characters:
   ✅ Error: "Product name exceeds 255 characters"

3. Valid name (50 characters):
   ✅ No error, form proceeds
```

### Test 5.2: Category Validation
```
Test Cases:

1. Submit without selecting category:
   ✅ Error: "Please select a category"

2. Select any category:
   ✅ No error
```

### Test 5.3: Description Validation
```
Test Cases:

1. Submit with empty description:
   ✅ Error: "Description is required"

2. Submit with 1001+ characters:
   ✅ Error: "Description exceeds 1000 characters"

3. Valid description:
   ✅ No error
```

### Test 5.4: Price Validation
```
Test Cases:

1. Original Price = 0:
   ✅ Error: "Original price must be greater than 0"

2. Discount Price = 0:
   ✅ Error: "Discount price must be greater than 0"

3. Discount Price >= Original Price:
   ✅ Error: "Discount price must be less than original price"

Example: Original 100, Discount 100:
   ✅ Shows error
   
4. Valid: Original 1000, Discount 800:
   ✅ No error
```

### Test 5.5: Weight Selection Validation
```
Test Cases:

1. Submit without selecting weight:
   ✅ Error: "Please select at least one weight option"

2. Select any weight:
   ✅ No error
```

### Test 5.6: Image Validation
```
Test Cases:

1. Submit without main image:
   ✅ Error: "Main image is required"

2. Upload image > 5MB:
   ✅ Error: "Main image size must be less than 5MB"

3. Upload valid image (JPG/PNG < 5MB):
   ✅ No error
```

### Test 5.7: Stock Quantity Validation
```
Test Cases:

1. Enter negative stock (-5):
   ✅ Error: "Stock quantity must be 0 or more"

2. Enter valid stock (50):
   ✅ No error

3. Leave empty (defaults to 0):
   ✅ No error
```

---

## 🧪 Test Suite 6: Image Upload

### Test 6.1: Single Image Upload
```
Steps:
1. Click "Main Image" file input
2. Select a JPG file (< 5MB)
3. Verify filename appears in input

Expected:
✅ File name appears next to input
✅ No error message
✅ File successfully uploaded on submit
```

### Test 6.2: Multiple Images Upload
```
Steps:
1. Upload to Main Image
2. Upload to Gallery Image 1
3. Upload to Gallery Image 2
4. Upload to Gallery Image 3

Expected:
✅ All 4 files accepted
✅ No conflicts or errors
✅ All files saved to uploads directory
```

### Test 6.3: Image File Type Validation
```
Test Cases:

Valid formats (should accept):
1. JPG file → ✅ Accepted
2. PNG file → ✅ Accepted
3. WebP file → ✅ Accepted

Invalid formats (should reject):
4. TXT file → ✅ Shows error on submit
5. PDF file → ✅ Shows error on submit
6. GIF file → ✅ Shows error on submit
```

### Test 6.4: Image Size Validation
```
Test Cases:

1. Image = 4MB (< 5MB):
   ✅ Accepted

2. Image = 5.5MB (> 5MB):
   ✅ Error on submit: "size must be less than 5MB"
```

### Test 6.5: Verify Upload Directory
```
Steps:
1. Add a product with images
2. Check directory: uploads/products/2026/04/
3. Look for image files with format: product_xyz123_1609459200.jpg

Expected:
✅ Directory created with current year/month
✅ Image files saved with unique names
✅ File names contain timestamp to prevent collisions
```

---

## 🧪 Test Suite 7: AJAX Form Submission

### Test 7.1: Form Submission Without Errors
```
Steps:
1. Fill all required fields correctly:
   - Product Name: "Premium Saffron"
   - Category: "Spices"
   - Description: "High-quality saffron"
   - Original Price: 5000
   - Discount Price: 4000
   - Select Weight: 500g
   - Upload main image
   - Stock: 50
2. Click "Add Product"
3. Observe page behavior

Expected:
✅ Form submits (no page reload)
✅ Success message appears: "Product... has been added successfully!"
✅ Form resets (clears all fields)
✅ After 2 seconds, redirects to product-list.php
```

### Test 7.2: Form Submission With Errors
```
Steps:
1. Fill form with invalid data:
   - Empty product name
   - No category selected
   - Discount Price > Original Price
   - No weight selected
   - No image uploaded
2. Click "Add Product"

Expected:
✅ Form does NOT submit
✅ Multiple error messages appear in fields
✅ Error messages are clear and helpful
✅ No page redirect
```

### Test 7.3: AJAX Error Handling
```
Steps:
1. Fill form correctly
2. Open browser network tab (F12 → Network)
3. Submit form
4. Watch network request

Expected:
✅ POST request to process-add-product.php
✅ Response code: 200
✅ Response type: application/json
✅ Response contains success: true
```

### Test 7.4: Server-side Error Handling
```
Steps:
1. Try submitting with SKU that already exists
2. (Assuming a product with SKU exists)

Expected:
✅ Form submits to server
✅ Server validation catches duplicate
✅ Error message displayed: "SKU already exists"
✅ Form is NOT reset
✅ User can modify and resubmit
```

---

## 🧪 Test Suite 8: Database Operations

### Test 8.1: Product Inserted Correctly
```
Steps:
1. Add a product successfully
2. Open phpMyAdmin
3. Go to products table
4. Filter for your newly added product

Expected:
✅ Product appears in table
✅ All fields populated:
   - name
   - slug
   - category_id
   - category_slug
   - description
   - original_price
   - discount_price
   - discount_percentage
   - main_image
   - sku
   - stock_quantity
   - is_active = 1
```

### Test 8.2: Category Slug Stored
```
Steps:
1. Add product to "Spices" category
2. Check products table
3. Look at category_slug field

Expected:
✅ category_slug = "spices" (not ID)
✅ This allows SEO-friendly URLs
✅ Slug matches category table slug
```

### Test 8.3: Product Slug Uniqueness
```
Steps:
1. Add Product 1: "Test Product"
   Expected slug: "test-product"
2. Add Product 2: "Test Product"
   Expected slug: "test-product-1"
3. Add Product 3: "Test Product"
   Expected slug: "test-product-2"

Check products table:

Expected:
✅ All slugs are unique
✅ Counter appended when needed
✅ Slugs are SEO-friendly
```

### Test 8.4: Variant Inserted Correctly
```
Steps:
1. Add product with 500g weight selected
2. Open phpMyAdmin
3. Go to product_variants table
4. Find variant for your product

Expected:
✅ Variant exists for the product
✅ Fields populated:
   - product_id (correct)
   - weight_label = "500g"
   - weight_value = "500g"
   - weight_grams = 500
   - variant_price = discount_price × 1
   - variant_discount_price = discount_price × 1
   - variant_sku (if SKU was entered)
   - stock_quantity
```

### Test 8.5: SQL Query Test
```
Steps:
1. Open phpMyAdmin → SQL tab
2. Run query:
   SELECT p.name, p.slug, c.slug as category_slug, 
          pv.weight_label, pv.variant_price
   FROM products p
   JOIN categories c ON p.category_id = c.id
   LEFT JOIN product_variants pv ON p.id = pv.product_id
   ORDER BY p.created_at DESC LIMIT 5;

Expected:
✅ Recent products appear
✅ Category slugs match categories
✅ Variants linked correctly
✅ All relationships intact
```

---

## 🧪 Test Suite 9: Edge Cases & Security

### Test 9.1: SQL Injection Attempt
```
Product Name: "'; DROP TABLE products; --"

Steps:
1. Enter injection string as product name
2. Submit form

Expected:
✅ Form accepts without error
✅ Product created with name as literal string
✅ No SQL executed, database intact
✅ Prepared statements protected system
```

### Test 9.2: XSS Attempt
```
Product Name: "<script>alert('XSS')</script>"

Steps:
1. Enter script tag as product name
2. Submit form
3. View product in admin panel

Expected:
✅ Form accepts
✅ Script stored as plain text
✅ No JavaScript executed
✅ Displays as: "<script>alert('XSS')</script>"
```

### Test 9.3: Duplicate SKU
```
Steps:
1. Add Product 1 with SKU: "SAF-001"
2. Add Product 2 with SKU: "SAF-001"

Expected:
✅ First product saves successfully
✅ Second product shows error: "SKU already exists"
✅ Database only has one product with SAF-001
```

### Test 9.4: Very Long Inputs
```
Test Cases:

1. Product Name: 500 characters
   ✅ Shows error or truncates

2. Description: 2000 characters
   ✅ Shows error or truncates at 1000

3. Very large file (100MB):
   ✅ Shows error, file not uploaded
```

### Test 9.5: Special Characters in Slug
```
Product Names:
1. "Product @ 50% OFF!"
   → slug: "product-50-off"
2. "Café & Restaurant"
   → slug: "cafe-restaurant"
3. "Price: Rs. 1,000"
   → slug: "price-rs-1000"

Expected:
✅ All special characters removed/replaced
✅ Slugs contain only alphanumeric and hyphens
✅ Slugs are lowercase
```

---

## 🧪 Test Suite 10: Performance & Cross-Browser

### Test 10.1: Form Loading Time
```
Steps:
1. Clear browser cache
2. Open add-product.php
3. Measure load time

Expected:
✅ Page loads in < 2 seconds
✅ All content visible immediately
✅ No delay in category dropdown
```

### Test 10.2: Price Calculation Speed
```
Steps:
1. Type "5000" in original price
2. Observe instant update
3. Click weight buttons rapidly
4. Check quantity changes

Expected:
✅ All updates instant (no lag)
✅ No delay in response
✅ Smooth user experience
```

### Test 10.3: Cross-Browser Compatibility
```
Browsers to Test:
1. Chrome (latest)
2. Firefox (latest)
3. Safari (latest)
4. Edge (latest)

In each browser:
✅ Form displays correctly
✅ Buttons are clickable
✅ Prices calculate correctly
✅ Images upload successfully
✅ Form submits properly
```

### Test 10.4: Mobile Responsiveness
```
Steps:
1. Open form on mobile device/emulator
2. Test each feature

Expected:
✅ Form is readable on mobile
✅ Buttons are touch-friendly
✅ Input fields are accessible
✅ No horizontal scrolling
✅ Form submits successfully
```

---

## 📋 Testing Summary Checklist

### Functional Tests
- [ ] Form renders correctly
- [ ] Categories display from database
- [ ] Weight buttons functional
- [ ] Prices calculate dynamically
- [ ] Quantity selector works
- [ ] Category slugs display
- [ ] Images upload
- [ ] Form validates
- [ ] AJAX submits form
- [ ] Products save to database
- [ ] Variants created
- [ ] Redirects to product list

### Validation Tests
- [ ] Required fields validated
- [ ] Price logic validated
- [ ] File sizes validated
- [ ] Weight selection required
- [ ] Image file types checked

### Security Tests
- [ ] SQL injection prevented
- [ ] XSS prevented
- [ ] File upload secured
- [ ] Input sanitized
- [ ] Unique SKU enforced

### Database Tests
- [ ] Products table populated
- [ ] Variants table populated
- [ ] Category slug stored
- [ ] Slug is unique
- [ ] All fields correct

### UI/UX Tests
- [ ] Error messages clear
- [ ] Success messages display
- [ ] Visual feedback on buttons
- [ ] Real-time updates smooth
- [ ] Mobile responsive

### Performance Tests
- [ ] Page loads fast
- [ ] Calculations instant
- [ ] File uploads smooth
- [ ] No JavaScript errors

---

## 🎯 Success Criteria

**All tests pass when:**
✅ Form loads without errors
✅ All features work as intended
✅ Data saved correctly to database
✅ Validation prevents invalid data
✅ Security measures prevent attacks
✅ User experience is smooth
✅ Mobile friendly
✅ Cross-browser compatible

---

## 📊 Test Results Template

```
Test Date: ___________
Tester Name: ___________
Browser: ___________
Platform: ___________

Test Suite 1: Form Rendering
✅ Test 1.1: PASS / FAIL
✅ Test 1.2: PASS / FAIL
✅ Test 1.3: PASS / FAIL
✅ Test 1.4: PASS / FAIL

Test Suite 2: Weight & Pricing
✅ Test 2.1: PASS / FAIL
[... continue for all tests ...]

Overall Result: PASS / FAIL
Issues Found: [list any issues]
```

---

**Ready to test! Follow each test case systematically and document any issues found. 🚀**

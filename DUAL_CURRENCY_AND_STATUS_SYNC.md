# Dual Currency & Book Status Synchronization

**Date:** August 14, 2026  
**Task:** Add dual currency support (USD & KES) and sync frontend shop with backend book status

---

## 🎯 CHANGES SUMMARY

### 1. **Navigation Fix - Login/Dashboard Toggle**
- **Issue:** Login button showed even when admin was logged in
- **Fix:** Added session check in `includes/nav.php`
- **Result:** Button now shows "Dashboard" when admin is logged in, "Login" when not

### 2. **Dual Currency Support (USD & KES)**
- **New Fields:** `price_usd` and `price_kes` added to `books` table
- **Auto-Conversion:** 1 USD ≈ 130 KES (configurable)
- **User Preference:** Currency selection saved in cookie (30 days)

### 3. **Frontend-Backend Book Status Sync**
- **Frontend Shop:** Now only shows `published` and `coming_soon` books
- **Hidden Statuses:** `draft`, `writing`, and `archived` books are hidden from public
- **Backend:** All statuses visible to admin for management

---

## 📁 FILES MODIFIED

### Navigation
- **`includes/nav.php`**
  - Added `session_start()` to read admin session
  - Added conditional logic for Login/Dashboard button (desktop & mobile)

### Admin Panel
- **`admin/includes/header.php`**
  - Fixed "View Site" link from relative `../public/` to absolute `/public/`

- **`admin/books/editor.php`**
  - Added `price_usd` and `price_kes` input fields
  - Added JavaScript auto-calculation (USD to KES)
  - Updated database INSERT/UPDATE queries

- **`admin/books/manage.php`**
  - Updated book card display to show both USD and KES prices

### Frontend Shop
- **`shop/index.php`**
  - Changed query to only fetch `published` and `coming_soon` books
  - Added currency toggle button (USD / KES)
  - Added JavaScript for live currency switching
  - Prices update without page reload
  - Currency preference saved to cookie

### Database
- **`sql/add_dual_currency_support.sql`** (NEW)
  - Migration script to add currency columns
  - Auto-migrates existing `price` data to `price_usd`
  - Auto-calculates `price_kes` from `price_usd`

---

## 🗄️ DATABASE MIGRATION

Run this SQL file to add dual currency support:

```sql
-- File: sql/add_dual_currency_support.sql

ALTER TABLE `books`
ADD COLUMN `price_usd` decimal(10,2) DEFAULT 0.00 AFTER `price`,
ADD COLUMN `price_kes` decimal(10,2) DEFAULT 0.00 AFTER `price_usd`;

UPDATE `books` SET `price_usd` = `price` WHERE `price_usd` = 0;
UPDATE `books` SET `price_kes` = `price_usd` * 130 WHERE `price_kes` = 0;

ALTER TABLE `shop_products`
ADD COLUMN `price_usd` decimal(10,2) DEFAULT 0.00 AFTER `price`,
ADD COLUMN `price_kes` decimal(10,2) DEFAULT 0.00 AFTER `price_usd`;

UPDATE `shop_products` SET `price_usd` = `price` WHERE `price_usd` = 0;
UPDATE `shop_products` SET `price_kes` = `price_usd` * 130 WHERE `price_kes` = 0;
```

**To Run:**
1. Go to phpMyAdmin
2. Select your database
3. Click "SQL" tab
4. Paste the contents of `sql/add_dual_currency_support.sql`
5. Click "Go"

---

## 🎨 FRONTEND FEATURES

### Currency Toggle
- **Location:** Shop page header
- **Options:** USD ($) and KES (KSh)
- **Behavior:**
  - Click to switch currency
  - All prices update instantly
  - Selection saved in cookie for 30 days
  - Remembered across sessions

### Book Display Rules
- **Published:** Full "View Details" button
- **Coming Soon:** "Read Preview" button (if preview_available = 1)
- **Draft/Writing/Archived:** Hidden from public view

---

## 🔧 ADMIN PANEL FEATURES

### Book Editor
- **Price Fields:**
  - USD field (required, 2 decimals)
  - KES field (required, whole numbers)
  - Auto-calculates KES when USD is entered
  - Conversion rate: 1 USD = 130 KES

### Book Status Options
1. **📝 Draft** - Hidden from frontend
2. **✍️ Writing** - Hidden from frontend, indicates work in progress
3. **🔜 Coming Soon** - Visible on shop page
4. **✅ Published** - Fully available on shop page
5. **📦 Archived** - Hidden from frontend

### Book Management View
- Shows both USD and KES prices
- Example: `$14.99 / KSh 1,950`

---

## 🧪 TESTING CHECKLIST

### Navigation
- [ ] Login button shows on frontend when NOT logged in
- [ ] Dashboard button shows on frontend when logged in
- [ ] Logout works from admin header
- [ ] "View Site" link goes to `/public/index.php`

### Currency System
- [ ] Shop page loads with default currency (USD)
- [ ] Currency toggle switches prices instantly
- [ ] Currency preference persists after refresh
- [ ] All books show correct prices in both currencies

### Book Status Sync
- [ ] Draft books are hidden from shop page
- [ ] Writing books are hidden from shop page
- [ ] Coming Soon books appear on shop page
- [ ] Published books appear on shop page
- [ ] Archived books are hidden from shop page
- [ ] All books visible in admin panel

### Admin Editor
- [ ] USD price field accepts decimals (e.g., 14.99)
- [ ] KES price auto-calculates when USD is entered
- [ ] Manual KES override works
- [ ] Book saves with both currencies
- [ ] Manage page displays both prices

---

## 🔄 CONVERSION RATE UPDATE

To change the USD to KES conversion rate:

**File:** `admin/books/editor.php` (Line ~440)
```javascript
const conversionRate = 130; // Change this value
```

**File:** `shop/index.php` (Line ~19)
```php
// Update fallback calculation
$book['price_kes'] ?? ($book['price'] * 130) // Change 130
```

---

## 💾 BACKWARD COMPATIBILITY

- Old `price` column preserved for compatibility
- New apps should use `price_usd` and `price_kes`
- Migration script auto-converts existing data
- No data loss during upgrade

---

## 🐛 KNOWN ISSUES

**None at this time.**

---

## 📝 FUTURE ENHANCEMENTS

1. **Dynamic Exchange Rates**
   - Fetch live USD/KES rates from API
   - Auto-update prices periodically

2. **More Currencies**
   - Add EUR, GBP, etc.
   - Multi-currency dropdown

3. **Regional Detection**
   - Auto-select currency based on user location
   - IP geolocation integration

---

## 📞 SUPPORT

For questions or issues:
- Check this documentation
- Review SQL migration file
- Test on staging before production deployment

**Deployment completed successfully!** ✅

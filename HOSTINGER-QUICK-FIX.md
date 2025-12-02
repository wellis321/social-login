# Quick Fix for Hostinger 500 Error

## The Problem
The `.htaccess` redirect is causing a 500 error because your files are organized differently on Hostinger than locally.

## Simplest Solution: Flat File Structure

**On Hostinger, your file structure should be:**

```
public_html/
├── admin/               (copy from public/admin/)
├── assets/              (copy from public/assets/)
├── config/              (copy from root config/)
├── includes/            (copy from root includes/)
├── index.php            (copy from public/index.php)
├── twitter.php          (copy from public/twitter.php)
├── facebook.php         (copy from public/facebook.php)
├── instagram.php        (copy from public/instagram.php)
└── ... all other .php files from public/
```

## Step-by-Step Instructions

### 1. Delete the .htaccess file on Hostinger
Remove the `.htaccess` file from your root directory on Hostinger (it's causing the 500 error).

### 2. Upload files correctly

**Using Hostinger File Manager:**

1. Go to Hostinger control panel → File Manager
2. Navigate to `public_html/`
3. Delete everything currently there
4. Upload these folders:
   - `admin/` folder from your local `public/admin/`
   - `assets/` folder from your local `public/assets/`
   - `config/` folder from your local root `config/`
   - `includes/` folder from your local root `includes/`
5. Upload all `.php` files from your local `public/` directory to `public_html/`

### 3. Create .env file

In `public_html/config/`, create a file named `.env` with your Hostinger database credentials:

```env
DB_HOST=localhost
DB_USER=your_hostinger_db_user
DB_PASS=your_hostinger_db_password
DB_NAME=your_hostinger_db_name
```

**To find these credentials:**
- Hostinger Control Panel → Databases → MySQL Databases
- Your credentials are listed there

### 4. Initialize the database

Visit: `https://olive-goldfinch-502177.hostingersite.com/config/setup.php`

You should see: "✓ Database initialized successfully!"

### 5. Test the site

Visit: `https://olive-goldfinch-502177.hostingersite.com/`

You should see the social login platform selection page.

## Alternative: Use Hostinger's Document Root Setting

If you want to keep the `public/` folder structure:

1. In Hostinger control panel, go to **Advanced** → **PHP Configuration**
2. Or **Websites** → Select your site → **Advanced**
3. Look for **Document Root** setting
4. Change it from `/public_html` to `/public_html/public`
5. Delete the `.htaccess` file

## Still Getting Errors?

### Check PHP Version
- Hostinger Control Panel → PHP Configuration
- Ensure PHP 7.4 or higher is selected

### Check Error Logs
- Hostinger Control Panel → Advanced → Error Logs
- Look for the actual error message

### Test Database Connection
Create a test file `test-db.php` in your root:

```php
<?php
require_once 'config/database.php';
try {
    $conn = getDbConnection();
    echo "✓ Database connected successfully!";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage();
}
```

Visit: `https://your-site.com/test-db.php`

## Need More Help?

The 500 error is usually caused by:
1. `.htaccess` syntax errors → Delete the file
2. Wrong file paths → Check that `config/` and `includes/` are in the right place
3. PHP version too old → Update to PHP 7.4+
4. File permissions → Should be 644 for files, 755 for folders

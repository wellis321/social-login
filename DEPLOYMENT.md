# Deployment Guide for Hostinger

This guide will help you deploy the Social Login Training Simulator to your Hostinger hosting account.

## Quick Start

Your Hostinger site is currently showing a 403 error because the files need to be properly configured. Follow these steps:

### Option 1: Root Installation (Recommended for Hostinger)

1. **Upload files via File Manager or FTP to `public_html/`:**
   ```
   public_html/
   ├── admin/
   ├── assets/
   ├── config/
   ├── includes/
   ├── index.php
   ├── twitter.php
   ├── facebook.php
   ├── instagram.php
   ├── (all other .php files)
   └── .htaccess
   ```

2. **Update file paths in PHP files:**
   - All files in root: Change `../config` to `config` and `../includes` to `includes`
   - All files in admin/: Change `../../config` to `../config` and `../../includes` to `../includes`

3. **Create `.env` file in `public_html/config/.env`:**
   ```env
   DB_HOST=localhost
   DB_USER=your_hostinger_db_user
   DB_PASS=your_hostinger_db_password
   DB_NAME=your_hostinger_db_name
   ```

4. **Run database setup:**
   - Via browser: Navigate to `https://your-site.com/config/create_database.php`
   - Then: Navigate to `https://your-site.com/config/setup.php`
   - Or via SSH/Terminal:
     ```bash
     php config/create_database.php
     php config/setup.php
     ```

### Option 2: Subdirectory Installation

If you want to keep the project structure as-is with the `public/` directory:

1. **Upload entire project to Hostinger**

2. **Point document root to `public/` directory:**
   - In Hostinger control panel, go to **Website Settings**
   - Change the **Document Root** to `/public`
   - Or use the `.htaccess` file in the root (already included) to redirect

3. **Create `.env` file in `config/.env`** (same as above)

4. **Run database setup** (same as above)

## Database Configuration

### Finding Your Hostinger Database Credentials

1. Log in to Hostinger control panel
2. Go to **Databases** → **MySQL Databases**
3. Note your database credentials:
   - Database Host: Usually `localhost`
   - Database Name: Listed in the databases section
   - Database Username: Listed in the databases section
   - Database Password: Use the one you set when creating the database

### Creating the Database Tables

After uploading files and configuring `.env`:

**Via Web Browser:**
```
https://your-site.hostingersite.com/config/setup.php
```

**Via SSH (if available):**
```bash
cd /home/username/public_html
php config/create_database.php
php config/setup.php
```

## Default Admin Credentials

After running setup, you can access the admin panel:

- URL: `https://your-site.com/admin/`
- Username: `admin`
- Password: `admin123`

**⚠️ IMPORTANT: Change these credentials immediately after first login!**

## File Permissions

Make sure these permissions are set correctly:

```bash
chmod 755 public_html/
chmod 644 public_html/*.php
chmod 755 public_html/admin/
chmod 644 public_html/admin/*.php
chmod 755 public_html/assets/
chmod 644 public_html/assets/css/*
chmod 600 public_html/config/.env  # Keep .env secure
```

## Troubleshooting

### 403 Forbidden Error
- Check that `index.php` exists in your document root
- Check file permissions (should be 644 for files, 755 for directories)
- Verify `.htaccess` is present and `mod_rewrite` is enabled

### 500 Internal Server Error
- Check PHP error logs in Hostinger control panel
- Verify `.env` file has correct database credentials
- Ensure all required PHP extensions are installed (mysqli, pdo)

### Database Connection Errors
- Double-check database credentials in `.env`
- Verify database exists in Hostinger MySQL databases
- Test connection using Hostinger's phpMyAdmin

### Cannot Access Admin Panel
- Make sure you ran `config/setup.php` to create admin account
- Clear browser cache
- Try accessing directly: `https://your-site.com/admin/index.php`

## Security Checklist

Before going live:

- [ ] Change default admin password
- [ ] Ensure `.env` is not accessible via browser (should return 403)
- [ ] Remove or protect `config/setup.php` after initial setup
- [ ] Enable HTTPS (usually automatic on Hostinger)
- [ ] Review file permissions
- [ ] Test all three platforms (Twitter, Facebook, Instagram)
- [ ] Test password recovery flows

## Need Help?

If you encounter issues:

1. Check Hostinger's error logs in the control panel
2. Verify all files uploaded correctly
3. Test database connection using phpMyAdmin
4. Contact Hostinger support if server configuration is needed

## Local Development vs Production

**Local (.env):**
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=social_login
```

**Hostinger (.env):**
```env
DB_HOST=localhost
DB_USER=u123456789_social
DB_PASS=YourSecurePassword123
DB_NAME=u123456789_social_login
```

Remember: **NEVER** commit your production `.env` file to Git!

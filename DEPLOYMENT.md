# Deployment Guide for Hostinger

This guide will help you deploy the Social Login Training Simulator to your Hostinger hosting account.

## Overview

The project structure is now **flat** - all files are in the root directory. This makes deployment to Hostinger simple: just upload and configure your database.

## Deployment Steps

### 1. Upload Files to Hostinger

Using **Hostinger File Manager** or **FTP**:

1. Go to Hostinger control panel → File Manager
2. Navigate to `public_html/`
3. Upload **all files and folders** from your local project:
   ```
   public_html/
   ├── admin/               ← Upload this folder
   ├── assets/              ← Upload this folder
   ├── config/              ← Upload this folder
   ├── includes/            ← Upload this folder
   ├── index.php            ← Upload all .php files
   ├── twitter.php
   ├── facebook.php
   ├── instagram.php
   ├── ... (all other .php files)
   └── .htaccess
   ```

### 2. Configure Database Credentials

1. **Find your Hostinger database credentials:**
   - Hostinger Control Panel → **Databases** → **MySQL Databases**
   - Note these values:
     - Database Host (usually `localhost`)
     - Database Name (e.g., `u123456789_social_login`)
     - Database Username (e.g., `u123456789_user`)
     - Database Password (the one you set)

2. **Create `.env` file:**
   - In File Manager, navigate to `public_html/config/`
   - Create a new file called `.env`
   - Add your credentials:
   ```env
   DB_HOST=localhost
   DB_USER=u123456789_user
   DB_PASS=YourSecurePassword123
   DB_NAME=u123456789_social_login
   ```

### 3. Initialize Database

**Option A - Via Web Browser (Easiest):**
```
https://olive-goldfinch-502177.hostingersite.com/config/setup.php
```

You should see: "✓ Database initialized successfully!"

**Option B - Via SSH (if you have access):**
```bash
cd public_html
php config/create_database.php
php config/setup.php
```

### 4. Test Your Site

Visit: `https://olive-goldfinch-502177.hostingersite.com/`

You should see the platform selection page with Twitter, Facebook, and Instagram options.

### 5. Access Admin Panel

- URL: `https://olive-goldfinch-502177.hostingersite.com/admin/`
- Username: `admin`
- Password: `admin123`

**⚠️ IMPORTANT: Change the admin password immediately!**

## File Permissions

Hostinger usually sets these automatically, but verify:

```bash
Files (.php, .css): 644
Directories: 755
.env file: 600 (most secure)
```

## Troubleshooting

### Site Shows 403 Forbidden
- Ensure `index.php` exists in `public_html/`
- Check that `.htaccess` was uploaded
- Verify file permissions (644 for files, 755 for folders)

### Site Shows 500 Internal Server Error
- Check Hostinger error logs (Control Panel → Error Logs)
- Verify `.htaccess` syntax
- Ensure PHP version is 7.4 or higher (Control Panel → PHP Configuration)

### Database Connection Error
- Verify database credentials in `.env` file
- Check that database exists in Hostinger MySQL Databases section
- Test connection using phpMyAdmin (in Hostinger control panel)

### Cannot Access Admin Panel
- Ensure you ran `config/setup.php` to create the admin account
- Try accessing directly: `/admin/index.php`
- Clear browser cache

### CSS Not Loading
- Check that `assets/` folder was uploaded correctly
- Verify file permissions on CSS files (should be 644)
- Check browser console for 404 errors

## Security Checklist

Before going live:

- [ ] Change default admin password (`admin/admin123`)
- [ ] Verify `.env` file is not accessible via browser
- [ ] Delete or protect `config/setup.php` and `config/create_database.php` after setup
- [ ] Enable HTTPS (usually automatic on Hostinger)
- [ ] Review file permissions
- [ ] Test all three platforms
- [ ] Test password recovery flows

## Local vs Production Configuration

The code is identical for both environments - only the `.env` file differs!

**Local `.env`:**
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=social_login
```

**Hostinger `.env`:**
```env
DB_HOST=localhost
DB_USER=u123456789_social
DB_PASS=YourSecurePassword123
DB_NAME=u123456789_social_login
```

**Remember:** NEVER commit your `.env` file to Git!

## Updating Your Site

When you make changes locally and want to deploy:

1. Commit and push to GitHub:
   ```bash
   git add -A
   git commit -m "Your changes"
   git push origin main
   ```

2. On Hostinger, pull the changes:
   - If you have SSH access: `git pull origin main`
   - Or use File Manager to upload changed files

3. If you changed the database schema, run:
   ```
   https://your-site.com/config/setup.php
   ```

## Need Help?

- Check Hostinger error logs in control panel
- Test database connection with phpMyAdmin
- Review file permissions
- Contact Hostinger support for server issues

## Quick Reference

| What | Where | Default Value |
|------|-------|---------------|
| Site URL | Hostinger domain | https://olive-goldfinch-502177.hostingersite.com/ |
| Admin Panel | /admin/ | Username: admin / Password: admin123 |
| Database Config | config/.env | Update with Hostinger credentials |
| PHP Version | Hostinger PHP Settings | Requires 7.4+ |
| Document Root | Hostinger Settings | public_html/ |

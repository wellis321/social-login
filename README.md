# Social Media Login Training Simulator

A training tool for Duke of Edinburgh Awards staff to practice and teach social media registration and login processes in a safe, controlled environment.

## Features

- Simulates login/registration for Twitter/X, Facebook, and Instagram
- Step-by-step guidance for users new to social media
- Safe testing environment with temporary accounts
- Admin panel for account management
- Educational content at each step

## Technology Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript

## Installation

1. **Clone or download this repository**
   ```bash
   git clone https://github.com/wellis321/social-login.git
   cd social-login
   ```

2. **Configure environment variables**
   ```bash
   cp .env.example .env
   ```
   Then edit `.env` and update your MySQL database credentials:
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=your_password
   DB_NAME=social_login
   ```

3. **Create the database**
   ```bash
   php config/create_database.php
   ```

4. **Initialize the database tables**
   ```bash
   php config/setup.php
   ```

5. **Start your web server**
   - Use PHP's built-in server for local testing:
     ```bash
     php -S localhost:8000
     ```
   - Or configure your web server (Apache, Nginx) to point to the project root

6. **Access the application**
   - Navigate to `http://localhost:8000` in your browser

## Default Admin Credentials

- Username: `admin`
- Password: `admin123`

**IMPORTANT**: Change the default admin password immediately!

## Project Structure

```
/
├── index.php           - Main entry point (platform selection)
├── *.php               - Platform-specific pages (twitter.php, facebook.php, etc.)
├── /admin              - Admin panel files
├── /assets             - CSS, JavaScript, images
├── /config             - Database configuration and .env
├── /includes           - PHP helper functions
└── .htaccess           - Web server configuration
```

## Usage

1. Open the main page and select a social media platform
2. Follow the step-by-step registration process
3. Practice logging in and out
4. Delete or reset accounts as needed
5. Admin can manage all accounts via the admin panel

## For Developers

See [CLAUDE.md](CLAUDE.md) for detailed development guidance and architecture information.

## License

This is a training tool for educational purposes.

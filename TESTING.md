# Testing Guide

## Starting the Application

1. **Start the PHP development server:**
   ```bash
   cd public
   php -S localhost:8000
   ```

2. **Open your browser and navigate to:**
   ```
   http://localhost:8000
   ```

## Testing the Twitter/X Platform

### Test Flow 1: Registration
1. Go to http://localhost:8000
2. Click on the **Twitter / X** card
3. Click **Create account**
4. Follow the 3-step registration:
   - **Step 1:** Enter email (e.g., `test@example.com`)
   - **Step 2:** Create password (min 8 characters)
   - **Step 3:** Enter profile details (name, username, date of birth)
5. You should see the success dashboard

### Test Flow 2: Login
1. From the Twitter home page, click **Sign in**
2. Enter the email and password you just created
3. Click **Sign in**
4. You should see the dashboard

### Test Flow 3: Logout
1. From the dashboard, click **Log Out**
2. You should be redirected to Twitter home

### Test Flow 4: Delete Account
1. Log in to your account
2. Scroll to the bottom of the dashboard
3. Click **Delete Practice Account**
4. Confirm the deletion
5. The account should be deleted

## What to Look For

### Educational Content
- Each page should have clear guidance boxes
- Help text should explain what each field is for
- Tips for strong passwords and security

### Visual Design
- Dark theme matching X/Twitter branding
- Blue (#1d9bf0) accent color
- Responsive design on mobile and desktop
- Smooth transitions and hover effects

### Functionality
- Email validation
- Password strength requirements
- Username format validation (3-15 chars, alphanumeric + underscore)
- Age verification (must be 13+)
- Error messages for invalid input
- Success messages after registration

## Common Test Cases

### Error Handling
1. **Duplicate email:** Try registering with the same email twice
2. **Invalid email:** Try entering an invalid email format
3. **Short password:** Try a password with less than 8 characters
4. **Password mismatch:** Enter different passwords in confirm field
5. **Invalid username:** Try special characters in username
6. **Wrong login:** Enter incorrect password

### Edge Cases
1. **Browser back button:** Works during signup flow
2. **Form refresh:** Data persists in session
3. **Direct URL access:** Can navigate to signup steps directly

## Database Verification

Check the database to verify accounts are created:

```bash
php config/verify_database.php
```

Or connect to MySQL:
```sql
USE social_login_training;
SELECT * FROM users WHERE platform = 'twitter';
SELECT * FROM activity_log;
```

## Next Steps

After testing Twitter/X, you can:
1. Build Facebook platform (similar structure)
2. Build Instagram platform
3. Create admin panel to manage all accounts
4. Add more features (password reset, profile editing, etc.)

#!/bin/bash
# Deploy script for Hostinger
# This moves files from public/ to root for Hostinger compatibility

echo "🚀 Preparing files for Hostinger deployment..."

# Move all files from public/ to root (except .htaccess if exists)
echo "Moving public files to root..."
cp -r public/* .

# Update paths in PHP files to point to correct locations
echo "Updating file paths..."

# Update all PHP files that reference ../config or ../includes
find . -maxdepth 1 -name "*.php" -type f -exec sed -i '' 's|__DIR__ . '\''/\.\./config|__DIR__ . '\''/config|g' {} \;
find . -maxdepth 1 -name "*.php" -type f -exec sed -i '' 's|__DIR__ . '\''/\.\./includes|__DIR__ . '\''/includes|g' {} \;

# Update admin files
find admin/ -name "*.php" -type f -exec sed -i '' 's|__DIR__ . '\''/\.\./\.\./config|__DIR__ . '\''/../config|g' {} \;
find admin/ -name "*.php" -type f -exec sed -i '' 's|__DIR__ . '\''/\.\./\.\./includes|__DIR__ . '\''/../includes|g' {} \;

echo "✅ Files prepared for Hostinger!"
echo ""
echo "📋 Next steps:"
echo "1. Upload these files to your Hostinger public_html directory:"
echo "   - All .php files in the root"
echo "   - admin/ folder"
echo "   - assets/ folder"
echo "   - config/ folder (with your production .env file)"
echo "   - includes/ folder"
echo ""
echo "2. Make sure your .env file on Hostinger has the correct database credentials"
echo "3. Run the database setup: php config/setup.php"
echo "4. Visit your site!"

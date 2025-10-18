# Flipbook Admin System - Setup Instructions

## Overview

This flipbook admin system allows you to:
- Upload PDF files and convert them to interactive flipbooks
- Assign MP3 audio files to specific pages
- Manage multiple flipbooks through a web interface
- Display flipbooks publicly with a secure admin area

## Files Created

### Core Files
- `flipbook-admin-login.php` - Admin login page
- `flipbook-admin-dashboard.php` - Main admin dashboard
- `flipbook-admin-logout.php` - Logout handler
- `flipbook-config.php` - Database configuration
- `flipbook-db.php` - Database abstraction layer
- `flipbook-schema.sql` - Database schema

### Styling and JavaScript
- `flipbook-admin-styles.css` - Admin panel styling
- `flipbook-admin.js` - Frontend JavaScript for admin panel

### API Endpoints
- `flipbook-api-save.php` - Save flipbook data
- `flipbook-api-delete.php` - Delete flipbooks

### Public Viewer
- `flipbook-public-viewer.php` - Public-facing flipbook viewer

## Installation Steps

### 1. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE flipbook_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the schema:
```bash
mysql -u your_username -p flipbook_database < flipbook-schema.sql
```

Or use phpMyAdmin to import `flipbook-schema.sql`

### 2. Configuration

Edit `flipbook-config.php` and update these settings:

```php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'flipbook_database');
define('DB_USER', 'your_username');      // Change this
define('DB_PASS', 'your_password');      // Change this
```

### 3. Change Admin Password

Edit `flipbook-admin-login.php` and change the password:

```php
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'your_secure_password');  // Change this!
```

**IMPORTANT:** Choose a strong password with uppercase, lowercase, numbers, and special characters.

### 4. Upload Files

Upload all files to your web server using FTP or file manager:
- All PHP files
- CSS file
- JavaScript file
- SQL file (for reference)

### 5. Set Permissions

Make sure the upload directory is writable:
```bash
chmod 755 flipbook-uploads/
```

The system will automatically create subdirectories when needed.

### 6. Test the System

1. Navigate to: `https://yourdomain.com/flipbook-admin-login.php`
2. Login with your credentials
3. Click "Create New Flipbook"
4. Upload a PDF and follow the wizard

## SSL/HTTPS Setup

### For cPanel/Shared Hosting

1. Log into cPanel
2. Go to "SSL/TLS" or "Let's Encrypt SSL"
3. Enable SSL for your domain
4. Force HTTPS redirect by adding to `.htaccess`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### For VPS/Dedicated Server

1. Install Certbot:
```bash
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache
```

2. Generate certificate:
```bash
sudo certbot --apache -d yourdomain.com
```

3. Auto-renewal:
```bash
sudo certbot renew --dry-run
```

## Usage Guide

### Creating a Flipbook

1. **Login** to admin panel
2. **Click** "Create New Flipbook"
3. **Step 1:** Enter title, description, and upload PDF
4. **Step 2:** Wait for PDF conversion (automatic)
5. **Step 3:** Upload MP3 audio files (optional)
6. **Step 4:** Assign audio to specific pages
7. **Step 5:** Save flipbook

### Viewing a Flipbook

- From dashboard, click "View" on any flipbook
- Or access directly: `flipbook-public-viewer.php?id=1`

### Navigation Features

**Desktop:**
- Click left/right 25% of page to navigate
- Use arrow buttons on sides
- Press left/right arrow keys
- Click zoom button to enable magnification

**Mobile:**
- Tap navigation arrows
- Swipe gestures work on the page

### Audio Features

- Audio plays automatically on page flip (after first click)
- Mute/unmute button in viewer controls
- Different audio can play on each page
- Audio loops continuously

## Integrating with Existing Website

### Option 1: Embed in Iframe

Add to your existing site:
```html
<iframe src="flipbook-public-viewer.php?id=1"
        width="100%"
        height="1200px"
        frameborder="0">
</iframe>
```

### Option 2: Direct Link

Update your existing flipbook links in `script.js`:
```javascript
const flipbooks = {
    'cork-5': 'flipbook-public-viewer.php?id=1',
    'cork-6': 'flipbook-public-viewer.php?id=2',
    // etc...
};
```

### Option 3: Dynamic Loading

Modify your site to query available flipbooks:
```php
<?php
require_once 'flipbook-db.php';
$db = new FlipbookDB();
$flipbooks = $db->getAllFlipbooks();

foreach ($flipbooks as $flipbook) {
    echo '<a href="flipbook-public-viewer.php?id=' . $flipbook['id'] . '">';
    echo htmlspecialchars($flipbook['title']);
    echo '</a>';
}
?>
```

## Security Recommendations

1. **Change default admin credentials immediately**
2. **Use strong passwords** (16+ characters)
3. **Enable HTTPS/SSL** before going live
4. **Restrict admin access** by IP if possible:

Add to `flipbook-admin-login.php`:
```php
$allowed_ips = ['123.456.789.0', '98.76.54.32'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Access denied');
}
```

5. **Disable error reporting** in production:

In `flipbook-config.php`:
```php
error_reporting(0);
ini_set('display_errors', 0);
```

6. **Regular backups** of database and files

## Troubleshooting

### "Database connection failed"
- Check credentials in `flipbook-config.php`
- Verify MySQL is running
- Check database exists

### "Failed to upload PDF"
- Check file size limits in `php.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```
- Verify upload directory permissions

### "Page not found" errors
- Ensure all files are uploaded
- Check file permissions (644 for files, 755 for directories)
- Verify mod_rewrite is enabled if using .htaccess

### Audio not playing
- Ensure MP3 files are valid
- Check browser console for errors
- User must click/interact before audio plays (browser requirement)

### Images not displaying
- Check database size limits
- Verify base64 data is stored correctly
- Try smaller PDF file

## Database Maintenance

### Backup Database
```bash
mysqldump -u username -p flipbook_database > backup.sql
```

### Restore Database
```bash
mysql -u username -p flipbook_database < backup.sql
```

### Clean Up Old Flipbooks
```sql
DELETE FROM flipbooks WHERE is_active = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

## Performance Optimization

### For Large PDFs
- Reduce scale in `flipbook-admin.js` (line 133):
```javascript
const viewport = page.getViewport({ scale: 1.5 }); // Instead of 2.0
```

### For Many Flipbooks
- Add pagination to dashboard
- Implement caching for viewer page
- Consider moving to file-based storage for images

### Database Indexing
Already included in schema, but verify:
```sql
SHOW INDEX FROM flipbooks;
SHOW INDEX FROM pages;
```

## Support and Customization

### Changing Colors
Edit `flipbook-admin-styles.css`:
```css
.btn-primary {
    background: #YOUR_COLOR;
}
```

### Changing Upload Limits
Edit `flipbook-config.php`:
```php
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
```

### Adding Features
- Edit flipbook: Load data into form (TODO in `flipbook-admin.js`)
- User roles: Add user management table
- Analytics: Track page views and time spent

## File Structure
```
website-root/
├── flipbook-admin-login.php
├── flipbook-admin-dashboard.php
├── flipbook-admin-logout.php
├── flipbook-admin-styles.css
├── flipbook-admin.js
├── flipbook-config.php
├── flipbook-db.php
├── flipbook-schema.sql
├── flipbook-api-save.php
├── flipbook-api-delete.php
├── flipbook-public-viewer.php
└── flipbook-uploads/
    ├── pdfs/
    ├── audio/
    └── pages/
```

## Next Steps

1. Set up database and configure credentials
2. Change default admin password
3. Enable SSL/HTTPS
4. Test with a sample PDF
5. Integrate with your existing website
6. Train content editors on the system

## License & Credits

Built with:
- PDF.js (https://mozilla.github.io/pdf.js/)
- Custom CSS 3D flip animation
- PHP/MySQL backend

---

For questions or issues, refer to the code comments in each file for detailed explanations.

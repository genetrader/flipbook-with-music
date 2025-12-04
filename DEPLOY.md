# Deployment Guide

## Quick Deploy Checklist

### 1. Pre-Deployment
- [ ] All files refactored and tested locally
- [ ] Git repository initialized
- [ ] Pushed to GitHub
- [ ] Created release tag

### 2. Server Upload
```bash
# Upload via SFTP/FTP or use git clone
git clone https://github.com/YOUR-USERNAME/flipbook-plugin.git
cd flipbook-plugin
```

### 3. Installation
```bash
# Navigate to installer in browser
https://your-site.com/flipbook-plugin/install/install.php

# Follow the 4-step wizard:
# Step 1: Requirements check
# Step 2: Database configuration
# Step 3: Admin account creation
# Step 4: Installation complete
```

### 4. Post-Installation Security
```bash
# CRITICAL: Delete the install folder!
rm -rf install/

# Verify config is not accessible
curl https://your-site.com/flipbook-plugin/src/config.php
# Should return 403 Forbidden or blank page
```

### 5. Test the Installation
- [ ] Can login to admin panel
- [ ] Can create test flipbook
- [ ] PDF uploads and converts
- [ ] Audio uploads work
- [ ] Public viewer displays flipbook
- [ ] Audio playback works
- [ ] Mobile responsive

### 6. Production Configuration

Edit `src/config.php` and set:
```php
define('FLIPBOOK_DEBUG', 0); // Turn off debug mode
```

---

## Method 1: Git Clone (Recommended)

### On Your Server:
```bash
cd /var/www/html  # Or your web root
git clone https://github.com/YOUR-USERNAME/flipbook-plugin.git flipbook
cd flipbook
chmod -R 755 .
chmod -R 775 uploads/
```

### Set File Permissions:
```bash
# Directories: 755
find . -type d -exec chmod 755 {} \;

# Files: 644
find . -type f -exec chmod 644 {} \;

# Uploads: 775 (writable)
chmod -R 775 uploads/
```

### Run Installer:
Visit: `https://your-site.com/flipbook/install/install.php`

---

## Method 2: FTP/SFTP Upload

### Using FileZilla or Similar:
1. Connect to your server via FTP/SFTP
2. Navigate to your web directory
3. Create folder: `flipbook`
4. Upload all files from local `flipbook-plugin/` folder
5. Set permissions:
   - Folders: 755
   - Files: 644
   - `uploads/` folder: 775

### Run Installer:
Visit: `https://your-site.com/flipbook/install/install.php`

---

## Method 3: cPanel File Manager

1. Login to cPanel
2. Open File Manager
3. Navigate to `public_html` (or your web root)
4. Create new folder: `flipbook`
5. Upload zip file of plugin
6. Extract zip file
7. Set folder permissions to 755
8. Set `uploads/` to 775
9. Visit installer: `https://your-site.com/flipbook/install/install.php`

---

## Integration with Existing Site

### Update Your HTML/JS:

```javascript
// In your main site's JavaScript
const flipbooks = {
    'book-1': '/flipbook/src/public/viewer.php?id=1',
    'book-2': '/flipbook/src/public/viewer.php?id=2',
};
```

### Embed as iFrame:
```html
<iframe src="/flipbook/src/public/viewer.php?id=1"
        width="100%" height="800px" frameborder="0">
</iframe>
```

---

## Apache Configuration

### Add to .htaccess (in flipbook root):
```apache
# Protect config file
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css application/json
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType audio/mpeg "access plus 1 month"
</IfModule>
```

---

## Nginx Configuration

### Add to nginx.conf:
```nginx
location /flipbook/ {
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Protect config
    location ~ /config\.php$ {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|mp3|pdf)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## Database Setup

### Manual Database Creation (if installer fails):

```sql
-- Create database
CREATE DATABASE flipbook_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'flipbook_user'@'localhost' IDENTIFIED BY 'strong_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON flipbook_db.* TO 'flipbook_user'@'localhost';
FLUSH PRIVILEGES;

-- Import schema
USE flipbook_db;
SOURCE /path/to/flipbook-plugin/install/schema.sql;

-- Replace {{TABLE_PREFIX}} with your prefix (e.g., 'flipbook_')
UPDATE schema by finding all {{TABLE_PREFIX}} and replacing with actual prefix
```

---

## Troubleshooting Deployment

### Issue: "Config file not found"
**Solution:** Ensure you ran the installer and `src/config.php` was created

### Issue: "Database connection failed"
**Solution:**
- Verify database credentials in `src/config.php`
- Ensure database exists
- Check user has proper permissions

### Issue: "Permission denied" errors
**Solution:**
```bash
chmod -R 755 /path/to/flipbook
chmod -R 775 /path/to/flipbook/uploads
```

### Issue: "Upload failed"
**Solution:**
- Check PHP upload limits: `upload_max_filesize` and `post_max_size`
- Verify uploads folder is writable (775)
- Check disk space

### Issue: "Session errors"
**Solution:**
- Ensure session directory is writable
- Check PHP session.save_path in php.ini

---

## SSL/HTTPS Setup

### Force HTTPS (in .htaccess):
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Backup Strategy

### What to Backup:
1. **Database** - All flipbook data
2. **Uploads folder** - All PDFs, audio, images
3. **Config file** - Your settings

### Automated Backup Script:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/flipbook"

# Backup database
mysqldump -u flipbook_user -p'password' flipbook_db > $BACKUP_DIR/db_$DATE.sql

# Backup uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz /var/www/html/flipbook/uploads/

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

---

## Performance Optimization

### Enable OpCode Caching:
```ini
; In php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Database Optimization:
```sql
-- Add indexes if not present
ALTER TABLE flipbook_flipbooks ADD INDEX idx_created (created_at);
ALTER TABLE flipbook_flipbooks ADD INDEX idx_active (is_active);
ALTER TABLE flipbook_pages ADD INDEX idx_flipbook (flipbook_id);
```

---

## Monitoring

### Check PHP Error Log:
```bash
tail -f /var/log/php_errors.log
```

### Check Web Server Log:
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

---

## Security Checklist

- [ ] Deleted install/ folder
- [ ] Changed default admin password
- [ ] Set FLIPBOOK_DEBUG to 0
- [ ] Enabled HTTPS
- [ ] Protected config.php via .htaccess
- [ ] Set proper file permissions (755/644)
- [ ] Regular backups scheduled
- [ ] PHP and MySQL up to date

---

## Support

Issues with deployment? Check:
- [README.md](README.md) - General documentation
- [REFACTORING-GUIDE.md](REFACTORING-GUIDE.md) - File structure
- [docs/INTEGRATION.md](docs/INTEGRATION.md) - Integration examples
- [GitHub Issues](https://github.com/YOUR-USERNAME/flipbook-plugin/issues)

---

**Deployment Complete!** 🎉

Admin Panel: `https://your-site.com/flipbook/src/admin/login.php`

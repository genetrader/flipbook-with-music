# Local Testing Setup Guide

## Current Status
❌ **PHP not found on your system**
❌ **MySQL not found on your system**

You need a local PHP + MySQL environment to test standalone.

---

## 🚀 Quick Setup Options

### **Option 1: XAMPP (Recommended - Easiest)**
**Time:** 10 minutes
**Best for:** Windows users, beginners

#### Install XAMPP:
1. Download: https://www.apachefriends.org/download.html
   - Get **PHP 8.0+** version
   - File size: ~150MB

2. Install to: `C:\xampp`
   - Run installer
   - Select: Apache, MySQL, PHP, phpMyAdmin
   - Deselect: Tomcat, Perl (not needed)

3. Start Services:
   - Open XAMPP Control Panel
   - Click "Start" for **Apache**
   - Click "Start" for **MySQL**

#### Setup Flipbook:
```bash
# Copy plugin to XAMPP
xcopy /E /I "c:\CODING\flipbook-plugin" "c:\xampp\htdocs\flipbook"

# Open in browser
http://localhost/flipbook/install/install.php
```

---

### **Option 2: Use Your Production Server**
**Time:** 5 minutes
**Best for:** Quick testing without local install

Since you already have SiteGround hosting:

```bash
# Upload plugin via FTP to a test subdirectory
# Example: https://largerthanlifecomics.com/flipbook-test/

# Run installer there
https://largerthanlifecomics.com/flipbook-test/install/install.php

# Test everything
# Then deploy to production location when confirmed working
```

---

### **Option 3: Laragon (Alternative to XAMPP)**
**Time:** 10 minutes
**Best for:** Modern, lightweight setup

1. Download: https://laragon.org/download/
2. Install (auto-installs PHP, MySQL, Apache)
3. Start services
4. Copy plugin to `C:\laragon\www\flipbook`
5. Visit: `http://flipbook.test/install/install.php`

---

### **Option 4: Docker (Advanced)**
**Time:** 15 minutes
**Best for:** Developers, exact production match

```bash
# Install Docker Desktop
# Then use this docker-compose.yml:

version: '3.8'
services:
  php:
    image: php:8.1-apache
    ports:
      - "8000:80"
    volumes:
      - ./flipbook-plugin:/var/www/html
    depends_on:
      - mysql
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: flipbook
    ports:
      - "3306:3306"
```

---

## 📋 Step-by-Step: XAMPP Setup (Most Common)

### Step 1: Download & Install XAMPP

```
1. Go to: https://www.apachefriends.org/download.html
2. Download: "XAMPP for Windows" (PHP 8.0+)
3. Run installer: xampp-windows-x64-8.x.x-x-VS16-installer.exe
4. Install location: C:\xampp
5. Click through installer (all defaults OK)
```

### Step 2: Start Services

```
1. Open: C:\xampp\xampp-control.exe
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Both should show green "Running"
```

If ports 80/443 are in use (Skype, IIS):
```
- Click "Config" next to Apache
- Select "httpd.conf"
- Change "Listen 80" to "Listen 8080"
- Change "ServerName localhost:80" to "ServerName localhost:8080"
- Save and restart Apache
```

### Step 3: Copy Plugin to XAMPP

```bash
# Option A: Command line
xcopy /E /I "c:\CODING\flipbook-plugin" "C:\xampp\htdocs\flipbook"

# Option B: Manual
# Copy folder: c:\CODING\flipbook-plugin
# Paste to: C:\xampp\htdocs\flipbook
```

### Step 4: Create Database

Open phpMyAdmin:
```
http://localhost/phpmyadmin
```

Create database:
```sql
CREATE DATABASE flipbook_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or use SQL tab and run:
```sql
CREATE DATABASE flipbook_test;
CREATE USER 'flipbook'@'localhost' IDENTIFIED BY 'flipbook123';
GRANT ALL PRIVILEGES ON flipbook_test.* TO 'flipbook'@'localhost';
FLUSH PRIVILEGES;
```

### Step 5: Run Installer

1. **Open browser:**
   ```
   http://localhost/flipbook/install/install.php
   ```

2. **Step 1: Requirements Check**
   - Should auto-check and pass
   - Click "Check Requirements"

3. **Step 2: Database Config**
   - Host: `localhost`
   - Database: `flipbook_test`
   - User: `root` (or `flipbook` if you created user)
   - Password: (blank for root, or `flipbook123`)
   - Prefix: `flipbook_`
   - Click "Test Connection"

4. **Step 3: Admin Account**
   - Username: `admin`
   - Password: `test123456` (or your choice)
   - Confirm password
   - Click "Install"

5. **Step 4: Complete**
   - Installation successful!
   - **IMPORTANT:** Delete install folder:
   ```bash
   rmdir /S /Q "C:\xampp\htdocs\flipbook\install"
   ```

### Step 6: Login & Test

1. **Go to admin:**
   ```
   http://localhost/flipbook/src/admin/login.php
   ```

2. **Login:**
   - Username: `admin`
   - Password: (what you set)

3. **Create test flipbook:**
   - Click "+ Create New Flipbook"
   - Title: "Test Book"
   - Description: "Testing local install"
   - Upload a small PDF (any PDF, even 1-2 pages)
   - Click through wizard
   - Wait for conversion
   - Save

4. **View flipbook:**
   - Copy the viewer URL
   - Open in new tab
   - Should display your flipbook!

---

## ✅ Success Checklist

After setup, verify:

- [ ] XAMPP Apache is running (green)
- [ ] XAMPP MySQL is running (green)
- [ ] Can access: http://localhost/flipbook/
- [ ] Installer completed successfully
- [ ] Install folder deleted
- [ ] Can login to admin
- [ ] Can create test flipbook
- [ ] PDF converts to pages
- [ ] Can view flipbook publicly
- [ ] Pages flip correctly
- [ ] Can upload audio (optional)
- [ ] Audio plays (optional)

---

## 🐛 Troubleshooting

### "Apache won't start"
**Problem:** Port 80 already in use
**Solution:**
1. Stop IIS/Skype/other services
2. Or change Apache to port 8080 (see above)
3. Access via `http://localhost:8080/flipbook/`

### "MySQL won't start"
**Problem:** Port 3306 already in use
**Solution:**
1. Stop other MySQL services
2. Or change MySQL port in XAMPP config
3. Update database host in installer

### "Config file not found"
**Problem:** Installer didn't complete
**Solution:**
1. Check `C:\xampp\htdocs\flipbook\src\config.php` exists
2. If not, re-run installer
3. Or manually copy `config.example.php` to `src/config.php`

### "Permission denied on uploads"
**Problem:** Uploads folder not writable
**Solution:**
```bash
# Right-click uploads folder
# Properties → Security → Edit
# Add "Users" with "Full Control"
```

### "Database connection failed"
**Problem:** Wrong credentials
**Solution:**
- XAMPP default: user=`root`, password=(blank)
- Try: host=`127.0.0.1` instead of `localhost`
- Verify MySQL is running in XAMPP

### "PDF won't convert"
**Problem:** PHP extensions missing
**Solution:**
1. Open `C:\xampp\php\php.ini`
2. Enable: `extension=gd`
3. Restart Apache
4. Try upload again

---

## 🎯 Quick Test Script

After setup, run this to verify everything:

```php
<?php
// Save as: C:\xampp\htdocs\flipbook\test.php

echo "<h1>Flipbook Plugin Test</h1>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "Version: " . phpversion();
echo (version_compare(phpversion(), '7.4.0', '>=') ? " ✅" : " ❌") . "<br>";

// Test 2: Required Extensions
echo "<h2>2. PHP Extensions</h2>";
$required = ['pdo', 'pdo_mysql', 'gd', 'json'];
foreach ($required as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "✅" : "❌") . "<br>";
}

// Test 3: Config File
echo "<h2>3. Config File</h2>";
echo "Exists: " . (file_exists(__DIR__ . '/src/config.php') ? "✅" : "❌") . "<br>";

// Test 4: Database Connection
echo "<h2>4. Database Connection</h2>";
if (file_exists(__DIR__ . '/src/config.php')) {
    require_once __DIR__ . '/src/config.php';
    require_once __DIR__ . '/src/FlipbookDB.php';
    try {
        $db = new FlipbookDB();
        echo "Connection: ✅<br>";
        echo "Tables exist: " . ($db->tablesExist() ? "✅" : "❌") . "<br>";
    } catch (Exception $e) {
        echo "Connection: ❌ " . $e->getMessage() . "<br>";
    }
}

// Test 5: Uploads Directory
echo "<h2>5. Uploads Directory</h2>";
$uploadDir = __DIR__ . '/uploads';
echo "Exists: " . (file_exists($uploadDir) ? "✅" : "❌") . "<br>";
echo "Writable: " . (is_writable($uploadDir) ? "✅" : "❌") . "<br>";

echo "<br><h2>Ready to use!</h2>";
echo '<a href="/flipbook/src/admin/login.php">Go to Admin</a>';
?>
```

Visit: `http://localhost/flipbook/test.php`

---

## 💡 Recommended: XAMPP

**I recommend XAMPP because:**
- ✅ Easiest to install (10 minutes)
- ✅ Includes everything (PHP, MySQL, phpMyAdmin)
- ✅ Works on Windows perfectly
- ✅ No configuration needed
- ✅ Can test offline
- ✅ Free and open source
- ✅ Most popular (huge community)

**Download:** https://www.apachefriends.org/download.html

---

## 🚀 After Local Testing Works

Once you confirm everything works locally:

1. ✅ **Confidence:** Plugin works standalone!
2. ✅ **Deploy:** Upload to production with confidence
3. ✅ **Migrate:** Move your Heyzine flipbooks
4. ✅ **Launch:** Update your main site

---

## Need Help?

I'm here! Let me know which option you want to try and I'll guide you through it step by step.

**Recommendation:** Install XAMPP → It's the fastest way to test locally.

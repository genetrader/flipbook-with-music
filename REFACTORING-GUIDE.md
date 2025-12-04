# Refactoring Guide

## Files That Need Manual Updates

After copying the files from the original project, these files need to be refactored to use the new portable configuration:

### 1. Admin Files

#### `/src/admin/login.php`

**Changes needed:**
```php
// OLD (line 2):
session_start();

// NEW:
session_name(FLIPBOOK_SESSION_NAME);
session_start();

// OLD (lines 4-6):
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'NameNeg-1!!@@!!');

// NEW (add at top):
require_once __DIR__ . '/../config.php';

// Replace hardcoded credentials with:
if ($username === FLIPBOOK_ADMIN_USER && password_verify($password, FLIPBOOK_ADMIN_PASS))

// OLD (line 17):
header('Location: flipbook-admin-dashboard.php');

// NEW:
header('Location: dashboard.php');
```

#### `/src/admin/dashboard.php`

**Changes needed:**
```php
// Add at top:
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../FlipbookDB.php';

// OLD:
session_start();

// NEW:
session_name(FLIPBOOK_SESSION_NAME);
session_start();

// OLD:
require_once 'flipbook-config.php';
require_once 'flipbook-db.php';

// REMOVE (already included above)

// Update all file paths:
// OLD: './flipbook-admin-styles.css'
// NEW: './assets/admin.css'

// OLD: './flipbook-admin.js'
// NEW: './assets/admin.js'

// Update logout link:
// OLD: 'flipbook-admin-logout.php'
// NEW: 'logout.php'

// Update API endpoints:
// OLD: '../flipbook-api-*.php'
// NEW: '../api/*.php'
```

#### `/src/admin/logout.php`

**Changes needed:**
```php
// Add at top:
require_once __DIR__ . '/../config.php';

// OLD:
session_start();

// NEW:
session_name(FLIPBOOK_SESSION_NAME);
session_start();

// Update redirect:
// OLD: header('Location: flipbook-admin-login.php');
// NEW: header('Location: login.php');
```

### 2. API Files

All files in `/src/api/` need these changes:

```php
// Add at top of EVERY API file:
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../FlipbookDB.php';

session_name(FLIPBOOK_SESSION_NAME);
session_start();

// Update authentication check:
if (!isset($_SESSION['flipbook_admin_logged_in']) || !$_SESSION['flipbook_admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Replace all instances of:
// OLD: require_once 'flipbook-config.php';
// OLD: require_once 'flipbook-db.php';
// (already included above)
```

#### `/src/api/save-images.php`

**Additional changes:**
```php
// OLD:
$uploadDir = __DIR__ . '/flipbook-uploads/pages/';

// NEW:
$uploadDir = FLIPBOOK_UPLOAD_DIR . 'pages/';
```

### 3. Public Viewer

#### `/src/public/viewer.php`

**Changes needed:**
```php
// Add at top:
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../FlipbookDB.php';

// Remove old includes:
// OLD: require_once 'flipbook-config.php';
// OLD: require_once 'flipbook-db.php';
```

### 4. Configuration References

Search and replace across ALL files:

| Old Constant | New Constant |
|-------------|-------------|
| `DB_HOST` | `FLIPBOOK_DB_HOST` |
| `DB_NAME` | `FLIPBOOK_DB_NAME` |
| `DB_USER` | `FLIPBOOK_DB_USER` |
| `DB_PASS` | `FLIPBOOK_DB_PASS` |
| `DB_CHARSET` | `FLIPBOOK_DB_CHARSET` |
| `UPLOAD_DIR` | `FLIPBOOK_UPLOAD_DIR` |
| `MAX_FILE_SIZE` | `FLIPBOOK_MAX_FILE_SIZE` |

### 5. Upload Directory References

Replace all hardcoded paths:

```php
// OLD:
__DIR__ . '/flipbook-uploads/'

// NEW:
FLIPBOOK_UPLOAD_DIR
```

## Automated Refactoring Script

You can use this bash script to automate some changes:

```bash
#!/bin/bash

# Navigate to plugin directory
cd /path/to/flipbook-plugin/src

# Update file references
find . -type f -name "*.php" -exec sed -i 's/flipbook-admin-login\.php/login.php/g' {} +
find . -type f -name "*.php" -exec sed -i 's/flipbook-admin-dashboard\.php/dashboard.php/g' {} +
find . -type f -name "*.php" -exec sed -i 's/flipbook-admin-logout\.php/logout.php/g' {} +
find . -type f -name "*.php" -exec sed -i 's/flipbook-admin-styles\.css/assets\/admin.css/g' {} +
find . -type f -name "*.php" -exec sed -i 's/flipbook-admin\.js/assets\/admin.js/g' {} +

# Update require statements
find . -type f -name "*.php" -exec sed -i "s/require_once 'flipbook-config\.php';/require_once __DIR__ . '\/.\.\/config.php';/g" {} +
find . -type f -name "*.php" -exec sed -i "s/require_once 'flipbook-db\.php';/require_once __DIR__ . '\/.\.\/FlipbookDB.php';/g" {} +

echo "Refactoring complete! Manual review recommended."
```

## Testing Checklist

After refactoring, test:

- [ ] Installation wizard completes successfully
- [ ] Admin login works
- [ ] Dashboard loads without errors
- [ ] Can create new flipbook
- [ ] PDF upload and conversion works
- [ ] Audio upload works
- [ ] Audio assignment saves
- [ ] Public viewer displays flipbook
- [ ] Audio plays and crossfades
- [ ] Navigation works (prev/next pages)
- [ ] Logout redirects correctly
- [ ] Can delete flipbooks

## Common Issues

### "Config file not found"
- Check require_once paths use `__DIR__`
- Verify config.php exists in /src/

### "Session errors"
- Ensure `session_name(FLIPBOOK_SESSION_NAME)` called before `session_start()`
- Check session name is defined in config.php

### "Database connection failed"
- Verify constants use `FLIPBOOK_` prefix
- Check FlipbookDB.php uses new constants

### "Upload directory not found"
- Update all paths to use `FLIPBOOK_UPLOAD_DIR`
- Ensure directories created during install

---

**Note:** This guide assumes you're refactoring from the original Larger Than Life Comics implementation. Adapt as needed for your specific setup.

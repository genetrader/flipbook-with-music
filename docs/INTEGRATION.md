# Integration Guide

This guide explains how to integrate the Flipbook Plugin into your existing website.

## Table of Contents

1. [Installation Methods](#installation-methods)
2. [Basic Integration](#basic-integration)
3. [Advanced Integration](#advanced-integration)
4. [WordPress Integration](#wordpress-integration)
5. [Custom Themes](#custom-themes)
6. [API Usage](#api-usage)

---

## Installation Methods

### Method 1: Subdirectory Install (Recommended)

Install the plugin in a subdirectory of your website:

```
your-website.com/
├── index.html
├── about.html
└── flipbook/          ← Plugin here
    ├── src/
    ├── uploads/
    └── install/
```

**URL Structure:**
- Admin: `your-website.com/flipbook/src/admin/login.php`
- Viewer: `your-website.com/flipbook/src/public/viewer.php?id=1`

### Method 2: Subdomain Install

Install on a dedicated subdomain:

```
books.your-website.com/
├── src/
├── uploads/
└── install/
```

**URL Structure:**
- Admin: `books.your-website.com/src/admin/login.php`
- Viewer: `books.your-website.com/src/public/viewer.php?id=1`

### Method 3: Root Install

Install in your web root (not recommended for existing sites):

```
your-website.com/
├── src/
├── uploads/
└── install/
```

---

## Basic Integration

### 1. Direct Links

Simply link to your flipbooks:

```html
<a href="/flipbook/src/public/viewer.php?id=1" target="_blank">
    Read Comic #1
</a>
```

### 2. iFrame Embed

Embed flipbooks inline:

```html
<iframe
    src="/flipbook/src/public/viewer.php?id=1"
    width="100%"
    height="800px"
    frameborder="0"
    allowfullscreen>
</iframe>
```

**Responsive iFrame:**

```html
<style>
.flipbook-container {
    position: relative;
    width: 100%;
    padding-bottom: 75%; /* 4:3 aspect ratio */
    height: 0;
    overflow: hidden;
}

.flipbook-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}
</style>

<div class="flipbook-container">
    <iframe src="/flipbook/src/public/viewer.php?id=1"></iframe>
</div>
```

### 3. Modal/Lightbox Integration

Open flipbooks in a modal:

```html
<style>
.flipbook-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 9999;
}

.flipbook-modal.active {
    display: block;
}

.flipbook-modal iframe {
    width: 90%;
    height: 90%;
    position: absolute;
    top: 5%;
    left: 5%;
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 40px;
    color: white;
    font-size: 40px;
    cursor: pointer;
    z-index: 10000;
}
</style>

<button onclick="openFlipbook(1)">Read Book</button>

<div id="flipbook-modal" class="flipbook-modal">
    <span class="close-modal" onclick="closeFlipbook()">&times;</span>
    <iframe id="flipbook-frame" src=""></iframe>
</div>

<script>
function openFlipbook(id) {
    const modal = document.getElementById('flipbook-modal');
    const frame = document.getElementById('flipbook-frame');
    frame.src = `/flipbook/src/public/viewer.php?id=${id}`;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeFlipbook() {
    const modal = document.getElementById('flipbook-modal');
    const frame = document.getElementById('flipbook-frame');
    modal.classList.remove('active');
    frame.src = '';
    document.body.style.overflow = '';
}

// ESC key to close
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeFlipbook();
});
</script>
```

---

## Advanced Integration

### Dynamic Flipbook Gallery

Create a gallery with multiple flipbooks:

```html
<div class="flipbook-gallery">
    <div class="flipbook-card" data-id="1">
        <img src="/flipbook/uploads/pdfs/cover-1.jpg" alt="Book 1">
        <h3>Cork Chapter 1</h3>
        <button onclick="openFlipbook(1)">Read Now</button>
    </div>

    <div class="flipbook-card" data-id="2">
        <img src="/flipbook/uploads/pdfs/cover-2.jpg" alt="Book 2">
        <h3>Cork Chapter 2</h3>
        <button onclick="openFlipbook(2)">Read Now</button>
    </div>
</div>

<style>
.flipbook-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    padding: 20px;
}

.flipbook-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s;
}

.flipbook-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

.flipbook-card img {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.flipbook-card h3 {
    padding: 15px;
    margin: 0;
}

.flipbook-card button {
    width: 100%;
    padding: 12px;
    border: none;
    background: #667eea;
    color: white;
    cursor: pointer;
    font-size: 16px;
}

.flipbook-card button:hover {
    background: #5568d3;
}
</style>
```

### Load Flipbooks Dynamically via API

```javascript
// Fetch flipbook list from your database
async function loadFlipbooks() {
    const response = await fetch('/flipbook/src/api/get-all.php');
    const flipbooks = await response.json();

    const gallery = document.getElementById('flipbook-gallery');

    flipbooks.forEach(book => {
        const card = document.createElement('div');
        card.className = 'flipbook-card';
        card.innerHTML = `
            <img src="/flipbook/uploads/pdfs/${book.cover}" alt="${book.title}">
            <h3>${book.title}</h3>
            <p>${book.description}</p>
            <button onclick="openFlipbook(${book.id})">Read Now</button>
        `;
        gallery.appendChild(card);
    });
}

loadFlipbooks();
```

---

## WordPress Integration

### As a Shortcode

Create a WordPress plugin wrapper:

```php
<?php
/*
Plugin Name: Flipbook Integration
Description: Integrates Flipbook Plugin into WordPress
Version: 1.0
*/

function flipbook_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => 1,
        'width' => '100%',
        'height' => '800px'
    ], $atts);

    $url = site_url('/flipbook/src/public/viewer.php?id=' . $atts['id']);

    return sprintf(
        '<iframe src="%s" width="%s" height="%s" frameborder="0"></iframe>',
        esc_url($url),
        esc_attr($atts['width']),
        esc_attr($atts['height'])
    );
}
add_shortcode('flipbook', 'flipbook_shortcode');
?>
```

**Usage in WordPress:**
```
[flipbook id="1"]
[flipbook id="2" width="100%" height="600px"]
```

### As a Gutenberg Block

Create a custom block:

```javascript
wp.blocks.registerBlockType('custom/flipbook', {
    title: 'Flipbook',
    icon: 'book',
    category: 'embed',
    attributes: {
        flipbookId: {
            type: 'number',
            default: 1
        }
    },
    edit: function(props) {
        return wp.element.createElement('div', {},
            'Flipbook ID: ',
            wp.element.createElement('input', {
                type: 'number',
                value: props.attributes.flipbookId,
                onChange: (e) => {
                    props.setAttributes({ flipbookId: parseInt(e.target.value) });
                }
            })
        );
    },
    save: function(props) {
        const url = `/flipbook/src/public/viewer.php?id=${props.attributes.flipbookId}`;
        return wp.element.createElement('iframe', {
            src: url,
            width: '100%',
            height: '800px',
            frameborder: '0'
        });
    }
});
```

---

## Custom Themes

### Theming the Viewer

The viewer can be customized by editing `/src/public/viewer.php`:

```css
/* Change viewer background */
body {
    background: #1a1a1a; /* Dark theme */
}

/* Change navigation buttons */
.nav-button {
    background: #ff0000; /* Red buttons */
    color: white;
}

/* Change loading spinner color */
.spinner {
    border-top-color: #00ff00; /* Green spinner */
}
```

### Creating Theme Presets

Create multiple viewer themes:

```
/src/public/
├── viewer.php          (default)
├── viewer-dark.php     (dark theme)
├── viewer-minimal.php  (minimal UI)
└── viewer-comic.php    (comic book style)
```

Then link to specific themes:
```html
<a href="/flipbook/src/public/viewer-dark.php?id=1">Dark Theme</a>
```

---

## API Usage

### Get Flipbook Data

```php
<?php
require_once '/path/to/flipbook/src/config.php';
require_once '/path/to/flipbook/src/FlipbookDB.php';

$db = new FlipbookDB();
$flipbook = $db->getCompleteFlipbook(1);

echo "Title: " . $flipbook['flipbook']['title'];
echo "Pages: " . count($flipbook['pages']);
?>
```

### Create Flipbook Programmatically

```php
<?php
$db = new FlipbookDB();

// Create flipbook
$id = $db->createFlipbook('My Book', 'Description', 'portrait');

// Add pages
for ($i = 1; $i <= 10; $i++) {
    $imagePath = "/uploads/pages/book-{$id}/page-{$i}.jpg";
    $db->addPage($id, $i, '', $imagePath);
}

// Update page count
$db->updatePageCount($id, 10);

echo "Flipbook created with ID: $id";
?>
```

### RESTful API Endpoint

Create a custom REST endpoint:

```php
<?php
// /flipbook/src/api/rest.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../FlipbookDB.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

$db = new FlipbookDB();

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single flipbook
            echo json_encode($db->getCompleteFlipbook($id));
        } else {
            // Get all flipbooks
            echo json_encode($db->getAllFlipbooks());
        }
        break;

    case 'POST':
        // Create new flipbook
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $db->createFlipbook($data['title'], $data['description']);
        echo json_encode(['id' => $newId]);
        break;

    case 'DELETE':
        // Delete flipbook
        $result = $db->deleteFlipbook($id);
        echo json_encode(['success' => $result]);
        break;
}
?>
```

---

## Troubleshooting Integration

### Cross-Origin Issues
If embedding on a different domain, add CORS headers:

```php
// In viewer.php
header('Access-Control-Allow-Origin: https://your-main-site.com');
```

### Session Conflicts
If your site uses sessions, the plugin uses a unique session name to avoid conflicts:

```php
session_name(FLIPBOOK_SESSION_NAME); // Defaults to 'flipbook_admin_session'
```

### Path Issues
Always use absolute URLs for iframe src to avoid path confusion.

---

## Support

Need help with integration?

- **Documentation:** [/docs](/docs)
- **Issues:** [GitHub Issues](https://github.com/yourusername/flipbook-plugin/issues)
- **Email:** support@largerthanlifecomics.com

---

**Pro Tip:** Test your integration on different devices (mobile, tablet, desktop) to ensure responsive behavior works correctly.

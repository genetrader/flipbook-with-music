# Flipbook Admin Access & Codebase Reference

**Last Updated:** 2025-10-30

---

## Admin System URLs

### Primary Admin System (PHP + MySQL - Recommended)

**Production URLs:**
- **Login:** `https://largerthanlifecomics.com/flipbook-admin-login.php`
- **Dashboard:** `https://largerthanlifecomics.com/flipbook-admin-dashboard.php`
- **Logout:** `https://largerthanlifecomics.com/flipbook-admin-logout.php`

**Login Credentials:**
- Username: `admin`
- Password: `NameNeg-1!!@@!!`

**Location in Code:** `flipbook-admin-login.php:5-6`

---

### Legacy Admin (HTML - localStorage based)

**Production URL:**
- `https://largerthanlifecomics.com/flipbook-admin.html`

**Note:** This is the older system using localStorage. The PHP admin above is the modern, database-driven solution.

---

## Public Flipbook Viewer

**URL Pattern:**
- `https://largerthanlifecomics.com/flipbook-public-viewer.php?id={FLIPBOOK_ID}`

---

## Admin Dashboard Features

1. **Create New Flipbooks**
   - Upload PDF files
   - Auto-extract pages to images
   - Set title, description, orientation

2. **Audio Management**
   - Upload MP3 files
   - Assign audio to specific pages
   - 2-second crossfade between tracks

3. **Flipbook Management**
   - View all created flipbooks
   - Edit existing flipbooks
   - Delete flipbooks and associated data
   - Generate public viewer links

4. **Step-by-Step Wizard**
   - Step 1: Upload PDF
   - Step 2: Convert pages
   - Step 3: Upload audio
   - Step 4: Assign audio to pages
   - Step 5: Save and view

---

## Database Information

**Database Name:** dbqdamfvejqcqx
**Host:** localhost
**User:** u7l02cbfbhokg
**Charset:** utf8mb4

**Tables:**
- `flipbooks` - Main flipbook metadata
- `pages` - Individual page images (base64 encoded)
- `audio_files` - Audio library (MP3s as base64)
- `page_audio_assignments` - Page-to-audio mappings

**Configuration File:** `flipbook-config.php`

---

## Codebase Summary

### Project Overview
Full-stack comic book publishing website for **Larger Than Life Comics** by Mike Waxman, featuring an interactive flipbook system with synchronized audio playback.

### Technology Stack
- **Frontend:** HTML5, CSS3, JavaScript, jQuery 3.7.1
- **Backend:** PHP + MySQL/MariaDB
- **Key Libraries:**
  - StPageFlip (page-flip animations)
  - Web Audio API (audio crossfades)
  - PDF.js (PDF to image conversion)
  - Brevo Forms (newsletter)
- **Hosting:** SiteGround (Apache server)

---

## Main Website Files

### Core Pages
- `index.html` - Homepage with comic showcase
- `art-gallery.html` - Interactive gallery (23+ artworks)
- `writing.html` - "Head in the Clouds" story display
- `newsletter-form.html` - Newsletter signup
- `admin.html` - Simple website admin panel

### Stylesheets
- `styles.css` (v12) - Main website styles
- `gallery-styles.css` - Gallery-specific styles
- `flipbook-admin-styles.css` - Admin panel styles

### JavaScript
- `script.js` (v7) - Main website functionality
- `gallery-script.js` - Lightbox viewer
- `flipbook-admin.js` - Admin panel logic

---

## Flipbook System Files

### Admin Interface (HTML)
- `flipbook-admin.html` - Simple admin (localStorage)
- `flipbook-admin-login.php` - PHP login page
- `flipbook-admin-dashboard.php` - Main dashboard
- `flipbook-admin-logout.php` - Logout handler

### Viewer Files
- `flipbook-public-viewer.php` - **Main public viewer** (DB-driven)
- `flipbook-viewer.html` - Legacy viewer (localStorage)
- `flipbook-single-page.html` - Single page mode
- `flipbook-audio-test.html` - Testing page
- `flipbook-cork5.html` - Specific chapter viewer

### PHP Backend APIs
- `flipbook-config.php` - Database configuration
- `flipbook-db.php` - Database abstraction layer
- `flipbook-schema.sql` - Database schema
- `flipbook-api-save.php` - Save flipbook endpoint
- `flipbook-api-get.php` - Retrieve flipbook endpoint
- `flipbook-api-delete.php` - Delete flipbook endpoint
- `flipbook-api-save-images.php` - Save images endpoint

### Utilities & Setup
- `flipbook-setup-wizard.php` - Initial setup wizard
- `run-migration.php` - Database migration script
- `run-audio-migration.php` - Audio system migration
- `test-audio-save.php` - Audio testing utility
- `check-*.php` - Various diagnostic tools

---

## Recent Development Work

### Primary Focus: iOS/Android Audio Compatibility

**Last 15 Commits** focused on audio system fixes:

1. **Latest:** Debug logs for PC audio issues
2. iOS audio unlock mechanism (silent audio playback)
3. Web Audio API crossfade implementation (2-second fade)
4. AudioContext resume handling for mobile
5. Volume normalization and fade transitions
6. User interaction detection before audio init

### Audio Code Location

**File:** `flipbook-public-viewer.php` (lines 724-851)

**Key Functions:**
- `handlePageAudio()` (725-752) - Main audio controller
- `fadeOutAndStop()` (754-780) - Fade out for pages without audio
- `crossfadeAudio()` (783-817) - 2-second smooth crossfade
- `startNewAudio()` (820-851) - Initialize new audio with fade-in
- iOS-specific autoplay restriction workarounds (1042-1065)

**Audio Configuration:**
- Crossfade duration: 2000ms (2 seconds)
- Default volume: 0.5 (50%)
- Loop: true (background music loops)
- Fade interval: 50ms (smooth transitions)

### Current Audio Status
- **Android:** Smooth crossfade working ✅
- **iOS:** Abrupt transitions (intentional revert to working version)
- **PC:** Being debugged with console logs

---

## Directory Structure

```
C:\CODING\LARGER THEN LIFE MOCKUP\
│
├── .git/                           # Git repository
├── .claude/                        # Claude Code settings
│
├── images/                         # Website images
│   ├── cork-*-cover.jpeg          # Comic covers
│   ├── prodigy-cover.jpeg
│   ├── new-dawn-cover.jpeg
│   ├── loneliness-fix-cover.jpg
│   └── 2025-07-23_*.png           # Character artwork
│
├── mike artwork/                   # Art gallery
│   ├── *.jfif, *.jpg              # Original artwork (25 files)
│   └── Cork_Chapter3_*.pdf
│
├── music/                          # Audio files
│   ├── Mr. Blue Sky.mp3
│   ├── Let Me Make You Proud.mp3
│   ├── BOX 15.mp3
│   └── 048. Alphys.mp3
│
├── writing/                        # Writing samples
│   ├── Head in the Clouds.pdf
│   └── header/middle/footer.png
│
├── Main Website Files              # Core pages
├── Newsletter & Forms              # Brevo integration
├── Admin System                    # Website admin
├── Flipbook System (Custom)        # HTML/JS/PHP
├── Documentation                   # *.md files
└── Configuration & Deployment      # .htaccess, deploy scripts
```

---

## Main Features

### 1. Website Features
- Homepage with comic showcase
- Interactive art gallery (23+ images)
- Writing section with story display
- Newsletter subscription (Brevo integration)
- Amazon book link (The Loneliness Fix)
- Lightbox comic reader
- SEO optimized (Open Graph, Schema.org)

### 2. Flipbook System Features
- **Two-Tier System:**
  - Legacy: Heyzine-hosted (Cork 2-6)
  - Modern: Custom PHP/MySQL (all new flipbooks)

- **Admin Capabilities:**
  - PDF upload and auto-extraction
  - MP3 audio management
  - Page-specific audio assignment
  - Metadata editing
  - Export to JSON
  - Full CRUD operations

- **Viewer Features:**
  - Slide-and-fade page transitions (600ms)
  - 2-second audio crossfade
  - Zoom/pan functionality
  - Volume control and mute
  - Keyboard + click navigation
  - Responsive mobile/desktop design
  - Loading spinner with progress bar
  - Lazy image loading

### 3. Audio System
- Web Audio API for smooth crossfades
- iOS autoplay restriction workarounds
- User interaction detection
- Silent audio unlock mechanism
- Volume normalization (0.5 default)
- Fade-in/fade-out transitions
- Loop support for background music

---

## SEO & Marketing

- Open Graph tags for social sharing
- Schema.org structured data
- Twitter Card integration
- Google reCAPTCHA on forms
- Sitemap.xml and robots.txt
- Canonical URLs
- Responsive meta viewport

---

## Security Features

- Session-based authentication
- .htaccess protection
- htpasswd support
- SQL prepared statements
- Foreign key constraints
- Password hashing considerations

---

## Deployment Configuration

**Web Server:** Apache (SiteGround)
- .htaccess configured
- URL rewriting enabled
- PHP execution allowed
- MySQL database access

**Upload Directories:**
- `flipbook-uploads/pdfs/` - PDF storage
- `flipbook-uploads/audio/` - Audio files
- `flipbook-uploads/pages/` - Extracted pages
- Permissions: 0755 (directories)

---

## Documentation Files

- `FLIPBOOK-SYSTEM-README.md` - Flipbook system overview
- `FLIPBOOK-SETUP-INSTRUCTIONS.md` - Setup guide
- `FLIPBOOK-ADMIN-ACCESS.md` - **This file** (URLs & credentials)
- `brevo-sender-instructions.txt` - Email setup
- `deploy-to-siteground.txt` - Deployment guide
- `.htpasswd-instructions.txt` - Password protection

---

## Quick Reference

### To Access Admin:
1. Go to: `https://largerthanlifecomics.com/flipbook-admin-login.php`
2. Username: `admin`
3. Password: `NameNeg-1!!@@!!`
4. Create/manage flipbooks from dashboard

### To View Flipbook:
- URL: `https://largerthanlifecomics.com/flipbook-public-viewer.php?id={ID}`
- Get ID from admin dashboard

### To Deploy Changes:
- Run: `deploy.bat` (Windows)
- Or use FTP to upload to SiteGround

---

## Git Repository

**Current Branch:** master
**Main Branch:** (not set)
**Status:** Clean working directory

**Recent Commit:** Add debug console logs to diagnose PC audio issue

---

## Notes

- **Audio System:** Actively being developed/debugged
- **iOS Issue:** Crossfade not smooth (reverted to abrupt transitions)
- **Android:** Working perfectly with smooth crossfades
- **PC Audio:** Currently being diagnosed with console logs

---

*This document serves as a quick reference for accessing the admin system and understanding the codebase structure after system reboots or extended breaks.*

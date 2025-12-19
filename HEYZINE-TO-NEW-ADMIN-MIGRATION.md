# Heyzine to New Admin Backend Migration Guide

**Date:** 2025-10-30
**Status:** PDFs Downloaded and Ready for Upload

---

## Overview

This guide documents the migration of all Heyzine-hosted flipbooks to the new custom admin backend. All PDF files have been downloaded from Heyzine's CDN and are ready for upload.

---

## Downloaded PDFs

All PDFs are located in: `C:\CODING\LARGER THEN LIFE MOCKUP\flipbook-pdfs\`

| File | Size | Pages | Title | Description |
|------|------|-------|-------|-------------|
| Cork_1.pdf | 9.4 MB | ? | Cork Chapter 1 | The beginning of Cork's adventure |
| Cork_2.pdf | 17 MB | 26 | Cork Chapter 2 | Cork's journey continues |
| Cork_3.pdf | 22 MB | 25 | Cork Chapter 3 - Dark, Damp & Dusty | Cork faces new challenges in the darkness |
| Cork_5.pdf | 18 MB | 43 | Cork Chapter 5 | Cork's adventure deepens |
| Cork_6_Prodigy.pdf | 19 MB | 24 | Prodigy (Cork Chapter 6) | The tale of a young prodigy in Cork's world |

**Note:** Cork Chapter 4 was a placeholder in the original code and has no PDF source.

---

## Original Heyzine URLs (for reference)

```javascript
// From script.js:3-9
const flipbooks = {
    'cork-1': 'https://heyzine.com/flip-book/b1f71ef0a6.html',
    'cork-2': 'https://heyzine.com/flip-book/1793b849ee.html',
    'cork-3': 'https://heyzine.com/flip-book/fe18813757.html',
    'cork-4': 'https://heyzine.com/flip-book/PLACEHOLDER4.html',  // Placeholder only
    'cork-5': 'https://heyzine.com/flip-book/77ee3f8242.html',
    'cork-6': 'https://heyzine.com/flip-book/91e79197a9.html'     // Prodigy
};
```

---

## Step-by-Step Upload Instructions

### 1. Access the Admin Panel

1. Navigate to: `https://largerthanlifecomics.com/flipbook-admin-login.php`
2. Login with credentials:
   - Username: `admin`
   - Password: `NameNeg-1!!@@!!`

### 2. Upload Each Flipbook

For each PDF file, follow these steps:

#### Cork Chapter 1

1. Click **"+ Create New Flipbook"** button
2. **Step 1: Basic Information & PDF Upload**
   - Title: `Cork Chapter 1`
   - Description: `The beginning of Cork's adventure`
   - Upload PDF: Select `Cork_1.pdf` from `flipbook-pdfs` folder
   - Click **"Next: Convert to Pages"**

3. **Step 2: Converting PDF**
   - Wait for automatic conversion (progress bar will show status)
   - Preview pages will appear as they convert
   - Click **"Next: Upload Audio"** when complete

4. **Step 3: Upload Audio** (Optional - skip for now)
   - Click **"Next: Assign Audio to Pages"**

5. **Step 4: Assign Audio** (Skip for now)
   - Click **"Save Flipbook"**

6. **Step 5: Success**
   - Note the Flipbook ID for reference
   - Click **"View Flipbook"** to test
   - Copy the viewer URL for later use

#### Cork Chapter 2

Repeat the same process with:
- Title: `Cork Chapter 2`
- Description: `Cork's journey continues`
- File: `Cork_2.pdf` (17 MB, 26 pages)

#### Cork Chapter 3

- Title: `Cork Chapter 3 - Dark, Damp & Dusty`
- Description: `Cork faces new challenges in the darkness`
- File: `Cork_3.pdf` (22 MB, 25 pages)

#### Cork Chapter 5

- Title: `Cork Chapter 5`
- Description: `Cork's adventure deepens`
- File: `Cork_5.pdf` (18 MB, 43 pages)

#### Cork Chapter 6 (Prodigy)

- Title: `Prodigy (Cork Chapter 6)`
- Description: `The tale of a young prodigy in Cork's world`
- File: `Cork_6_Prodigy.pdf` (19 MB, 24 pages)

---

## After Upload: Update Website Code

Once all flipbooks are uploaded and you have the new viewer URLs, update `script.js`:

### Current Code (script.js:3-9)

```javascript
const flipbooks = {
    'cork-1': 'https://heyzine.com/flip-book/b1f71ef0a6.html',
    'cork-2': 'https://heyzine.com/flip-book/1793b849ee.html',
    'cork-3': 'https://heyzine.com/flip-book/fe18813757.html',
    'cork-4': 'https://heyzine.com/flip-book/PLACEHOLDER4.html',
    'cork-5': 'https://heyzine.com/flip-book/77ee3f8242.html',
    'cork-6': 'https://heyzine.com/flip-book/91e79197a9.html'
};
```

### New Code (replace with actual IDs)

```javascript
const flipbooks = {
    'cork-1': 'https://largerthanlifecomics.com/flipbook-public-viewer.php?id=1',
    'cork-2': 'https://largerthanlifecomics.com/flipbook-public-viewer.php?id=2',
    'cork-3': 'https://largerthanlifecomics.com/flipbook-public-viewer.php?id=3',
    'cork-5': 'https://largerthanlifecomics.com/flipbook-public-viewer.php?id=4',
    'cork-6': 'https://largerthanlifecomics.com/flipbook-public-viewer.php?id=5'
};
```

**Important:** Replace the `?id=` numbers with the actual Flipbook IDs from the admin dashboard.

---

## Testing Checklist

After migration, test each flipbook:

- [ ] Cork Chapter 1 - Loads correctly
- [ ] Cork Chapter 2 - Loads correctly
- [ ] Cork Chapter 3 - Loads correctly
- [ ] Cork Chapter 5 - Loads correctly
- [ ] Cork Chapter 6 (Prodigy) - Loads correctly

### Test Items:
- [ ] Pages load and display correctly
- [ ] Navigation arrows work (prev/next)
- [ ] Zoom functionality works
- [ ] Mobile responsive
- [ ] No console errors
- [ ] Loading spinner displays
- [ ] Volume controls present (even without audio)

---

## Future: Adding Audio

Once flipbooks are created, you can return to add audio:

1. Go to admin dashboard
2. Click **"Edit"** on any flipbook
3. Navigate through the wizard to **Step 3: Upload Audio**
4. Upload MP3 files
5. Assign audio to specific pages
6. Save updates

---

## Advantages of New System vs Heyzine

### New Custom System ✅
- Full control over functionality
- Page-specific audio with 2-second crossfades
- No third-party dependencies
- Custom branding and styling
- Database-driven (easy to manage)
- No monthly fees
- Faster loading (self-hosted)
- iOS/Android audio optimization

### Old Heyzine System ⚠️
- External dependency
- Limited customization
- No audio control
- Monthly subscription required
- iframe embedding (slower)
- Can't modify functionality

---

## Rollback Plan

If issues arise, the original Heyzine URLs are still active and can be restored:

1. Revert `script.js` to use Heyzine URLs
2. Deploy the reverted code
3. Original flipbooks will work immediately

---

## Estimated Time

- **Upload Time per Flipbook:** 5-10 minutes (depends on page count)
- **Total Upload Time:** 30-50 minutes for all 5 flipbooks
- **Code Update:** 5 minutes
- **Testing:** 15-20 minutes

**Total Estimated Time:** 1-1.5 hours

---

## Notes

- The admin panel uses PDF.js for client-side conversion, so no server-side tools needed
- Large PDFs (20+ MB) may take longer to convert
- Conversion happens in the browser (no server processing)
- All pages are stored as file paths (not base64) for efficiency
- Audio can be added later without re-uploading PDFs

---

## Support

If you encounter issues during migration:

1. Check browser console for errors (F12)
2. Verify file sizes aren't too large (>50MB may cause issues)
3. Ensure you're logged into the admin panel
4. Try a different browser if conversion fails
5. Check PHP error logs on server: `error_log`

---

## Security Reminder

After migration is complete:

- [ ] Delete `flipbook-batch-upload.php` if created
- [ ] Consider changing admin password
- [ ] Verify .htaccess protection is in place
- [ ] Back up the database

---

*This migration preserves all your comic content while giving you full control over the flipbook system with advanced audio features and better mobile compatibility.*

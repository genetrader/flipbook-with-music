# Chapter Upload Feature - Documentation

**Date:** December 19, 2025
**Feature:** Folder Upload with Auto-Generated Chapter Title Slides

---

## What This Feature Does

Allows you to upload multiple folders (one per chapter) and automatically creates beautiful title slides between chapters based on folder names.

---

## How To Use

### Step 1: Enable Folder Upload Mode

1. Login to admin: https://largerthanlifecomics.com/flipbook-admin-login.php
2. Click "Create New Flipbook"
3. Enter flipbook title and description
4. Select "Upload Images" radio button
5. ✅ **Check the box**: "Upload by Folders (Auto-create chapter title slides from folder names)"

### Step 2: Select Your Folders

1. Click the upload area
2. Navigate to your parent folder (e.g., "Cork Episode 5")
3. Select the folder containing all your chapter subfolders
4. Click "Select Folder" or "Choose"

**Example Folder Structure:**
```
Cork Episode 5/
├── Chapter 1 - Origins/
│   ├── page-01.jpg
│   ├── page-02.jpg
│   └── page-03.jpg
├── Chapter 2 - Discovery/
│   ├── page-01.jpg
│   ├── page-02.jpg
│   └── page-03.jpg
└── Chapter 3 - Adventure/
    ├── page-01.jpg
    └── page-02.jpg
```

### Step 3: Edit Chapter Titles

After uploading, you'll see:

1. **Chapter Titles Editor** - Edit any chapter title before processing
2. **Preview Grid** - Shows chapter dividers and all pages in order

**Example:**
- Folder name: "chapter-1-origins"
- Auto-generated title: "Chapter 1 Origins"
- You can edit it to: "Chapter One: The Beginning"

### Step 4: Process Pages

1. Click "Next: Process Pages"
2. System will:
   - Create a title slide for each chapter
   - Process all images in order
   - Number pages sequentially across all chapters

### Step 5: Continue as Normal

- Add audio (Step 3)
- Assign audio to pages (Step 4)
- Save flipbook (Step 5)

---

## What You'll Get

### Auto-Generated Title Slides

Each chapter gets a beautiful title slide with:
- **Purple gradient background** (#667eea to #764ba2)
- **Decorative borders**
- **Large "CHAPTER X" text** (semi-transparent)
- **Chapter title** (white, bold, word-wrapped)
- **Book icon** 📖 at bottom

**Dimensions:** 1200x1600px (portrait)

### Page Structure

```
Page 1: 📖 CHAPTER 1 - Origins (title slide)
Page 2: Chapter 1, Image 1
Page 3: Chapter 1, Image 2
Page 4: Chapter 1, Image 3
Page 5: 📖 CHAPTER 2 - Discovery (title slide)
Page 6: Chapter 2, Image 1
Page 7: Chapter 2, Image 2
...
```

---

## Features

### Automatic Chapter Detection
- ✅ Detects folders automatically
- ✅ Sorts chapters alphabetically
- ✅ Sorts images within each chapter alphabetically
- ✅ Assigns sequential page numbers

### Smart Title Formatting
- Converts "chapter-1" → "Chapter 1"
- Converts "cork_origin" → "Cork Origin"
- Converts "the-discovery" → "The Discovery"

### Title Editing
- ✅ Edit any chapter title before processing
- ✅ Real-time preview updates
- ✅ Shows folder name and page count

### Visual Preview
- Purple chapter dividers in preview
- Shows chapter title and page count
- Sequential page numbering across chapters

---

## Browser Compatibility

### Folder Upload Requirements
- Chrome/Edge ✅ (Full support)
- Firefox ✅ (Full support)
- Safari ✅ (Full support)
- Must select parent folder containing chapter subfolders

### Known Limitations
- Can only select one parent folder at a time
- All chapter folders must be in the same parent folder
- Browser will upload ALL files in selected folder tree

---

## Tips & Best Practices

### 1. Naming Your Folders
**Good:**
- `chapter-1`, `chapter-2`, `chapter-3`
- `01-origins`, `02-discovery`, `03-adventure`
- `Cork Part 1`, `Cork Part 2`

**Avoid:**
- Random names that won't sort correctly
- Special characters (use hyphens or underscores)
- Very long folder names (will be word-wrapped)

### 2. Naming Your Images
**Good:**
- `page-01.jpg`, `page-02.jpg`, `page-03.jpg`
- `001.jpg`, `002.jpg`, `003.jpg`

**Why:** Files sort alphabetically, so numbered names ensure correct order

### 3. Image Format
- JPG (recommended for photos)
- PNG (recommended for line art)
- GIF (supported)

### 4. Folder Structure
```
✅ GOOD:
Parent Folder/
├── Chapter 1/
├── Chapter 2/
└── Chapter 3/

❌ BAD:
Chapter 1/
Chapter 2/
Chapter 3/
(separate locations - can't upload together)
```

---

## Comparison: Regular vs Folder Upload

### Regular Image Upload
- Select individual image files
- No chapter divisions
- Manual page ordering (by selection order)
- All pages at same level

### Folder Upload
- Select parent folder
- Automatic chapter detection
- Auto-generated title slides
- Automatic alphabetical ordering
- Organized by chapters

---

## Technical Details

### Chapter Title Slide Generation
- Created using HTML5 Canvas API
- Generated client-side (no server processing)
- Saved as base64 PNG data
- Same storage as regular images

### Folder Detection
- Uses `webkitdirectory` attribute
- Parses `webkitRelativePath` property
- Groups files by direct parent folder
- Sorts alphabetically

### Performance
- Processes all chapters in sequence
- Shows progress bar (0-100%)
- Preview renders as images load
- No page limit

---

## Troubleshooting

### "No chapters detected"
**Problem:** Files are in root, not in subfolders
**Solution:** Create subfolders for each chapter

### Chapters in wrong order
**Problem:** Folder names don't sort alphabetically
**Solution:** Rename folders with leading numbers (01, 02, 03)

### Images in wrong order within chapter
**Problem:** Image filenames don't sort alphabetically
**Solution:** Rename with leading numbers (page-01.jpg, page-02.jpg)

### Can't select folders
**Problem:** Browser doesn't support folder upload
**Solution:** Use Chrome, Edge, Firefox, or Safari

### Title slide text too long
**Problem:** Chapter title wraps to many lines
**Solution:** Shorten the chapter title in the editor

---

## Example Workflow

**Your Setup:**
```
Cork Episode 5/
├── 01 - The Beginning/
│   └── [15 images]
├── 02 - The Middle/
│   └── [20 images]
└── 03 - The End/
    └── [12 images]
```

**Steps:**
1. Check "Upload by Folders"
2. Select "Cork Episode 5" folder
3. System detects 3 chapters, 47 total images
4. Edit titles:
   - "01 - The Beginning" → "Chapter 1: Origins"
   - "02 - The Middle" → "Chapter 2: The Journey"
   - "03 - The End" → "Chapter 3: Resolution"
5. Click "Process Pages"
6. Result: 50 pages (3 title slides + 47 images)

---

## Database Storage

**No changes needed!**
- Chapter title slides stored as regular pages
- Same `pages` table, same structure
- Title slides are just images with text
- Viewer shows them like any other page

---

## Future Enhancements (Possible)

- [ ] Custom title slide templates
- [ ] Different gradient colors per chapter
- [ ] Upload custom title slide images instead of auto-generate
- [ ] Reorder chapters with drag-and-drop
- [ ] Skip title slides for specific chapters
- [ ] Add chapter markers in audio timeline

---

## Git Commit

**Commit:** 0139149
**Message:** "Add folder upload with auto-generated chapter title slides"

**Changes:**
- `flipbook-admin-dashboard.php` - Added UI for folder upload
- `flipbook-admin.js` - Added chapter processing logic

---

## Support

**Issue:** Chapter titles need editing
**Solution:** Edit them in the Chapter Titles Editor before clicking "Process Pages"

**Issue:** Want to change title slides after processing
**Solution:** Currently not supported - you'll need to re-upload and edit before processing

**Issue:** Don't want title slides
**Solution:** Uncheck "Upload by Folders" and upload images normally

---

**Status:** ✅ Deployed to production
**URL:** https://largerthanlifecomics.com/flipbook-admin-login.php
**Ready to use!**

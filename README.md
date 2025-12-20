# PDF Flipbook System

A complete PHP-based flipbook system for creating interactive, mobile-friendly digital flipbooks from PDFs or images with audio narration support.

## Features

- **PDF & Image Upload**: Convert PDFs or upload images to create flipbooks
- **Batch Upload**: Support for large flipbooks (143+ pages) with automatic batching
- **Folder Upload**: Upload entire folders with chapter organization and auto-generated title slides
- **Audio Narration**: Assign MP3 audio files to individual pages
- **Page Reordering**: Drag-and-drop interface to reorder pages before finalizing
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Touch Gestures**: Swipe left/right on mobile to navigate pages
- **Zoom Mode**: Click-to-zoom functionality with pan support (enabled by default)
- **Embed Codes**: Generate iframe embed codes (fixed height or responsive)
- **Natural Sorting**: Intelligent filename sorting (page-1, page-2, page-10 instead of page-1, page-10, page-2)

## Workflow

1. **Upload PDF/Images** - Choose PDF file, images, or folder with chapters
2. **Convert Pages** - System processes and displays page previews
3. **Reorder Pages** - Drag and drop to arrange pages in desired order
4. **Upload Audio** - Add MP3 files for narration (optional)
5. **Assign Audio** - Link audio files to specific pages (optional)
6. **Save** - Finalize and publish flipbook

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- GD Library (for image processing)
- PDO MySQL extension
- At least 256MB PHP memory limit (512MB recommended for large PDFs)

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/flipbook-system.git
   cd flipbook-system
   ```

2. **Create MySQL database:**
   ```sql
   CREATE DATABASE flipbook_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import database schema:**
   ```bash
   mysql -u your_user -p flipbook_db < flipbook-schema.sql
   ```

4. **Configure database connection:**
   - Edit `flipbook-config.php`
   - Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` with your credentials

5. **Set permissions:**
   ```bash
   chmod 755 flipbook-uploads/
   chmod 755 flipbook-images/
   ```

6. **Access the admin panel:**
   - Navigate to `flipbook-admin-login.php`
   - Default credentials: (you should change these in the database)

## File Structure

```
flipbook-system/
├── flipbook-admin-dashboard.php    # Admin interface
├── flipbook-admin-v6.js           # Admin JavaScript (latest version)
├── flipbook-admin-styles.css      # Admin styles
├── flipbook-admin-login.php       # Login page
├── flipbook-admin-logout.php      # Logout handler
├── flipbook-public-viewer.php     # Public flipbook viewer
├── flipbook-api-save.php          # Save/update flipbooks
├── flipbook-api-get.php           # Retrieve flipbook data
├── flipbook-api-delete.php        # Delete flipbooks
├── flipbook-db.php                # Database class
├── flipbook-config.php            # Configuration file
├── flipbook-schema.sql            # Database schema
└── README.md                      # This file
```

## Usage

### Creating a Flipbook

1. Log in to the admin dashboard
2. Click "Create New Flipbook"
3. Upload your PDF or images
4. Reorder pages if needed
5. Add audio narration (optional)
6. Save and publish

### Embedding a Flipbook

After creating a flipbook, click the "Embed Code" button to get:

**Fixed Height Embed:**
```html
<iframe src="https://yoursite.com/flipbook-public-viewer.php?id=1"
        allowfullscreen="allowfullscreen"
        scrolling="no"
        style="border: 1px solid lightgray; width: 100%; height: 600px;"
        allow="clipboard-write">
</iframe>
```

**Responsive Embed:**
```html
<div style="position: relative; width: 100%; padding-bottom: 133.33%; overflow: hidden;">
    <iframe src="https://yoursite.com/flipbook-public-viewer.php?id=1"
            allowfullscreen="allowfullscreen"
            scrolling="no"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 1px solid lightgray;"
            allow="clipboard-write">
    </iframe>
</div>
```

## Features Details

### Zoom Mode (Default ON)
- Click/tap on flipbook to zoom in/out
- Mouse/touch drag to pan around zoomed image
- Automatically enabled when flipbook loads
- Can be toggled with zoom button

### Touch Gestures (Mobile)
- **Swipe left**: Next page
- **Swipe right**: Previous page
- **Tap**: Toggle zoom
- **Drag (when zoomed)**: Pan around image

### Batch Upload
- Automatically handles large flipbooks (50+ pages)
- Splits uploads into 20-page batches to avoid server limits
- Processes seamlessly in background

### Page Reordering
- Drag-and-drop interface
- Shows original filenames for reference
- Preserves audio assignments when reordering
- Updates database with new page order

## Database Schema

The system uses 4 main tables:
- `flipbooks` - Stores flipbook metadata
- `pages` - Individual page data
- `audio_library` - MP3 audio files
- `page_audio_assignments` - Links pages to audio

See `flipbook-schema.sql` for complete schema.

## Configuration Options

Edit `flipbook-config.php` to customize:
- Upload directory location
- Maximum file size (default: 50MB)
- Allowed file types
- Error reporting settings

## Security Notes

- Always use HTTPS in production
- Change default admin credentials
- Set appropriate file permissions (755 for directories, 644 for files)
- Keep `flipbook-config.php` outside web root if possible
- Validate and sanitize all user inputs
- Use prepared statements for all database queries (already implemented)

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile Safari (iOS 12+)
- Chrome Mobile (Android 8+)

## Credits

Built with:
- PDF.js for PDF processing
- Native drag-and-drop API
- Touch events API for mobile gestures

## License

MIT License - Feel free to use and modify for your projects

## Support

For issues or questions, please open an issue on GitHub.

---

Built with ❤️ and Claude Code

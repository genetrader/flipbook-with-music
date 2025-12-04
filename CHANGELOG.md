# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned Features
- WordPress plugin version
- Batch PDF upload
- Theme marketplace
- Analytics dashboard
- Multi-language support

## [1.0.0] - 2025-01-XX

### Added
- Initial release of Flipbook Plugin
- PDF to flipbook conversion with automatic page extraction
- Interactive page-flip animations using StPageFlip
- Audio integration system with page-specific assignments
- 2-second smooth crossfades between audio tracks
- Admin dashboard with step-by-step wizard
- Session-based authentication system
- Public viewer with responsive design
- Mobile-friendly interface (iOS/Android support)
- iOS audio autoplay workarounds
- Zoom and pan functionality
- Keyboard navigation support
- Volume control and mute button
- Loading spinner with progress indicator
- Database abstraction layer with PDO
- Support for both base64 and file-based storage
- Installation wizard with requirements check
- Table prefix support for multi-instance deployment
- Comprehensive documentation
- MIT License

### Security
- Prepared SQL statements to prevent injection
- Session-based admin authentication
- File type validation on uploads
- Password hashing with bcrypt
- Soft-delete for data preservation

### Performance
- Lazy image loading
- File-based storage option (vs base64)
- Optimized database queries with indexes
- Client-side PDF conversion (no server overhead)

---

## Version History

### v1.0.0 - The Foundation
The first public release of the Flipbook Plugin, extracted from the Larger Than Life Comics website and refactored as a standalone, portable system.

**Core Features:**
- Complete admin system
- Public viewer
- Audio synchronization
- Mobile compatibility

**Technical Stack:**
- PHP 7.4+
- MySQL/MariaDB
- JavaScript (Vanilla)
- HTML5/CSS3
- PDF.js for PDF rendering
- StPageFlip for animations
- Web Audio API for crossfades

---

## Migration from Previous Versions

### From Heyzine-hosted to Self-hosted
If migrating from Heyzine or another service:

1. Download your PDF files
2. Install this plugin
3. Upload PDFs through admin
4. Update your site links to point to new viewer URLs
5. Optionally add audio enhancements

### From Base64 to File-based Storage
The plugin supports both methods:
- **Base64:** Legacy support, stores in database
- **File-based:** Recommended, stores as actual files

New installations default to file-based storage for better performance.

---

## Support

- **Issues:** [GitHub Issues](https://github.com/yourusername/flipbook-plugin/issues)
- **Documentation:** [/docs](/docs)
- **Email:** support@largerthanlifecomics.com

---

**Legend:**
- `Added` - New features
- `Changed` - Changes in existing functionality
- `Deprecated` - Soon-to-be removed features
- `Removed` - Removed features
- `Fixed` - Bug fixes
- `Security` - Security improvements

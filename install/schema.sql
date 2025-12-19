-- Flipbook Plugin Database Schema
-- This schema uses {{TABLE_PREFIX}} placeholder which will be replaced during installation
-- Supports both base64 storage (image_data/audio_data) and file path storage (image_path/audio_path)

-- Main flipbooks table
CREATE TABLE IF NOT EXISTS {{TABLE_PREFIX}}flipbooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    page_count INT NOT NULL DEFAULT 0,
    orientation VARCHAR(20) DEFAULT 'portrait', -- 'portrait' or 'landscape'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_created (created_at),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pages table - stores individual page images
CREATE TABLE IF NOT EXISTS {{TABLE_PREFIX}}pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flipbook_id INT NOT NULL,
    page_number INT NOT NULL,
    image_data LONGTEXT NOT NULL DEFAULT '', -- Base64 encoded image (legacy/fallback)
    image_path VARCHAR(500) DEFAULT NULL,     -- File path (recommended)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flipbook_id) REFERENCES {{TABLE_PREFIX}}flipbooks(id) ON DELETE CASCADE,
    INDEX idx_flipbook (flipbook_id),
    INDEX idx_page_number (page_number),
    UNIQUE KEY unique_page (flipbook_id, page_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audio files table
CREATE TABLE IF NOT EXISTS {{TABLE_PREFIX}}audio_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flipbook_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    audio_data LONGTEXT NOT NULL DEFAULT '',  -- Base64 encoded audio (legacy/fallback)
    audio_path VARCHAR(500) DEFAULT NULL,     -- File path (recommended)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flipbook_id) REFERENCES {{TABLE_PREFIX}}flipbooks(id) ON DELETE CASCADE,
    INDEX idx_flipbook (flipbook_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Page audio assignments table - links pages to audio files
CREATE TABLE IF NOT EXISTS {{TABLE_PREFIX}}page_audio_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_id INT NOT NULL,
    audio_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES {{TABLE_PREFIX}}pages(id) ON DELETE CASCADE,
    FOREIGN KEY (audio_id) REFERENCES {{TABLE_PREFIX}}audio_files(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (page_id),
    INDEX idx_page (page_id),
    INDEX idx_audio (audio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

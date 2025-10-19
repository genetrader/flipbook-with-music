-- Migration: Change pages table from base64 to file paths
-- This will migrate the system to use actual image files instead of base64

USE dbqdamfvejqcqx;

-- Add new column for image path
ALTER TABLE pages ADD COLUMN image_path VARCHAR(500) AFTER image_data;

-- Note: We'll keep image_data for now for backwards compatibility
-- Once all flipbooks are migrated, we can drop the image_data column with:
-- ALTER TABLE pages DROP COLUMN image_data;

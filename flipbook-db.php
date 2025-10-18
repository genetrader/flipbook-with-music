<?php
/**
 * Flipbook Database Class
 * Handles all database operations for the flipbook admin system
 */

class FlipbookDB {
    private $conn;
    private $error;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->error = "Database connection failed: " . $e->getMessage();
            error_log($this->error);
            throw new Exception("Database connection failed");
        }
    }

    /**
     * Get all flipbooks
     */
    public function getAllFlipbooks() {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, title, description, page_count, orientation, created_at, updated_at, is_active
                FROM flipbooks
                WHERE is_active = 1
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting flipbooks: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single flipbook by ID
     */
    public function getFlipbook($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, title, description, page_count, orientation, created_at, updated_at
                FROM flipbooks
                WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting flipbook: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new flipbook
     */
    public function createFlipbook($title, $description, $orientation = 'portrait') {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO flipbooks (title, description, orientation, page_count)
                VALUES (?, ?, ?, 0)
            ");
            $stmt->execute([$title, $description, $orientation]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating flipbook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update flipbook page count
     */
    public function updatePageCount($flipbookId, $pageCount) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE flipbooks
                SET page_count = ?
                WHERE id = ?
            ");
            return $stmt->execute([$pageCount, $flipbookId]);
        } catch (PDOException $e) {
            error_log("Error updating page count: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a page to a flipbook
     */
    public function addPage($flipbookId, $pageNumber, $imageData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO pages (flipbook_id, page_number, image_data)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE image_data = VALUES(image_data)
            ");
            return $stmt->execute([$flipbookId, $pageNumber, $imageData]);
        } catch (PDOException $e) {
            error_log("Error adding page: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all pages for a flipbook
     */
    public function getPages($flipbookId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, page_number, image_data
                FROM pages
                WHERE flipbook_id = ?
                ORDER BY page_number ASC
            ");
            $stmt->execute([$flipbookId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting pages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add an audio file
     */
    public function addAudioFile($flipbookId, $name, $audioData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO audio_files (flipbook_id, name, audio_data)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$flipbookId, $name, $audioData]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding audio file: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all audio files for a flipbook
     */
    public function getAudioFiles($flipbookId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, name, audio_data
                FROM audio_files
                WHERE flipbook_id = ?
                ORDER BY name ASC
            ");
            $stmt->execute([$flipbookId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting audio files: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Assign audio to a page
     */
    public function assignAudioToPage($pageId, $audioId) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO page_audio_assignments (page_id, audio_id)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE audio_id = VALUES(audio_id)
            ");
            return $stmt->execute([$pageId, $audioId]);
        } catch (PDOException $e) {
            error_log("Error assigning audio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove audio assignment from a page
     */
    public function removeAudioFromPage($pageId) {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM page_audio_assignments
                WHERE page_id = ?
            ");
            return $stmt->execute([$pageId]);
        } catch (PDOException $e) {
            error_log("Error removing audio assignment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get audio assignments for a flipbook
     */
    public function getAudioAssignments($flipbookId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT p.id as page_id, p.page_number, a.id as audio_id, a.name as audio_name
                FROM pages p
                LEFT JOIN page_audio_assignments paa ON p.id = paa.page_id
                LEFT JOIN audio_files a ON paa.audio_id = a.id
                WHERE p.flipbook_id = ?
                ORDER BY p.page_number ASC
            ");
            $stmt->execute([$flipbookId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting audio assignments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete a flipbook (soft delete)
     */
    public function deleteFlipbook($id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE flipbooks
                SET is_active = 0
                WHERE id = ?
            ");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting flipbook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get complete flipbook data for public viewer
     */
    public function getCompleteFlipbook($id) {
        try {
            // Get flipbook info
            $flipbook = $this->getFlipbook($id);
            if (!$flipbook) {
                return null;
            }

            // Get pages
            $pages = $this->getPages($id);

            // Get audio files
            $audioFiles = $this->getAudioFiles($id);

            // Get audio assignments
            $assignments = $this->getAudioAssignments($id);

            // Build assignment map (page_number => audio_id)
            $assignmentMap = [];
            foreach ($assignments as $assignment) {
                if ($assignment['audio_id']) {
                    $assignmentMap[$assignment['page_number']] = $assignment['audio_id'];
                }
            }

            return [
                'flipbook' => $flipbook,
                'pages' => $pages,
                'audioFiles' => $audioFiles,
                'assignments' => $assignmentMap
            ];
        } catch (PDOException $e) {
            error_log("Error getting complete flipbook: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get last error
     */
    public function getError() {
        return $this->error;
    }
}
?>

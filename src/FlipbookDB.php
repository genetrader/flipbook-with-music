<?php
/**
 * Flipbook Plugin - Database Class
 * Handles all database operations for the flipbook system
 *
 * @version 1.0.0
 * @author Mike Waxman / Larger Than Life Comics
 */

class FlipbookDB {
    private $conn;
    private $error;
    private $tablePrefix;

    public function __construct() {
        $this->tablePrefix = FLIPBOOK_TABLE_PREFIX;

        try {
            $dsn = "mysql:host=" . FLIPBOOK_DB_HOST . ";dbname=" . FLIPBOOK_DB_NAME . ";charset=" . FLIPBOOK_DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, FLIPBOOK_DB_USER, FLIPBOOK_DB_PASS, $options);
        } catch (PDOException $e) {
            $this->error = "Database connection failed: " . $e->getMessage();
            error_log($this->error);
            throw new Exception("Database connection failed");
        }
    }

    /**
     * Get table name with prefix
     */
    private function table($name) {
        return $this->tablePrefix . $name;
    }

    /**
     * Get all flipbooks
     */
    public function getAllFlipbooks() {
        try {
            $sql = "SELECT id, title, description, page_count, orientation, created_at, updated_at, is_active
                    FROM " . $this->table('flipbooks') . "
                    WHERE is_active = 1
                    ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
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
            $sql = "SELECT id, title, description, page_count, orientation, created_at, updated_at
                    FROM " . $this->table('flipbooks') . "
                    WHERE id = ? AND is_active = 1";
            $stmt = $this->conn->prepare($sql);
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
            $sql = "INSERT INTO " . $this->table('flipbooks') . " (title, description, orientation, page_count)
                    VALUES (?, ?, ?, 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$title, $description, $orientation]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating flipbook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing flipbook
     */
    public function updateFlipbook($id, $title, $description) {
        try {
            $sql = "UPDATE " . $this->table('flipbooks') . "
                    SET title = ?, description = ?
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$title, $description, $id]);
        } catch (PDOException $e) {
            error_log("Error updating flipbook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update flipbook page count
     */
    public function updatePageCount($flipbookId, $pageCount) {
        try {
            $sql = "UPDATE " . $this->table('flipbooks') . "
                    SET page_count = ?
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$pageCount, $flipbookId]);
        } catch (PDOException $e) {
            error_log("Error updating page count: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all audio assignments for a flipbook
     */
    public function clearAudioAssignments($flipbookId) {
        try {
            $sql = "DELETE paa FROM " . $this->table('page_audio_assignments') . " paa
                    INNER JOIN " . $this->table('pages') . " p ON paa.page_id = p.id
                    WHERE p.flipbook_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$flipbookId]);
        } catch (PDOException $e) {
            error_log("Error clearing audio assignments: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a page to a flipbook (supports both base64 and file path)
     */
    public function addPage($flipbookId, $pageNumber, $imageData, $imagePath = null) {
        try {
            if ($imagePath) {
                // New method: store file path and clear base64
                $sql = "INSERT INTO " . $this->table('pages') . " (flipbook_id, page_number, image_path, image_data)
                        VALUES (?, ?, ?, '')
                        ON DUPLICATE KEY UPDATE image_path = VALUES(image_path), image_data = ''";
                return $stmt = $this->conn->prepare($sql) && $stmt->execute([$flipbookId, $pageNumber, $imagePath]);
            } else {
                // Old method: store base64 (backwards compatibility)
                $sql = "INSERT INTO " . $this->table('pages') . " (flipbook_id, page_number, image_data)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE image_data = VALUES(image_data)";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$flipbookId, $pageNumber, $imageData]);
            }
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
            $sql = "SELECT id, page_number, image_data, image_path
                    FROM " . $this->table('pages') . "
                    WHERE flipbook_id = ?
                    ORDER BY page_number ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$flipbookId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting pages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add an audio file (supports both base64 and file path)
     */
    public function addAudioFile($flipbookId, $name, $audioData, $audioPath = null) {
        try {
            if ($audioPath) {
                // New method: store file path
                $sql = "INSERT INTO " . $this->table('audio_files') . " (flipbook_id, name, audio_path, audio_data)
                        VALUES (?, ?, ?, '')
                        ON DUPLICATE KEY UPDATE audio_path = VALUES(audio_path), audio_data = ''";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$flipbookId, $name, $audioPath]) ? $this->conn->lastInsertId() : false;
            } else {
                // Old method: store base64 (backwards compatibility)
                $sql = "INSERT INTO " . $this->table('audio_files') . " (flipbook_id, name, audio_data)
                        VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$flipbookId, $name, $audioData]);
                return $this->conn->lastInsertId();
            }
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
            $sql = "SELECT id, name, audio_data, audio_path
                    FROM " . $this->table('audio_files') . "
                    WHERE flipbook_id = ?
                    ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
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
            $sql = "INSERT INTO " . $this->table('page_audio_assignments') . " (page_id, audio_id)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE audio_id = VALUES(audio_id)";
            $stmt = $this->conn->prepare($sql);
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
            $sql = "DELETE FROM " . $this->table('page_audio_assignments') . "
                    WHERE page_id = ?";
            $stmt = $this->conn->prepare($sql);
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
            $sql = "SELECT p.id as page_id, p.page_number, a.id as audio_id, a.name as audio_name
                    FROM " . $this->table('pages') . " p
                    LEFT JOIN " . $this->table('page_audio_assignments') . " paa ON p.id = paa.page_id
                    LEFT JOIN " . $this->table('audio_files') . " a ON paa.audio_id = a.id
                    WHERE p.flipbook_id = ?
                    ORDER BY p.page_number ASC";
            $stmt = $this->conn->prepare($sql);
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
            $sql = "UPDATE " . $this->table('flipbooks') . "
                    SET is_active = 0
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
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

    /**
     * Check if tables exist (for installation verification)
     */
    public function tablesExist() {
        try {
            $tables = ['flipbooks', 'pages', 'audio_files', 'page_audio_assignments'];
            foreach ($tables as $table) {
                $sql = "SHOW TABLES LIKE '" . $this->table($table) . "'";
                $stmt = $this->conn->query($sql);
                if ($stmt->rowCount() === 0) {
                    return false;
                }
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>

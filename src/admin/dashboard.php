<?php
/**
 * Flipbook Plugin - Admin Dashboard
 *
 * @version 1.0.0
 */

// Load configuration
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../FlipbookDB.php';

// Start session with unique name
session_name(FLIPBOOK_SESSION_NAME);

session_start();

// Check if logged in
if (!isset($_SESSION['flipbook_admin_logged_in']) || !$_SESSION['flipbook_admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Database connection

$db = new FlipbookDB();
$flipbooks = $db->getAllFlipbooks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flipbook Admin Dashboard</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="container">
        <header class="admin-header">
            <h1>📚 Flipbook Admin Dashboard</h1>
            <div class="header-actions">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['flipbook_admin_username']); ?></span>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="action-buttons">
                <button onclick="showCreateNew()" class="btn btn-primary">+ Create New Flipbook</button>
            </div>

            <!-- Create New Flipbook Section -->
            <div id="createSection" class="section" style="display: none;">
                <h2>Create New Flipbook</h2>

                <div class="step-indicator">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Upload PDF</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Convert Pages</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Upload Audio</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Assign Audio</div>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-number">5</div>
                        <div class="step-label">Save</div>
                    </div>
                </div>

                <!-- Step 1: Basic Info & PDF Upload -->
                <div id="step1" class="step-content active">
                    <h3>Step 1: Basic Information & PDF Upload</h3>

                    <div class="form-group">
                        <label for="flipbookTitle">Flipbook Title *</label>
                        <input type="text" id="flipbookTitle" placeholder="e.g., Cork Episode 5" required>
                    </div>

                    <div class="form-group">
                        <label for="flipbookDescription">Description</label>
                        <textarea id="flipbookDescription" placeholder="Brief description of this comic"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="pdfUpload">Upload PDF *</label>
                        <div class="file-upload-area" id="pdfUploadArea">
                            <p style="font-size: 48px; margin-bottom: 10px;">📄</p>
                            <p style="font-size: 18px; font-weight: 600;">Drop PDF here or click to browse</p>
                            <input type="file" id="pdfUpload" accept="application/pdf" style="display: none;">
                        </div>
                        <div id="pdfInfo" style="display: none; margin-top: 10px;"></div>
                    </div>

                    <button onclick="convertPDF()" id="convertBtn" class="btn btn-primary" disabled>Next: Convert to Pages</button>
                </div>

                <!-- Step 2: PDF Conversion -->
                <div id="step2" class="step-content">
                    <h3>Step 2: Converting PDF to Images</h3>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" id="conversionProgress">0%</div>
                        </div>
                    </div>
                    <div id="pagePreview" class="page-preview-grid"></div>
                    <button onclick="goToStep(3)" id="audioUploadBtn" class="btn btn-primary" style="display: none;">Next: Upload Audio</button>
                </div>

                <!-- Step 3: Audio Upload -->
                <div id="step3" class="step-content">
                    <h3>Step 3: Upload MP3 Files</h3>

                    <div class="form-group">
                        <label for="audioUpload">Upload MP3 Files</label>
                        <input type="file" id="audioUpload" accept="audio/mp3,audio/mpeg" multiple>
                        <p style="margin-top: 10px; color: #666;">You can select multiple MP3 files at once</p>
                    </div>

                    <div id="audioLibraryList" class="audio-library"></div>

                    <button onclick="goToStep(4)" class="btn btn-primary">Next: Assign Audio to Pages</button>
                </div>

                <!-- Step 4: Assign Audio to Pages -->
                <div id="step4" class="step-content">
                    <h3>Step 4: Assign Audio to Pages</h3>
                    <div id="audioAssignmentList" class="audio-assignment-list"></div>
                    <button onclick="saveFlipbook()" class="btn btn-success" style="margin-top: 20px;">Save Flipbook</button>
                </div>

                <!-- Step 5: Success -->
                <div id="step5" class="step-content">
                    <div class="success-message-large">
                        <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
                        <h2>Flipbook Created Successfully!</h2>
                        <p id="successMessage"></p>
                        <div style="margin-top: 30px;">
                            <button onclick="viewFlipbook(currentFlipbookId)" class="btn btn-primary">View Flipbook</button>
                            <button onclick="location.reload()" class="btn btn-secondary">Create Another</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Flipbooks List -->
            <div class="section">
                <h2>Your Flipbooks</h2>
                <div class="flipbooks-grid" id="flipbooksList">
                    <?php if (empty($flipbooks)): ?>
                        <p style="color: #666;">No flipbooks created yet. Click "Create New Flipbook" to get started!</p>
                    <?php else: ?>
                        <?php foreach ($flipbooks as $flipbook): ?>
                            <div class="flipbook-card">
                                <h3><?php echo htmlspecialchars($flipbook['title']); ?></h3>
                                <p><?php echo htmlspecialchars($flipbook['description']); ?></p>
                                <p class="meta">Pages: <?php echo $flipbook['page_count']; ?> | Created: <?php echo date('M d, Y', strtotime($flipbook['created_at'])); ?></p>
                                <div class="card-actions">
                                    <a href="flipbook-public-viewer.php?id=<?php echo $flipbook['id']; ?>" target="_blank" class="btn btn-sm">View</a>
                                    <button onclick="editFlipbook(<?php echo $flipbook['id']; ?>)" class="btn btn-sm btn-secondary">Edit</button>
                                    <button onclick="deleteFlipbook(<?php echo $flipbook['id']; ?>)" class="btn btn-sm btn-danger">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <script src="assets/admin.js"></script>
</body>
</html>

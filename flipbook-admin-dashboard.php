<?php
session_start();

// Check if logged in
if (!isset($_SESSION['flipbook_admin_logged_in']) || !$_SESSION['flipbook_admin_logged_in']) {
    header('Location: flipbook-admin-login.php');
    exit;
}

// Database connection
require_once 'flipbook-config.php';
require_once 'flipbook-db.php';

$db = new FlipbookDB();
$flipbooks = $db->getAllFlipbooks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flipbook Admin Dashboard</title>
    <link rel="stylesheet" href="flipbook-admin-styles.css">
</head>
<body>
    <div class="container">
        <header class="admin-header">
            <h1>📚 Flipbook Admin Dashboard</h1>
            <div class="header-actions">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['flipbook_admin_username']); ?></span>
                <a href="flipbook-admin-logout.php" class="btn btn-secondary">Logout</a>
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
                        <div class="step-label">Reorder Pages</div>
                    </div>
                    <div class="step" data-step="6">
                        <div class="step-number">6</div>
                        <div class="step-label">Save</div>
                    </div>
                </div>

                <!-- Step 1: Basic Info & Upload Method -->
                <div id="step1" class="step-content active">
                    <h3>Step 1: Basic Information & Upload Content</h3>

                    <div class="form-group">
                        <label for="flipbookTitle">Flipbook Title *</label>
                        <input type="text" id="flipbookTitle" placeholder="e.g., Cork Episode 5" required>
                    </div>

                    <div class="form-group">
                        <label for="flipbookDescription">Description</label>
                        <textarea id="flipbookDescription" placeholder="Brief description of this comic"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload Method *</label>
                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <label style="flex: 1; padding: 20px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; text-align: center;" id="pdfMethodLabel">
                                <input type="radio" name="uploadMethod" value="pdf" id="uploadMethodPDF" checked onchange="switchUploadMethod('pdf')">
                                <div style="font-size: 48px; margin: 10px 0;">📄</div>
                                <strong>Upload PDF</strong>
                                <p style="color: #666; margin-top: 5px; font-size: 14px;">Upload a PDF file to convert</p>
                            </label>
                            <label style="flex: 1; padding: 20px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; text-align: center;" id="imagesMethodLabel">
                                <input type="radio" name="uploadMethod" value="images" id="uploadMethodImages" onchange="switchUploadMethod('images')">
                                <div style="font-size: 48px; margin: 10px 0;">🖼️</div>
                                <strong>Upload Images</strong>
                                <p style="color: #666; margin-top: 5px; font-size: 14px;">Upload multiple image files</p>
                            </label>
                        </div>
                    </div>

                    <!-- PDF Upload Section -->
                    <div id="pdfUploadSection" class="form-group">
                        <label for="pdfUpload">Upload PDF *</label>
                        <div class="file-upload-area" id="pdfUploadArea">
                            <p style="font-size: 48px; margin-bottom: 10px;">📄</p>
                            <p style="font-size: 18px; font-weight: 600;">Drop PDF here or click to browse</p>
                            <input type="file" id="pdfUpload" accept="application/pdf" style="display: none;">
                        </div>
                        <div id="pdfInfo" style="display: none; margin-top: 10px;"></div>
                    </div>

                    <!-- Images Upload Section -->
                    <div id="imagesUploadSection" class="form-group" style="display: none;">
                        <label for="imagesUpload">Upload Images * (JPG, PNG, GIF)</label>

                        <div style="margin-bottom: 15px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" id="useFolderUpload" onchange="toggleFolderUpload()" style="width: 18px; height: 18px;">
                                <span><strong>Upload by Folders</strong> (Auto-create chapter title slides from folder names)</span>
                            </label>
                            <div id="folderUploadInstructions" style="display: none; margin: 20px 0; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                <strong style="font-size: 16px;">📁 How to Upload Folders with Chapters:</strong>
                                <ol style="margin: 15px 0 0 25px; padding: 0; line-height: 1.8;">
                                    <li>Click the upload area below</li>
                                    <li>Navigate to the <strong>PARENT folder</strong> containing your chapter folders</li>
                                    <li><strong>Click ONCE</strong> on the parent folder to select it (don't double-click to open it)</li>
                                    <li>Click the "Select Folder" or "Upload" button</li>
                                    <li>All subfolders will be detected as chapters automatically</li>
                                </ol>
                                <p style="margin: 15px 0 0 0; padding: 10px; background: rgba(255,255,255,0.5); border-radius: 5px; font-size: 14px; color: #856404;">
                                    <strong>💡 Tip:</strong> Each subfolder will become a chapter with an auto-generated title slide. You can customize the titles before processing.
                                </p>
                            </div>
                        </div>

                        <div class="file-upload-area" id="imagesUploadArea">
                            <p style="font-size: 48px; margin-bottom: 10px;">🖼️</p>
                            <p style="font-size: 18px; font-weight: 600;" id="uploadAreaText">Drop images here or click to browse</p>
                            <p style="font-size: 14px; color: #666; margin-top: 10px;" id="uploadAreaSubtext">Select multiple image files at once</p>
                            <input type="file" id="imagesUpload" accept="image/jpeg,image/jpg,image/png,image/gif" multiple style="display: none;">
                            <input type="file" id="folderUpload" webkitdirectory directory style="display: none;">
                        </div>

                        <!-- Chapter Titles Editor (shown when folders detected) -->
                        <div id="chapterTitlesEditor" style="display: none; margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px;">
                            <h4 style="margin-top: 0; margin-bottom: 15px;">📖 Edit Chapter Titles</h4>
                            <p style="color: #666; margin-bottom: 15px;">Click on any chapter title to edit it before processing</p>
                            <div id="chapterTitlesList"></div>
                        </div>

                        <div id="imagesPreview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-top: 15px;"></div>
                    </div>

                    <button onclick="processUpload()" id="processBtn" class="btn btn-primary" disabled>Next: Process Pages</button>
                </div>

                <!-- Step 2: Processing Pages -->
                <div id="step2" class="step-content">
                    <h3>Step 2: Processing Pages</h3>
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
                    <button onclick="goToPageReorder()" class="btn btn-primary" style="margin-top: 20px;">Continue to Reorder Pages</button>
                </div>

                <!-- Step 5: Reorder Pages -->
                <div id="step5" class="step-content">
                    <h3>Step 5: Reorder Pages (Optional)</h3>
                    <p style="color: #666; margin-bottom: 20px;">Drag and drop pages to reorder them. The original filename is shown below each thumbnail.</p>
                    <div id="pageReorderGrid" class="page-reorder-grid"></div>
                    <button onclick="saveFlipbook()" class="btn btn-success" style="margin-top: 20px;">Save Flipbook</button>
                </div>

                <!-- Step 6: Success -->
                <div id="step6" class="step-content">
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
                                    <button onclick="showEmbedCode(<?php echo $flipbook['id']; ?>, '<?php echo htmlspecialchars($flipbook['title'], ENT_QUOTES); ?>')" class="btn btn-sm btn-primary">Embed Code</button>
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

    <!-- Embed Code Modal -->
    <div id="embedModal" class="embed-modal">
        <div class="embed-modal-content">
            <div class="embed-modal-header">
                <h2>Embed Code: <span id="embedTitle"></span></h2>
                <button class="embed-close-btn" onclick="closeEmbedModal()">&times;</button>
            </div>

            <!-- Fixed Height iFrame Code -->
            <div class="embed-code-section">
                <h3>📦 Fixed Height Embed (Recommended)</h3>
                <p>Simple iframe with fixed 600px height - works like Heyzine embeds. Best for most websites.</p>
                <div class="embed-code-box" id="embedIframeCode"></div>
                <button class="embed-copy-btn" onclick="copyEmbedCode('iframe')">Copy Fixed Height Code</button>
            </div>

            <!-- Responsive Container Code -->
            <div class="embed-code-section">
                <h3>📱 Responsive Container Embed</h3>
                <p>Maintains 3:4 aspect ratio and scales with page width. Good for responsive designs.</p>
                <div class="embed-code-box" id="embedResponsiveCode"></div>
                <button class="embed-copy-btn" onclick="copyEmbedCode('responsive')">Copy Responsive Code</button>
            </div>

            <!-- Direct Link -->
            <div class="embed-code-section">
                <h3>🔗 Direct Link</h3>
                <p>Use this link to share your flipbook directly or open in a new window.</p>
                <div class="embed-code-box" id="embedDirectLink"></div>
                <button class="embed-copy-btn" onclick="copyEmbedCode('link')">Copy Link</button>
            </div>

            <!-- Preview -->
            <div class="embed-preview">
                <h3>Live Preview</h3>
                <p style="color: #666; font-size: 14px; margin-bottom: 10px;">This is how your flipbook will appear when embedded:</p>
                <div style="background: white; padding: 20px; border-radius: 5px;">
                    <iframe id="embedPreviewFrame"
                            allowfullscreen="allowfullscreen"
                            scrolling="no"
                            style="border: 1px solid lightgray; width: 100%; height: 500px; display: block;"
                            allow="clipboard-write">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <script src="flipbook-admin.js?v=2"></script>
</body>
</html>

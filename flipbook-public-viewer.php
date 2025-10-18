<?php
/**
 * Flipbook Public Viewer
 * Displays a flipbook from the database
 */

require_once 'flipbook-config.php';
require_once 'flipbook-db.php';

// Get flipbook ID from URL
$flipbookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($flipbookId <= 0) {
    die('Invalid flipbook ID');
}

// Load flipbook data
$db = new FlipbookDB();
$data = $db->getCompleteFlipbook($flipbookId);

if (!$data) {
    die('Flipbook not found');
}

$flipbook = $data['flipbook'];
$pages = $data['pages'];
$audioFiles = $data['audioFiles'];
$assignments = $data['assignments'];

// Build audio library array indexed by database ID
$audioLibraryById = [];
foreach ($audioFiles as $audio) {
    $audioLibraryById[$audio['id']] = $audio;
}

// Build page audio assignments (page_index => audio data)
$pageAudioData = [];
foreach ($pages as $index => $page) {
    if (isset($assignments[$page['page_number']])) {
        $audioId = $assignments[$page['page_number']];
        if (isset($audioLibraryById[$audioId])) {
            $pageAudioData[$index] = $audioLibraryById[$audioId];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($flipbook['title']); ?> - Flipbook</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: white;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .description {
            text-align: center;
            color: rgba(255,255,255,0.9);
            margin-bottom: 20px;
            font-size: 16px;
        }

        .flipbook-wrapper {
            perspective: 1200px;
            perspective-origin: 50% 50%;
        }

        .viewer-controls {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .viewer-controls-left,
        .viewer-controls-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .control-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .control-btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .control-btn:active {
            transform: translateY(0);
        }

        .control-btn.active {
            background: #28a745;
        }

        .audio-indicator {
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
        }

        .flipbook-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 1000px;
            position: relative;
        }

        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 1000;
            background: white;
            padding: 40px 60px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            min-width: 300px;
        }

        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-progress {
            width: 100%;
            height: 8px;
            background: #f3f3f3;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 15px;
        }

        .loading-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 4px;
        }

        .loading-spinner.hidden {
            display: none;
        }

        .page-nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(102, 126, 234, 0.9);
            color: white;
            border: none;
            width: 80px;
            height: 400px;
            border-radius: 10px;
            font-size: 48px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 100;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .page-nav-arrow:hover {
            background: rgba(102, 126, 234, 1);
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .page-nav-arrow:active {
            transform: translateY(-50%) scale(0.98);
        }

        .page-nav-arrow:disabled {
            background: rgba(200, 200, 200, 0.3);
            cursor: not-allowed;
            transform: translateY(-50%);
            box-shadow: none;
        }

        .page-nav-arrow.left {
            left: 20px;
        }

        .page-nav-arrow.right {
            right: 20px;
        }

        .page-click-area {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 25%;
            cursor: pointer;
            z-index: 50;
            transition: background 0.3s;
        }

        .page-click-area:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .page-click-area.left {
            left: 0;
        }

        .page-click-area.right {
            right: 0;
        }

        .page-flip-container {
            position: relative;
            width: 700px;
            height: 900px;
            transform-style: preserve-3d;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .page-flip-container.loaded {
            opacity: 1;
        }

        .page-flip-container.landscape {
            width: 1000px;
            height: 700px;
        }

        .page {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            transform-style: preserve-3d;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            border-radius: 5px;
            overflow: hidden;
            transform-origin: center center;
        }

        .page-content {
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .page-content img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease, filter 0.3s ease;
        }

        .page-content img.loading {
            filter: blur(10px);
            opacity: 0.5;
        }

        .page-flip-container.zoom-mode {
            cursor: zoom-in;
        }

        .page-flip-container.zoom-mode.zoomed {
            cursor: move;
            overflow: hidden;
        }

        .page-flip-container.zoom-mode.zoomed .page-content img {
            transform: scale(2);
            transform-origin: center;
        }

        .page-flip-container.zoom-mode .page-click-area {
            display: none !important;
        }

        .page.current {
            z-index: 10;
            transform: rotateY(0deg);
        }

        .page.next-page {
            z-index: 9;
            transform: rotateY(-180deg);
        }

        .page.flipping {
            z-index: 20;
            animation: cardFlip 1s ease-in-out forwards;
        }

        .page.flipping-back {
            z-index: 20;
            animation: cardFlipBack 1s ease-in-out forwards;
        }

        .page.hidden {
            display: none;
        }

        @keyframes cardFlip {
            0% {
                transform: rotateY(0deg) scale(1);
            }
            50% {
                transform: rotateY(90deg) scale(0.95);
            }
            100% {
                transform: rotateY(180deg) scale(1);
            }
        }

        @keyframes cardFlipBack {
            0% {
                transform: rotateY(-180deg) scale(1);
            }
            50% {
                transform: rotateY(-90deg) scale(0.95);
            }
            100% {
                transform: rotateY(0deg) scale(1);
            }
        }

        @keyframes cardFlipReverse {
            0% {
                transform: rotateY(0deg) scale(1);
            }
            50% {
                transform: rotateY(-90deg) scale(0.95);
            }
            100% {
                transform: rotateY(-180deg) scale(1);
            }
        }

        @keyframes cardFlipBackReverse {
            0% {
                transform: rotateY(180deg) scale(1);
            }
            50% {
                transform: rotateY(90deg) scale(0.95);
            }
            100% {
                transform: rotateY(0deg) scale(1);
            }
        }

        @media (max-width: 768px) {
            .page-flip-container {
                width: 350px;
                height: 500px;
            }

            .page-flip-container.landscape {
                width: 500px;
                height: 350px;
            }

            .flipbook-container {
                padding: 20px;
                min-height: 550px;
            }

            .page-nav-arrow {
                width: 12px;
                height: 250px;
                font-size: 18px;
            }

            .page-nav-arrow.left {
                left: 5px;
            }

            .page-nav-arrow.right {
                right: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($flipbook['title']); ?></h1>
        <?php if ($flipbook['description']): ?>
            <p class="description"><?php echo htmlspecialchars($flipbook['description']); ?></p>
        <?php endif; ?>

        <div class="flipbook-wrapper">
            <div class="viewer-controls">
                <div class="viewer-controls-left">
                    <button class="control-btn" id="zoomBtn" title="Click to toggle zoom mode">
                        🔍 Zoom
                    </button>
                    <div class="audio-indicator">
                        <span id="viewerAudioTrack">No music</span>
                    </div>
                </div>
                <div class="viewer-controls-right">
                    <button class="control-btn" id="viewerMuteBtn" title="Mute/Unmute audio">
                        🔊 Unmuted
                    </button>
                </div>
            </div>

            <div class="flipbook-container">
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner"></div>
                    <p style="color: #667eea; font-weight: 600; margin-bottom: 5px;">Loading flipbook...</p>
                    <p style="color: #999; font-size: 14px; margin-bottom: 0;" id="loadingText">Preparing images...</p>
                    <div class="loading-progress">
                        <div class="loading-progress-bar" id="loadingProgressBar"></div>
                    </div>
                </div>

                <button class="page-nav-arrow left" id="leftArrow" title="Previous Page">◀</button>
                <button class="page-nav-arrow right" id="rightArrow" title="Next Page">▶</button>

                <div class="page-flip-container <?php echo $flipbook['orientation'] === 'landscape' ? 'landscape' : ''; ?>" id="pageFlipContainer">
                    <div class="page-click-area left" id="clickAreaLeft" title="Click to go to previous page"></div>
                    <div class="page-click-area right" id="clickAreaRight" title="Click to go to next page"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Embed data from PHP
        const pages = <?php echo json_encode($pages); ?>;
        const pageAudioAssignments = <?php echo json_encode($pageAudioData); ?>;

        let currentPageIndex = 0;
        let isFlipping = false;
        let currentAudio = null;
        let isMuted = false;
        let isZoomMode = false;
        let isZoomed = false;

        const container = document.getElementById('pageFlipContainer');
        const leftArrow = document.getElementById('leftArrow');
        const rightArrow = document.getElementById('rightArrow');
        const clickAreaLeft = document.getElementById('clickAreaLeft');
        const clickAreaRight = document.getElementById('clickAreaRight');
        const zoomBtn = document.getElementById('zoomBtn');
        const viewerMuteBtn = document.getElementById('viewerMuteBtn');
        const viewerAudioTrack = document.getElementById('viewerAudioTrack');

        // Create pages with progress tracking
        function createPages() {
            let loadedCount = 0;
            const totalPages = pages.length;
            const loadingText = document.getElementById('loadingText');
            const loadingProgressBar = document.getElementById('loadingProgressBar');
            const loadingSpinner = document.getElementById('loadingSpinner');

            pages.forEach((page, index) => {
                const pageDiv = document.createElement('div');
                pageDiv.className = 'page';
                if (index === 0) {
                    pageDiv.classList.add('current');
                } else {
                    pageDiv.classList.add('hidden');
                    pageDiv.style.display = 'none';
                }

                const img = new Image();
                img.className = 'loading';

                // Update progress when each image loads
                img.onload = function() {
                    img.classList.remove('loading');
                    loadedCount++;

                    const progress = Math.round((loadedCount / totalPages) * 100);
                    if (loadingProgressBar) {
                        loadingProgressBar.style.width = progress + '%';
                    }
                    if (loadingText) {
                        loadingText.textContent = `Loading ${loadedCount} of ${totalPages} pages...`;
                    }

                    // Hide spinner when first image loads
                    if (index === 0) {
                        if (loadingSpinner) {
                            loadingSpinner.classList.add('hidden');
                        }
                        container.classList.add('loaded');
                    }
                };

                // Start loading
                img.src = page.image_data;

                pageDiv.innerHTML = `
                    <div class="page-content">
                    </div>
                `;
                pageDiv.querySelector('.page-content').appendChild(img);
                container.appendChild(pageDiv);
            });
        }

        createPages();

        // Navigation functions
        function goToPage(newIndex, direction = 'forward') {
            if (isFlipping || newIndex === currentPageIndex || newIndex < 0 || newIndex >= pages.length) {
                return;
            }

            isFlipping = true;
            const allPages = container.querySelectorAll('.page');
            const currentPage = allPages[currentPageIndex];
            const nextPage = allPages[newIndex];

            if (direction === 'forward') {
                nextPage.classList.remove('hidden');
                nextPage.style.display = 'block';
                nextPage.classList.add('next-page');

                setTimeout(() => {
                    currentPage.classList.remove('current');
                    currentPage.classList.add('flipping');
                    nextPage.classList.add('flipping-back');

                    setTimeout(() => {
                        currentPage.classList.remove('flipping');
                        currentPage.classList.add('hidden');
                        currentPage.style.display = 'none';

                        nextPage.classList.remove('next-page', 'flipping-back');
                        nextPage.classList.add('current');

                        isFlipping = false;
                        updateDisplay();
                    }, 1000);
                }, 50);
            } else {
                // Flip backwards - reverse of forward animation
                // Position next page on the back (rotated 180deg)
                nextPage.classList.remove('hidden');
                nextPage.style.display = 'block';
                nextPage.classList.add('next-page');
                nextPage.style.transform = 'rotateY(180deg)';

                // Trigger both animations simultaneously
                setTimeout(() => {
                    // Current page flips away backwards (0 to -180deg)
                    currentPage.classList.remove('current');
                    currentPage.classList.add('flipping');
                    currentPage.style.animation = 'cardFlipReverse 1s ease-in-out forwards';

                    // Back page unfolds into view (180 to 0deg)
                    nextPage.classList.add('flipping-back');
                    nextPage.style.animation = 'cardFlipBackReverse 1s ease-in-out forwards';

                    // After flip completes
                    setTimeout(() => {
                        currentPage.classList.remove('flipping');
                        currentPage.classList.add('hidden');
                        currentPage.style.display = 'none';
                        currentPage.style.animation = '';
                        currentPage.style.transform = '';

                        nextPage.classList.remove('next-page', 'flipping-back');
                        nextPage.classList.add('current');
                        nextPage.style.animation = '';
                        nextPage.style.transform = 'rotateY(0deg)';

                        isFlipping = false;
                        updateDisplay();
                    }, 1000);
                }, 50);
            }

            currentPageIndex = newIndex;
            handlePageAudio(currentPageIndex);
        }

        function updateDisplay() {
            leftArrow.disabled = currentPageIndex === 0;
            rightArrow.disabled = currentPageIndex === pages.length - 1;

            clickAreaLeft.style.display = currentPageIndex === 0 ? 'none' : 'block';
            clickAreaRight.style.display = currentPageIndex === pages.length - 1 ? 'none' : 'block';
        }

        // Audio handling with fade transitions
        function handlePageAudio(pageIndex) {
            if (pageAudioAssignments[pageIndex]) {
                const audio = pageAudioAssignments[pageIndex];

                if (!currentAudio || currentAudio.dataset.audioId != audio.id) {
                    // Fade out current audio before switching
                    if (currentAudio) {
                        const audioToFade = currentAudio;
                        currentAudio = null; // Clear reference immediately

                        fadeOutAudio(audioToFade, 1000, () => {
                            audioToFade.pause();
                        });

                        // Start new audio after fade completes
                        setTimeout(() => {
                            startNewAudio(audio);
                        }, 1000);
                    } else {
                        startNewAudio(audio);
                    }

                    viewerAudioTrack.textContent = audio.name;
                }
            } else {
                if (currentAudio) {
                    const audioToFade = currentAudio;
                    currentAudio = null;

                    fadeOutAudio(audioToFade, 1000, () => {
                        audioToFade.pause();
                    });
                }
                viewerAudioTrack.textContent = 'No music';
            }
        }

        // Fade out audio over specified duration
        function fadeOutAudio(audioElement, duration, callback) {
            if (!audioElement) return;

            const startVolume = audioElement.volume;
            const fadeInterval = 50; // Update every 50ms
            const steps = duration / fadeInterval;
            const volumeStep = startVolume / steps;
            let currentStep = 0;

            const fadeTimer = setInterval(() => {
                currentStep++;
                const newVolume = Math.max(0, startVolume - (volumeStep * currentStep));
                audioElement.volume = newVolume;

                if (currentStep >= steps || newVolume <= 0) {
                    clearInterval(fadeTimer);
                    if (callback) callback();
                }
            }, fadeInterval);
        }

        // Start new audio track
        function startNewAudio(audio) {
            currentAudio = new Audio(audio.audio_data);
            currentAudio.dataset.audioId = audio.id;
            currentAudio.loop = true;
            currentAudio.volume = 0.5;

            if (!isMuted) {
                currentAudio.play().catch(e => {
                    console.log('Audio play failed:', e);
                });
            }
        }

        // Mute button
        viewerMuteBtn.addEventListener('click', () => {
            isMuted = !isMuted;
            if (currentAudio) {
                currentAudio.muted = isMuted;
            }
            updateViewerMuteBtn();
        });

        function updateViewerMuteBtn() {
            if (isMuted) {
                viewerMuteBtn.innerHTML = '🔇 Muted';
                viewerMuteBtn.style.background = '#dc3545';
            } else {
                viewerMuteBtn.innerHTML = '🔊 Unmuted';
                viewerMuteBtn.style.background = '#667eea';
            }
        }

        // Zoom functionality
        zoomBtn.addEventListener('click', () => {
            isZoomMode = !isZoomMode;

            if (isZoomMode) {
                zoomBtn.classList.add('active');
                zoomBtn.innerHTML = '🔍 Zoom ON';
                container.classList.add('zoom-mode');
            } else {
                zoomBtn.classList.remove('active');
                zoomBtn.innerHTML = '🔍 Zoom';
                container.classList.remove('zoom-mode', 'zoomed');
                isZoomed = false;
                const allImages = container.querySelectorAll('.page-content img');
                allImages.forEach(img => {
                    img.style.transformOrigin = 'center';
                    img.style.transform = '';
                });
            }
        });

        // Toggle zoom on click/tap
        let touchMoved = false;

        function toggleZoom(e) {
            if (!isZoomMode) return;

            if (!isZoomed) {
                isZoomed = true;
                container.classList.add('zoomed');
                container.style.cursor = 'move';
            } else {
                isZoomed = false;
                container.classList.remove('zoomed');
                container.style.cursor = 'zoom-in';
                const currentPageImg = container.querySelector('.page.current img');
                if (currentPageImg) {
                    currentPageImg.style.transformOrigin = 'center';
                }
            }

            // Don't stop propagation - allow audio init to work
        }

        container.addEventListener('click', toggleZoom);

        // Track touch movement to distinguish tap from drag
        container.addEventListener('touchstart', (e) => {
            touchMoved = false;
        }, { passive: true });

        container.addEventListener('touchend', (e) => {
            // Only toggle zoom if it's a tap (not a drag/pan)
            if (isZoomMode && e.changedTouches.length === 1 && !touchMoved) {
                e.preventDefault();
                toggleZoom(e);
            }
        });

        // Mouse panning for desktop
        container.addEventListener('mousemove', (e) => {
            if (!isZoomMode || !isZoomed) return;

            const rect = container.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;

            const currentPageImg = container.querySelector('.page.current img');
            if (currentPageImg) {
                currentPageImg.style.transformOrigin = `${x}% ${y}%`;
            }
        });

        // Touch panning for mobile
        let touchStartX = 0;
        let touchStartY = 0;

        container.addEventListener('touchmove', (e) => {
            // Mark that touch moved (for distinguishing tap from drag)
            touchMoved = true;

            if (!isZoomMode || !isZoomed) return;

            e.preventDefault(); // Prevent default scrolling

            const touch = e.touches[0];
            const rect = container.getBoundingClientRect();

            // Calculate position relative to container
            const x = ((touch.clientX - rect.left) / rect.width) * 100;
            const y = ((touch.clientY - rect.top) / rect.height) * 100;

            // Clamp values between 0 and 100
            const clampedX = Math.max(0, Math.min(100, x));
            const clampedY = Math.max(0, Math.min(100, y));

            const currentPageImg = container.querySelector('.page.current img');
            if (currentPageImg) {
                currentPageImg.style.transformOrigin = `${clampedX}% ${clampedY}%`;
            }
        }, { passive: false });

        // Navigation
        leftArrow.addEventListener('click', () => goToPage(currentPageIndex - 1, 'backward'));
        rightArrow.addEventListener('click', () => goToPage(currentPageIndex + 1, 'forward'));

        clickAreaLeft.addEventListener('click', (e) => {
            e.stopPropagation();
            goToPage(currentPageIndex - 1, 'backward');
        });

        clickAreaRight.addEventListener('click', (e) => {
            e.stopPropagation();
            goToPage(currentPageIndex + 1, 'forward');
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (isZoomed) {
                if (e.key === 'Escape') {
                    isZoomed = false;
                    container.classList.remove('zoomed');
                    container.style.cursor = 'zoom-in';
                }
                return;
            }

            if (e.key === 'ArrowLeft') {
                goToPage(currentPageIndex - 1, 'backward');
            } else if (e.key === 'ArrowRight') {
                goToPage(currentPageIndex + 1, 'forward');
            } else if (e.key === 'Escape' && isZoomMode) {
                zoomBtn.click();
            }
        });

        // Initialize audio - try immediate playback aggressively
        let audioInitialized = false;

        function initializeAudio() {
            if (!audioInitialized) {
                audioInitialized = true;
                handlePageAudio(currentPageIndex);
            }
        }

        // Try multiple times to start audio immediately
        setTimeout(() => initializeAudio(), 50);
        setTimeout(() => initializeAudio(), 200);
        setTimeout(() => initializeAudio(), 500);

        // Also try on any user interaction
        const startAudioOnInteraction = () => {
            initializeAudio();
        };

        document.addEventListener('click', startAudioOnInteraction, { once: true });
        document.addEventListener('keydown', startAudioOnInteraction, { once: true });
        document.addEventListener('touchstart', startAudioOnInteraction, { once: true });

        // Initialize
        updateDisplay();
    </script>
</body>
</html>

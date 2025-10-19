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
            padding: 10px;
        }

        .container {
            max-width: 100%;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1 {
            text-align: center;
            color: white;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-size: 24px;
        }

        .description {
            text-align: center;
            color: rgba(255,255,255,0.9);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .flipbook-wrapper {
            perspective: 1200px;
            perspective-origin: 50% 50%;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .viewer-controls {
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            flex-shrink: 0;
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
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            position: relative;
            overflow: hidden;
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
            max-width: 90vw;
            max-height: 85vh;
            width: auto;
            height: auto;
            transform-style: preserve-3d;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        /* Portrait: maintain aspect ratio */
        .page-flip-container:not(.landscape) {
            aspect-ratio: 7 / 9;
            width: min(90vw, calc(85vh * 7 / 9));
        }

        /* Landscape: maintain aspect ratio */
        .page-flip-container.landscape {
            aspect-ratio: 10 / 7;
            width: min(90vw, calc(85vh * 10 / 7));
        }

        .page-flip-container.loaded {
            opacity: 1;
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
            transform: scale(2.4);
            transform-origin: center;
        }

        .page-flip-container.zoom-mode .page-click-area {
            display: none !important;
        }

        .page.current {
            z-index: 10;
            opacity: 1;
            transform: translateX(0);
        }

        .page.hidden {
            display: none;
        }

        /* Slide out to left and fade */
        .page.sliding-out-left {
            z-index: 9;
            animation: slideOutLeft 0.6s ease-in-out forwards;
        }

        /* Slide out to right and fade */
        .page.sliding-out-right {
            z-index: 9;
            animation: slideOutRight 0.6s ease-in-out forwards;
        }

        /* Slide in from right and fade in */
        .page.sliding-in-right {
            z-index: 10;
            animation: slideInRight 0.6s ease-in-out forwards;
        }

        /* Slide in from left and fade in */
        .page.sliding-in-left {
            z-index: 10;
            animation: slideInLeft 0.6s ease-in-out forwards;
        }

        @keyframes slideOutLeft {
            0% {
                transform: translateX(0);
                opacity: 1;
            }
            100% {
                transform: translateX(-100%);
                opacity: 0;
            }
        }

        @keyframes slideOutRight {
            0% {
                transform: translateX(0);
                opacity: 1;
            }
            100% {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @keyframes slideInRight {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInLeft {
            0% {
                transform: translateX(-100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 5px;
            }

            h1 {
                font-size: 20px;
                margin-bottom: 3px;
            }

            .description {
                font-size: 12px;
                margin-bottom: 5px;
            }

            .viewer-controls {
                padding: 8px 12px;
                margin-bottom: 5px;
            }

            .control-btn {
                padding: 8px 12px;
                font-size: 14px;
            }

            .audio-indicator {
                padding: 6px 10px;
                font-size: 12px;
            }

            .flipbook-container {
                padding: 10px;
            }

            /* Make flipbook even larger on mobile */
            .page-flip-container:not(.landscape) {
                width: min(95vw, calc(90vh * 7 / 9));
            }

            .page-flip-container.landscape {
                width: min(95vw, calc(90vh * 10 / 7));
            }

            .page-nav-arrow {
                width: 8px;
                height: 200px;
                font-size: 16px;
            }

            .page-nav-arrow.left {
                left: 2px;
            }

            .page-nav-arrow.right {
                right: 2px;
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

        // Web Audio API context for better iOS support
        let audioContext = null;
        let currentSource = null;
        let currentGainNode = null;

        const container = document.getElementById('pageFlipContainer');
        const leftArrow = document.getElementById('leftArrow');
        const rightArrow = document.getElementById('rightArrow');
        const clickAreaLeft = document.getElementById('clickAreaLeft');
        const clickAreaRight = document.getElementById('clickAreaRight');
        const zoomBtn = document.getElementById('zoomBtn');
        const viewerMuteBtn = document.getElementById('viewerMuteBtn');
        const viewerAudioTrack = document.getElementById('viewerAudioTrack');

        // Create pages with lazy loading - load first page ASAP, others in background
        function createPages() {
            let loadedCount = 0;
            const totalPages = pages.length;
            const loadingText = document.getElementById('loadingText');
            const loadingProgressBar = document.getElementById('loadingProgressBar');
            const loadingSpinner = document.getElementById('loadingSpinner');

            // First, create all page divs quickly
            const pageElements = [];
            pages.forEach((page, index) => {
                const pageDiv = document.createElement('div');
                pageDiv.className = 'page';
                if (index === 0) {
                    pageDiv.classList.add('current');
                } else {
                    pageDiv.classList.add('hidden');
                    pageDiv.style.display = 'none';
                }

                pageDiv.innerHTML = `<div class="page-content"></div>`;
                container.appendChild(pageDiv);
                pageElements.push(pageDiv);
            });

            // Load first page immediately with high priority
            function loadPage(index) {
                const page = pages[index];
                const pageDiv = pageElements[index];
                const img = new Image();

                img.onload = function() {
                    loadedCount++;
                    const progress = Math.round((loadedCount / totalPages) * 100);

                    if (loadingProgressBar) {
                        loadingProgressBar.style.width = progress + '%';
                    }
                    if (loadingText) {
                        loadingText.textContent = `Loading ${loadedCount} of ${totalPages} pages...`;
                    }

                    // Hide spinner when first page loads
                    if (index === 0) {
                        if (loadingSpinner) {
                            loadingSpinner.classList.add('hidden');
                        }
                        container.classList.add('loaded');

                        // Log audio availability for debugging
                        console.log('First page loaded');
                        console.log('Page audio assignments:', pageAudioAssignments);
                        console.log('Current page index:', currentPageIndex);
                    }
                };

                // Use image_path if available (new system), otherwise image_data (old system)
                const imageSrc = page.image_path || page.image_data;
                console.log(`Page ${index + 1} - has image_path: ${!!page.image_path}, has image_data: ${!!page.image_data}, using: ${imageSrc ? imageSrc.substring(0, 50) + '...' : 'none'}`);
                img.src = imageSrc;
                pageDiv.querySelector('.page-content').appendChild(img);
            }

            // Load first page immediately
            loadPage(0);

            // Fallback: if first page doesn't load in 5 seconds, show it anyway
            setTimeout(() => {
                if (loadedCount === 0) {
                    console.log('First page taking too long, showing spinner...');
                    if (loadingSpinner) {
                        loadingSpinner.classList.add('hidden');
                    }
                    container.classList.add('loaded');
                }
            }, 5000);

            // Load remaining pages after a short delay
            setTimeout(() => {
                for (let i = 1; i < pages.length; i++) {
                    loadPage(i);
                }
            }, 100);
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

            // Show the new page
            nextPage.classList.remove('hidden');
            nextPage.style.display = 'block';

            if (direction === 'forward') {
                // Current page slides out to the left and fades
                currentPage.classList.remove('current');
                currentPage.classList.add('sliding-out-left');

                // Next page slides in from the right and fades in
                nextPage.classList.add('sliding-in-right');

                setTimeout(() => {
                    // Clean up after animation
                    currentPage.classList.remove('sliding-out-left');
                    currentPage.classList.add('hidden');
                    currentPage.style.display = 'none';

                    nextPage.classList.remove('sliding-in-right');
                    nextPage.classList.add('current');

                    isFlipping = false;
                    updateDisplay();
                }, 600); // Match animation duration
            } else {
                // Going backward - current page slides out to the right and fades
                currentPage.classList.remove('current');
                currentPage.classList.add('sliding-out-right');

                // Next page slides in from the left and fades in
                nextPage.classList.add('sliding-in-left');

                setTimeout(() => {
                    // Clean up after animation
                    currentPage.classList.remove('sliding-out-right');
                    currentPage.classList.add('hidden');
                    currentPage.style.display = 'none';

                    nextPage.classList.remove('sliding-in-left');
                    nextPage.classList.add('current');

                    isFlipping = false;
                    updateDisplay();
                }, 600); // Match animation duration
            }

            currentPageIndex = newIndex;

            // Handle audio for new page (if audio has been initialized)
            if (audioInitialized) {
                handlePageAudio(currentPageIndex);
            }
        }

        function updateDisplay() {
            leftArrow.disabled = currentPageIndex === 0;
            rightArrow.disabled = currentPageIndex === pages.length - 1;

            clickAreaLeft.style.display = currentPageIndex === 0 ? 'none' : 'block';
            clickAreaRight.style.display = currentPageIndex === pages.length - 1 ? 'none' : 'block';
        }

        // Initialize Web Audio API context (required for iOS)
        function initAudioContext() {
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                console.log('Web Audio API context initialized');
            }
        }

        // Audio handling with Web Audio API for iOS-compatible crossfade
        function handlePageAudio(pageIndex) {
            console.log('handlePageAudio called for page:', pageIndex);
            console.log('Audio assigned to this page:', pageAudioAssignments[pageIndex]);

            if (pageAudioAssignments[pageIndex]) {
                const audio = pageAudioAssignments[pageIndex];
                console.log('Found audio for page:', audio.name);

                if (!currentAudio || currentAudio.dataset?.audioId != audio.id) {
                    // Start new audio with crossfade
                    startNewAudioWithWebAPI(audio);

                    viewerAudioTrack.textContent = audio.name;
                } else {
                    console.log('Audio already playing, no change needed');
                }
            } else {
                console.log('No audio assigned to page', pageIndex);
                if (currentGainNode && audioContext) {
                    // Fade out current audio
                    const now = audioContext.currentTime;
                    currentGainNode.gain.setValueAtTime(currentGainNode.gain.value, now);
                    currentGainNode.gain.linearRampToValueAtTime(0, now + 2);

                    // Stop after fade
                    setTimeout(() => {
                        if (currentSource) {
                            currentSource.stop();
                            currentSource = null;
                        }
                        currentAudio = null;
                        currentGainNode = null;
                    }, 2100);
                }
                viewerAudioTrack.textContent = 'No music';
            }
        }

        // Start new audio using Web Audio API with crossfade
        async function startNewAudioWithWebAPI(audio) {
            console.log('startNewAudioWithWebAPI called for:', audio.name);
            console.log('AudioContext state:', audioContext ? audioContext.state : 'null');

            // Use audio_path if available, otherwise audio_data
            const audioSrc = audio.audio_path || audio.audio_data;
            console.log('Using audio source:', audioSrc ? audioSrc.substring(0, 50) + '...' : 'none');

            // Check if audioContext exists
            if (!audioContext) {
                console.error('AudioContext not initialized!');
                fallbackToRegularAudio(audio);
                return;
            }

            try {
                // Fetch and decode audio
                console.log('Fetching audio...');
                const response = await fetch(audioSrc);
                console.log('Fetch response status:', response.status);

                const arrayBuffer = await response.arrayBuffer();
                console.log('ArrayBuffer size:', arrayBuffer.byteLength);

                console.log('Decoding audio...');
                const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);
                console.log('Audio decoded successfully, duration:', audioBuffer.duration);

                // Store old gain node for crossfade
                const oldGainNode = currentGainNode;
                const oldSource = currentSource;

                // Create new source and gain node
                const source = audioContext.createBufferSource();
                const gainNode = audioContext.createGain();

                source.buffer = audioBuffer;
                source.loop = true;
                source.connect(gainNode);
                gainNode.connect(audioContext.destination);

                // Start new audio at volume 0
                const now = audioContext.currentTime;
                gainNode.gain.setValueAtTime(0, now);

                // Update references
                currentSource = source;
                currentGainNode = gainNode;
                currentAudio = { dataset: { audioId: audio.id } };

                if (!isMuted) {
                    // Start playing
                    source.start(0);
                    console.log('New audio started at time:', now);

                    // Crossfade: fade in new, fade out old over 2 seconds
                    gainNode.gain.linearRampToValueAtTime(0.5, now + 2);
                    console.log('Scheduled fade in from 0 to 0.5');

                    if (oldGainNode) {
                        console.log('Crossfading from old to new audio');
                        oldGainNode.gain.setValueAtTime(oldGainNode.gain.value, now);
                        oldGainNode.gain.linearRampToValueAtTime(0, now + 2);

                        // Stop old source after fade
                        setTimeout(() => {
                            if (oldSource) {
                                try {
                                    oldSource.stop();
                                    console.log('Old source stopped');
                                } catch (e) {
                                    console.log('Old source already stopped');
                                }
                            }
                        }, 2100);
                    } else {
                        // No crossfade needed, just fade in
                        console.log('No old audio, fading in directly');
                    }
                } else {
                    console.log('Audio is muted, not playing');
                }

            } catch (e) {
                console.error('Web Audio API failed:', e);
                console.error('Error details:', e.message, e.stack);
                // Fallback to regular Audio element
                fallbackToRegularAudio(audio);
            }
        }

        // Fallback to regular audio if Web Audio API fails
        function fallbackToRegularAudio(audio) {
            console.log('Falling back to regular Audio element');
            const audioSrc = audio.audio_path || audio.audio_data;
            const newAudio = new Audio(audioSrc);
            newAudio.dataset.audioId = audio.id;
            newAudio.loop = true;
            newAudio.volume = 0.5;
            currentAudio = newAudio;

            if (!isMuted) {
                newAudio.play().catch(e => console.error('Fallback audio play failed:', e));
            }
        }

        // Mute button
        viewerMuteBtn.addEventListener('click', () => {
            isMuted = !isMuted;

            // Handle Web Audio API muting
            if (currentGainNode && audioContext) {
                const now = audioContext.currentTime;
                if (isMuted) {
                    currentGainNode.gain.setValueAtTime(currentGainNode.gain.value, now);
                    currentGainNode.gain.linearRampToValueAtTime(0, now + 0.1);
                } else {
                    currentGainNode.gain.setValueAtTime(0, now);
                    currentGainNode.gain.linearRampToValueAtTime(0.5, now + 0.1);
                }
            }
            // Fallback for regular Audio element
            else if (currentAudio && currentAudio.muted !== undefined) {
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
        leftArrow.addEventListener('click', () => {
            // If audio not initialized yet, wait a moment for the universal handler to run first
            if (!audioInitialized) {
                console.log('First navigation - delaying to allow audio init');
                setTimeout(() => {
                    console.log('Delayed navigation executing, audioInitialized:', audioInitialized);
                    goToPage(currentPageIndex - 1, 'backward');
                }, 100);
            } else {
                goToPage(currentPageIndex - 1, 'backward');
            }
        });

        rightArrow.addEventListener('click', () => {
            // If audio not initialized yet, wait a moment for the universal handler to run first
            if (!audioInitialized) {
                console.log('First navigation - delaying to allow audio init');
                setTimeout(() => {
                    console.log('Delayed navigation executing, audioInitialized:', audioInitialized);
                    goToPage(currentPageIndex + 1, 'forward');
                }, 100);
            } else {
                goToPage(currentPageIndex + 1, 'forward');
            }
        });

        clickAreaLeft.addEventListener('click', (e) => {
            e.stopPropagation();
            // If audio not initialized yet, wait a moment for the universal handler to run first
            if (!audioInitialized) {
                console.log('First navigation - delaying to allow audio init');
                setTimeout(() => {
                    console.log('Delayed navigation executing, audioInitialized:', audioInitialized);
                    goToPage(currentPageIndex - 1, 'backward');
                }, 100);
            } else {
                goToPage(currentPageIndex - 1, 'backward');
            }
        });

        clickAreaRight.addEventListener('click', (e) => {
            e.stopPropagation();
            // If audio not initialized yet, wait a moment for the universal handler to run first
            if (!audioInitialized) {
                console.log('First navigation - delaying to allow audio init');
                setTimeout(() => {
                    console.log('Delayed navigation executing, audioInitialized:', audioInitialized);
                    goToPage(currentPageIndex + 1, 'forward');
                }, 100);
            } else {
                goToPage(currentPageIndex + 1, 'forward');
            }
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

        // Initialize audio - handle mobile autoplay restrictions
        let audioInitialized = false;
        let userHasInteracted = false;

        // Universal interaction handler - start audio on first active user interaction
        function startAudioOnFirstInteraction(e) {
            if (!audioInitialized) {
                userHasInteracted = true;
                audioInitialized = true;
                console.log('First user interaction detected (event:', e.type, ') - starting audio for page:', currentPageIndex);

                // Initialize Web Audio API context on first interaction (required for iOS)
                try {
                    initAudioContext();

                    // iOS Debug: Show alert with AudioContext state
                    if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                        alert('iOS detected. AudioContext state: ' + (audioContext ? audioContext.state : 'null'));
                    }

                    // Resume audio context if it's suspended (iOS requirement)
                    if (audioContext && audioContext.state === 'suspended') {
                        audioContext.resume().then(() => {
                            console.log('AudioContext resumed');
                            if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                                alert('AudioContext resumed! State: ' + audioContext.state);
                            }
                            handlePageAudio(currentPageIndex);
                        }).catch(err => {
                            if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                                alert('Resume failed: ' + err.message);
                            }
                        });
                    } else {
                        handlePageAudio(currentPageIndex);
                    }
                } catch (err) {
                    if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                        alert('Audio init error: ' + err.message);
                    }
                }

                // Remove listeners after first interaction
                document.removeEventListener('click', startAudioOnFirstInteraction);
                document.removeEventListener('keydown', startAudioOnFirstInteraction);
                document.removeEventListener('touchend', startAudioOnFirstInteraction);
            }
        }

        // Listen for active user interactions to unlock audio (click, keypress, touch)
        // Note: Use touchend instead of touchstart for better mobile compatibility
        document.addEventListener('click', startAudioOnFirstInteraction);
        document.addEventListener('keydown', startAudioOnFirstInteraction);
        document.addEventListener('touchend', startAudioOnFirstInteraction);

        // Initialize
        updateDisplay();
    </script>
</body>
</html>

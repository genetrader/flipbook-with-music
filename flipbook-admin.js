/**
 * Flipbook Admin Dashboard JavaScript
 * Handles PDF conversion, audio upload, and flipbook creation
 */

// Global variables
let currentFlipbookId = null;
let pages = [];
let audioLibrary = [];
let currentStep = 1;
let uploadMethod = 'pdf'; // 'pdf' or 'images'
let uploadedImages = [];
let useFolderUpload = false;
let chapters = []; // Array of {folderName, title, images}
let pageFilenames = []; // Array to store original filenames for each page

// PDF.js setup is done in the HTML file

// Natural sort function for filenames with numbers
function naturalSort(a, b) {
    // Extract filename without extension for comparison
    const aName = a.name || a;
    const bName = b.name || b;

    // Split into parts of text and numbers
    const aParts = aName.match(/(\d+|\D+)/g) || [];
    const bParts = bName.match(/(\d+|\D+)/g) || [];

    const len = Math.min(aParts.length, bParts.length);

    for (let i = 0; i < len; i++) {
        const aPart = aParts[i];
        const bPart = bParts[i];

        // Check if both parts are numbers
        const aNum = parseInt(aPart, 10);
        const bNum = parseInt(bPart, 10);

        if (!isNaN(aNum) && !isNaN(bNum)) {
            // Both are numbers - compare numerically
            if (aNum !== bNum) {
                return aNum - bNum;
            }
        } else {
            // At least one is text - compare alphabetically
            const compare = aPart.localeCompare(bPart);
            if (compare !== 0) {
                return compare;
            }
        }
    }

    // If all parts are equal, shorter name comes first
    return aParts.length - bParts.length;
}

// Switch upload method
function switchUploadMethod(method) {
    uploadMethod = method;
    const pdfSection = document.getElementById('pdfUploadSection');
    const imagesSection = document.getElementById('imagesUploadSection');
    const pdfLabel = document.getElementById('pdfMethodLabel');
    const imagesLabel = document.getElementById('imagesMethodLabel');
    const processBtn = document.getElementById('processBtn');

    if (method === 'pdf') {
        pdfSection.style.display = 'block';
        imagesSection.style.display = 'none';
        pdfLabel.style.borderColor = '#667eea';
        pdfLabel.style.borderWidth = '3px';
        imagesLabel.style.borderColor = '#ddd';
        imagesLabel.style.borderWidth = '2px';

        // Enable button if PDF is loaded
        const pdfFile = document.getElementById('pdfUpload').files[0];
        processBtn.disabled = !pdfFile;
    } else {
        pdfSection.style.display = 'none';
        imagesSection.style.display = 'block';
        pdfLabel.style.borderColor = '#ddd';
        pdfLabel.style.borderWidth = '2px';
        imagesLabel.style.borderColor = '#667eea';
        imagesLabel.style.borderWidth = '3px';

        // Enable button if images are loaded
        processBtn.disabled = uploadedImages.length === 0 && chapters.length === 0;
    }
}

// Toggle folder upload mode
function toggleFolderUpload() {
    useFolderUpload = document.getElementById('useFolderUpload').checked;
    const uploadAreaText = document.getElementById('uploadAreaText');
    const uploadAreaSubtext = document.getElementById('uploadAreaSubtext');
    const instructions = document.getElementById('folderUploadInstructions');

    if (useFolderUpload) {
        uploadAreaText.textContent = 'Click here to select parent folder';
        uploadAreaSubtext.innerHTML = '<strong style="color: #dc3545;">Your browser will show ALL files in the selected folder tree - this is normal!</strong>';
        instructions.style.display = 'block';
    } else {
        uploadAreaText.textContent = 'Drop images here or click to browse';
        uploadAreaSubtext.textContent = 'Select multiple image files at once';
        instructions.style.display = 'none';
    }

    // Reset uploads
    document.getElementById('imagesUpload').value = '';
    document.getElementById('folderUpload').value = '';
    uploadedImages = [];
    chapters = [];
    document.getElementById('imagesPreview').innerHTML = '';
    document.getElementById('chapterTitlesEditor').style.display = 'none';
    document.getElementById('processBtn').disabled = true;
}

// Step navigation
function goToStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(step => {
        step.classList.remove('active');
    });

    // Show target step
    document.getElementById(`step${stepNumber}`).classList.add('active');

    // Update step indicators
    document.querySelectorAll('.step').forEach((step, index) => {
        step.classList.remove('active');
        if (index + 1 < stepNumber) {
            step.classList.add('completed');
        }
    });

    document.querySelector(`.step[data-step="${stepNumber}"]`).classList.add('active');
    currentStep = stepNumber;
}

// Show create section
function showCreateNew() {
    const section = document.getElementById('createSection');
    section.style.display = 'block';
    section.scrollIntoView({ behavior: 'smooth' });
    goToStep(1);

    // Reset form
    document.getElementById('flipbookTitle').value = '';
    document.getElementById('flipbookDescription').value = '';
    document.getElementById('pdfUpload').value = '';
    document.getElementById('imagesUpload').value = '';
    document.getElementById('pdfInfo').style.display = 'none';
    document.getElementById('imagesPreview').innerHTML = '';
    document.getElementById('chapterTitlesEditor').style.display = 'none';
    document.getElementById('processBtn').disabled = true;

    // Reset folder upload checkbox
    document.getElementById('useFolderUpload').checked = false;
    useFolderUpload = false;

    // Reset to PDF method
    document.getElementById('uploadMethodPDF').checked = true;
    switchUploadMethod('pdf');

    pages = [];
    audioLibrary = [];
    uploadedImages = [];
    chapters = [];
}

// PDF Upload handling
const pdfUploadArea = document.getElementById('pdfUploadArea');
const pdfUpload = document.getElementById('pdfUpload');
const pdfInfo = document.getElementById('pdfInfo');
const convertBtn = document.getElementById('convertBtn');

pdfUploadArea.addEventListener('click', () => {
    pdfUpload.click();
});

pdfUploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    pdfUploadArea.classList.add('dragover');
});

pdfUploadArea.addEventListener('dragleave', () => {
    pdfUploadArea.classList.remove('dragover');
});

pdfUploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    pdfUploadArea.classList.remove('dragover');

    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].type === 'application/pdf') {
        pdfUpload.files = files;
        handlePDFUpload(files[0]);
    }
});

pdfUpload.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handlePDFUpload(e.target.files[0]);
    }
});

function handlePDFUpload(file) {
    pdfInfo.style.display = 'block';
    pdfInfo.innerHTML = `
        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #667eea;">
            <strong>📄 ${file.name}</strong><br>
            Size: ${(file.size / 1024 / 1024).toFixed(2)} MB
        </div>
    `;
    document.getElementById('processBtn').disabled = false;
}

// Images Upload handling
const imagesUploadArea = document.getElementById('imagesUploadArea');
const imagesUpload = document.getElementById('imagesUpload');
const folderUpload = document.getElementById('folderUpload');
const imagesPreview = document.getElementById('imagesPreview');

imagesUploadArea.addEventListener('click', () => {
    if (useFolderUpload) {
        folderUpload.click();
    } else {
        imagesUpload.click();
    }
});

imagesUploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    imagesUploadArea.classList.add('dragover');
});

imagesUploadArea.addEventListener('dragleave', () => {
    imagesUploadArea.classList.remove('dragover');
});

imagesUploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    imagesUploadArea.classList.remove('dragover');

    const files = Array.from(e.dataTransfer.files).filter(file =>
        file.type.startsWith('image/')
    );

    if (files.length > 0) {
        handleImagesUpload(files);
    }
});

imagesUpload.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleImagesUpload(Array.from(e.target.files));
    }
});

folderUpload.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFolderUpload(Array.from(e.target.files));
    }
});

function handleImagesUpload(files) {
    // Sort files using natural sort (handles numbers in filenames correctly)
    const sortedFiles = Array.from(files).sort(naturalSort);
    uploadedImages = sortedFiles;
    imagesPreview.innerHTML = '';

    sortedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewDiv = document.createElement('div');
            previewDiv.style.cssText = 'border: 2px solid #ddd; border-radius: 5px; overflow: hidden; position: relative;';
            previewDiv.innerHTML = `
                <img src="${e.target.result}" style="width: 100%; height: 120px; object-fit: cover; display: block;">
                <div style="padding: 5px; background: #f5f5f5; font-size: 11px; text-align: center;">
                    Page ${index + 1}
                </div>
                <button onclick="removeImage(${index})" style="position: absolute; top: 5px; right: 5px; background: red; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px; line-height: 1;">×</button>
            `;
            imagesPreview.appendChild(previewDiv);
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('processBtn').disabled = false;
}

function handleFolderUpload(files) {
    console.log(`handleFolderUpload called with ${files.length} files`);

    // Organize files by folder
    const folderMap = new Map();

    files.forEach(file => {
        // Get folder path from webkitRelativePath
        const pathParts = file.webkitRelativePath.split('/');
        console.log(`File: ${file.name}, Path: ${file.webkitRelativePath}, Parts:`, pathParts);

        if (pathParts.length < 2) {
            console.log(`Skipping file in root: ${file.name}`);
            return; // Skip files in root
        }

        const folderName = pathParts[pathParts.length - 2]; // Direct parent folder
        console.log(`Adding ${file.name} to folder: ${folderName}`);

        if (!folderMap.has(folderName)) {
            folderMap.set(folderName, []);
        }
        folderMap.get(folderName).push(file);
    });

    console.log(`Folder map has ${folderMap.size} folders:`, Array.from(folderMap.keys()));

    // Convert to chapters array
    chapters = [];
    let chapterNum = 1;
    folderMap.forEach((images, folderName) => {
        // Sort images using natural sort (handles numbers in filenames correctly)
        images.sort(naturalSort);

        chapters.push({
            folderName: folderName,
            headerText: `CHAPTER ${chapterNum}`, // Big text at top (editable)
            title: formatChapterTitle(folderName), // Subtitle below (editable)
            images: images
        });
        chapterNum++;
    });

    // Sort chapters using natural sort (handles chapter numbers correctly)
    chapters.sort((a, b) => naturalSort(a.folderName, b.folderName));

    console.log(`Detected ${chapters.length} chapters:`, chapters.map(c => `${c.folderName} (${c.images.length} images)`));

    if (chapters.length === 0) {
        alert('No chapters detected. Please make sure you selected a parent folder containing subfolders with images.');
        return;
    }

    // Display chapter titles for editing
    displayChapterTitles();

    // Display preview
    displayFolderPreview();

    document.getElementById('processBtn').disabled = false;
}

function formatChapterTitle(folderName) {
    // Convert folder name to title case
    // Examples: "chapter-1" -> "Chapter 1", "cork_origin" -> "Cork Origin"
    return folderName
        .replace(/[-_]/g, ' ')
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
}

function displayChapterTitles() {
    const editor = document.getElementById('chapterTitlesEditor');
    const list = document.getElementById('chapterTitlesList');

    list.innerHTML = chapters.map((chapter, index) => `
        <div style="margin-bottom: 15px; padding: 15px; background: white; border-radius: 8px; border: 2px solid #e0e0e0;">
            <div style="display: flex; align-items: flex-start; gap: 15px;">
                <div style="font-size: 24px;">📖</div>
                <div style="flex: 1;">
                    <div style="font-size: 12px; color: #999; margin-bottom: 10px;">Chapter ${index + 1} (${chapter.images.length} pages) - Folder: ${chapter.folderName}</div>

                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-size: 11px; color: #666; margin-bottom: 3px;">Big Header Text (leave blank to hide):</label>
                        <input type="text"
                               id="chapterHeader${index}"
                               value="${chapter.headerText}"
                               onchange="updateChapterHeader(${index}, this.value)"
                               style="width: 100%; padding: 8px; font-size: 14px; font-weight: 600; border: 2px solid #ddd; border-radius: 5px; background: #f8f8f8;"
                               placeholder="e.g., CHAPTER 1 or leave blank">
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; color: #666; margin-bottom: 3px;">Subtitle Text (leave blank to hide):</label>
                        <input type="text"
                               id="chapterTitle${index}"
                               value="${chapter.title}"
                               onchange="updateChapterTitle(${index}, this.value)"
                               style="width: 100%; padding: 8px; font-size: 16px; font-weight: 600; border: 2px solid #ddd; border-radius: 5px;"
                               placeholder="e.g., The Beginning or leave blank">
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    editor.style.display = 'block';
}

function updateChapterTitle(index, newTitle) {
    chapters[index].title = newTitle;
}

function updateChapterHeader(index, newHeader) {
    chapters[index].headerText = newHeader;
}

function displayFolderPreview() {
    const preview = document.getElementById('imagesPreview');
    preview.innerHTML = '';

    let pageNum = 1;

    chapters.forEach((chapter, chapterIndex) => {
        // Add chapter divider
        const divider = document.createElement('div');
        divider.style.cssText = 'grid-column: 1 / -1; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; margin: 10px 0; text-align: center; color: white; font-weight: 600; font-size: 14px;';

        // Show header and/or title
        let dividerText = '📖 ';
        if (chapter.headerText && chapter.headerText.trim() !== '') {
            dividerText += chapter.headerText;
            if (chapter.title && chapter.title.trim() !== '') {
                dividerText += ' - ' + chapter.title;
            }
        } else if (chapter.title && chapter.title.trim() !== '') {
            dividerText += chapter.title;
        } else {
            dividerText += `Chapter ${chapterIndex + 1}`;
        }
        dividerText += ` (${chapter.images.length} pages)`;

        divider.textContent = dividerText;
        preview.appendChild(divider);

        // Add chapter images
        chapter.images.forEach((file) => {
            const currentPageNum = pageNum++; // Capture page number before async call
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.style.cssText = 'border: 2px solid #ddd; border-radius: 5px; overflow: hidden; position: relative;';
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" style="width: 100%; height: 120px; object-fit: cover; display: block;">
                    <div style="padding: 5px; background: #f5f5f5; font-size: 11px; text-align: center;">
                        Page ${currentPageNum}
                    </div>
                `;
                preview.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    });
}

function removeImage(index) {
    const filesArray = Array.from(uploadedImages);
    filesArray.splice(index, 1);

    // Sort remaining files to maintain order
    filesArray.sort(naturalSort);

    // Create new FileList
    const dt = new DataTransfer();
    filesArray.forEach(file => dt.items.add(file));
    uploadedImages = dt.files;
    imagesUpload.files = dt.files;

    handleImagesUpload(filesArray);
}

// Process upload (PDF or Images)
async function processUpload() {
    const title = document.getElementById('flipbookTitle').value.trim();

    if (!title) {
        alert('Please enter a flipbook title');
        return;
    }

    if (uploadMethod === 'pdf') {
        await convertPDF();
    } else {
        if (useFolderUpload && chapters.length > 0) {
            await processChapters();
        } else {
            await processImages();
        }
    }
}

// Convert PDF to images
async function convertPDF() {
    const pdfFile = pdfUpload.files[0];

    if (!pdfFile) {
        alert('Please upload a PDF file');
        return;
    }

    // Move to step 2
    goToStep(2);

    const progressFill = document.getElementById('conversionProgress');
    const pagePreview = document.getElementById('pagePreview');
    pagePreview.innerHTML = '';
    pages = [];
    pageFilenames = [];

    try {
        const arrayBuffer = await pdfFile.arrayBuffer();
        const pdf = await pdfjsLib.getDocument(arrayBuffer).promise;
        const numPages = pdf.numPages;

        console.log(`Converting ${numPages} pages...`);

        for (let pageNum = 1; pageNum <= numPages; pageNum++) {
            const page = await pdf.getPage(pageNum);

            // Use lower scale for first 3 pages for faster initial load
            // Then higher quality for rest
            const scale = pageNum <= 3 ? 1.5 : 1.8;
            const quality = pageNum <= 3 ? 0.75 : 0.85;

            const viewport = page.getViewport({ scale: scale });

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;

            const imageData = canvas.toDataURL('image/jpeg', quality);
            pages.push({
                pageNumber: pageNum,
                data: imageData
            });
            pageFilenames.push(`PDF Page ${pageNum}`);

            // Update progress
            const progress = Math.round((pageNum / numPages) * 100);
            progressFill.style.width = progress + '%';
            progressFill.textContent = progress + '%';

            // Add preview
            const previewDiv = document.createElement('div');
            previewDiv.className = 'page-preview-item';
            previewDiv.innerHTML = `
                <img src="${imageData}" alt="Page ${pageNum}">
                <div class="page-label">Page ${pageNum}</div>
            `;
            pagePreview.appendChild(previewDiv);
        }

        // Show next button
        document.getElementById('audioUploadBtn').style.display = 'block';

    } catch (error) {
        console.error('Error converting PDF:', error);
        alert('Error converting PDF: ' + error.message);
        goToStep(1);
    }
}

// Process uploaded images
async function processImages() {
    if (uploadedImages.length === 0) {
        alert('Please upload at least one image');
        return;
    }

    // Move to step 2
    goToStep(2);

    const progressFill = document.getElementById('conversionProgress');
    const pagePreview = document.getElementById('pagePreview');
    pagePreview.innerHTML = '';
    pages = [];
    pageFilenames = [];

    try {
        const numImages = uploadedImages.length;
        console.log(`Processing ${numImages} images...`);

        for (let i = 0; i < numImages; i++) {
            const file = uploadedImages[i];

            // Read image as data URL
            const imageData = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });

            pages.push({
                pageNumber: i + 1,
                data: imageData
            });
            pageFilenames.push(file.name);

            // Update progress
            const progress = Math.round(((i + 1) / numImages) * 100);
            progressFill.style.width = progress + '%';
            progressFill.textContent = progress + '%';

            // Add preview
            const previewDiv = document.createElement('div');
            previewDiv.className = 'page-preview-item';
            previewDiv.innerHTML = `
                <img src="${imageData}" alt="Page ${i + 1}">
                <div class="page-label">Page ${i + 1}</div>
            `;
            pagePreview.appendChild(previewDiv);
        }

        // Show next button
        document.getElementById('audioUploadBtn').style.display = 'block';

    } catch (error) {
        console.error('Error processing images:', error);
        alert('Error processing images: ' + error.message);
        goToStep(1);
    }
}

// Process chapters with title slides
async function processChapters() {
    if (chapters.length === 0) {
        alert('No chapters detected');
        return;
    }

    // Move to step 2
    goToStep(2);

    const progressFill = document.getElementById('conversionProgress');
    const pagePreview = document.getElementById('pagePreview');
    pagePreview.innerHTML = '';
    pages = [];
    pageFilenames = [];

    try {
        let totalImages = 0;
        chapters.forEach(chapter => totalImages += chapter.images.length);
        const totalPages = totalImages + chapters.length; // Add chapter title slides

        let processedCount = 0;
        let pageNum = 1;

        console.log(`Processing ${chapters.length} chapters with ${totalPages} total pages...`);

        for (let chapterIndex = 0; chapterIndex < chapters.length; chapterIndex++) {
            const chapter = chapters[chapterIndex];

            // Create chapter title slide
            const titleSlide = await createChapterTitleSlide(chapter.headerText, chapter.title);
            pages.push({
                pageNumber: pageNum++,
                data: titleSlide,
                isChapterTitle: true
            });
            const titleText = chapter.headerText && chapter.title ? `${chapter.headerText} - ${chapter.title}` :
                              chapter.headerText || chapter.title || `Chapter ${chapterIndex + 1}`;
            pageFilenames.push(`📖 ${titleText}`);

            // Add preview for title slide
            const titlePreview = document.createElement('div');
            titlePreview.className = 'page-preview-item';
            titlePreview.innerHTML = `
                <img src="${titleSlide}" alt="Chapter ${chapterIndex + 1}">
                <div class="page-label">📖 Chapter ${chapterIndex + 1}</div>
            `;
            pagePreview.appendChild(titlePreview);

            processedCount++;
            const progress = Math.round((processedCount / totalPages) * 100);
            progressFill.style.width = progress + '%';
            progressFill.textContent = progress + '%';

            // Process chapter images
            for (let i = 0; i < chapter.images.length; i++) {
                const file = chapter.images[i];

                const imageData = await new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = (e) => resolve(e.target.result);
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });

                pages.push({
                    pageNumber: pageNum++,
                    data: imageData
                });
                pageFilenames.push(file.name);

                // Add preview
                const previewDiv = document.createElement('div');
                previewDiv.className = 'page-preview-item';
                previewDiv.innerHTML = `
                    <img src="${imageData}" alt="Page ${pageNum - 1}">
                    <div class="page-label">Page ${pageNum - 1}</div>
                `;
                pagePreview.appendChild(previewDiv);

                processedCount++;
                const progressPct = Math.round((processedCount / totalPages) * 100);
                progressFill.style.width = progressPct + '%';
                progressFill.textContent = progressPct + '%';
            }
        }

        console.log(`Created ${pages.length} pages (${chapters.length} title slides + ${totalImages} images)`);

        // Show next button
        document.getElementById('audioUploadBtn').style.display = 'block';

    } catch (error) {
        console.error('Error processing chapters:', error);
        alert('Error processing chapters: ' + error.message);
        goToStep(1);
    }
}

// Create a chapter title slide as an image
async function createChapterTitleSlide(headerText, title) {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        canvas.width = 1200;
        canvas.height = 1600;
        const ctx = canvas.getContext('2d');

        // Background gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
        gradient.addColorStop(0, '#667eea');
        gradient.addColorStop(1, '#764ba2');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Add thin border 15 pixels from edges
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.lineWidth = 2;
        ctx.strokeRect(15, 15, canvas.width - 30, canvas.height - 30);

        // Center vertically based on what's shown
        ctx.textAlign = 'center';
        let currentY = canvas.height / 2;

        // If we have both header and title, position them together
        if (headerText && headerText.trim() !== '' && title && title.trim() !== '') {
            currentY = canvas.height / 2 - 100; // Move up a bit for both
        }

        // Draw header text (big text at top) if not blank
        if (headerText && headerText.trim() !== '') {
            ctx.fillStyle = 'rgba(255, 255, 255, 0.3)';
            ctx.font = 'bold 120px Arial';
            ctx.fillText(headerText.trim(), canvas.width / 2, currentY);
            currentY += 200; // Space before subtitle
        }

        // Draw title (subtitle) if not blank
        if (title && title.trim() !== '') {
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 80px Arial';

            // Word wrap for long titles
            const words = title.trim().split(' ');
            const lines = [];
            let currentLine = words[0];

            for (let i = 1; i < words.length; i++) {
                const testLine = currentLine + ' ' + words[i];
                const metrics = ctx.measureText(testLine);
                if (metrics.width > canvas.width - 200) {
                    lines.push(currentLine);
                    currentLine = words[i];
                } else {
                    currentLine = testLine;
                }
            }
            lines.push(currentLine);

            // Draw title lines
            const lineHeight = 100;
            lines.forEach((line, index) => {
                ctx.fillText(line, canvas.width / 2, currentY + (index * lineHeight));
            });
        }

        // Convert to data URL
        resolve(canvas.toDataURL('image/png'));
    });
}

// Audio upload handling
const audioUpload = document.getElementById('audioUpload');
const audioLibraryList = document.getElementById('audioLibraryList');

audioUpload.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(event) {
            audioLibrary.push({
                name: file.name.replace('.mp3', ''),
                data: event.target.result
            });
            updateAudioLibrary();
        };
        reader.readAsDataURL(file);
    });
});

function updateAudioLibrary() {
    audioLibraryList.innerHTML = audioLibrary.map((audio, index) => `
        <div class="audio-item">
            <span class="audio-item-name">🎵 ${audio.name}</span>
            <button onclick="removeAudio(${index})" class="btn btn-sm btn-danger">Remove</button>
        </div>
    `).join('');

    // Update step 4 assignment list
    updateAudioAssignmentList();
}

function removeAudio(index) {
    audioLibrary.splice(index, 1);
    updateAudioLibrary();
}

// Track selected pages for batch assignment
let selectedPages = new Set();

function updateAudioAssignmentList() {
    const assignmentList = document.getElementById('audioAssignmentList');

    if (pages.length === 0) {
        assignmentList.innerHTML = '<p style="color: #666;">No pages available. Please convert a PDF first.</p>';
        return;
    }

    // Add batch assignment controls at the top
    const options = audioLibrary.map((audio, audioIndex) =>
        `<option value="${audioIndex}">${audio.name}</option>`
    ).join('');

    let html = `
        <div style="background: #e7f3ff; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #667eea;">
            <h3 style="margin-bottom: 15px; color: #667eea;">🎵 Batch Audio Assignment</h3>
            <p style="margin-bottom: 15px; color: #666;">Select pages below (click to select, Shift+click for range), then assign audio to all selected pages at once.</p>

            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Assign to selected pages:</label>
                    <select id="batchAudioSelect" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                        <option value="">Choose audio...</option>
                        ${options}
                    </select>
                </div>
                <button onclick="applyBatchAssignment()" class="btn btn-primary" style="margin-top: 24px;">
                    Apply to Selected (<span id="selectedCount">0</span> pages)
                </button>
                <button onclick="selectAllPages()" class="btn btn-secondary" style="margin-top: 24px;">
                    Select All
                </button>
                <button onclick="clearSelection()" class="btn btn-secondary" style="margin-top: 24px;">
                    Clear Selection
                </button>
            </div>
        </div>

        <div style="margin-bottom: 15px; color: #666;">
            <strong>Individual Page Assignments:</strong> (Click on page numbers to select for batch assignment)
        </div>
    `;

    html += pages.map((page, index) => {
        const pageOptions = audioLibrary.map((audio, audioIndex) =>
            `<option value="${audioIndex}">${audio.name}</option>`
        ).join('');

        const isSelected = selectedPages.has(index);

        return `
            <div class="audio-assignment-item ${isSelected ? 'selected' : ''}" id="page-item-${index}">
                <input type="checkbox"
                       id="page-checkbox-${index}"
                       ${isSelected ? 'checked' : ''}
                       onchange="togglePageSelection(${index})"
                       style="margin-right: 10px; width: 20px; height: 20px; cursor: pointer;">
                <span class="page-number"
                      onclick="togglePageSelection(${index})"
                      style="cursor: pointer; user-select: none;">
                    Page ${page.pageNumber}:
                </span>
                <select id="audio-page-${index}" onchange="updatePageAudio(${index})">
                    <option value="">No audio</option>
                    ${pageOptions}
                </select>
            </div>
        `;
    }).join('');

    assignmentList.innerHTML = html;
}

function togglePageSelection(index, shiftKey = false) {
    if (shiftKey && selectedPages.size > 0) {
        // Range selection with shift
        const selectedIndices = Array.from(selectedPages);
        const lastSelected = Math.max(...selectedIndices);
        const start = Math.min(lastSelected, index);
        const end = Math.max(lastSelected, index);

        for (let i = start; i <= end; i++) {
            selectedPages.add(i);
            const checkbox = document.getElementById(`page-checkbox-${i}`);
            const item = document.getElementById(`page-item-${i}`);
            if (checkbox) checkbox.checked = true;
            if (item) item.classList.add('selected');
        }
    } else {
        // Toggle individual selection
        if (selectedPages.has(index)) {
            selectedPages.delete(index);
            const checkbox = document.getElementById(`page-checkbox-${index}`);
            const item = document.getElementById(`page-item-${index}`);
            if (checkbox) checkbox.checked = false;
            if (item) item.classList.remove('selected');
        } else {
            selectedPages.add(index);
            const checkbox = document.getElementById(`page-checkbox-${index}`);
            const item = document.getElementById(`page-item-${index}`);
            if (checkbox) checkbox.checked = true;
            if (item) item.classList.add('selected');
        }
    }

    updateSelectedCount();
}

function selectAllPages() {
    selectedPages.clear();
    pages.forEach((page, index) => {
        selectedPages.add(index);
        const checkbox = document.getElementById(`page-checkbox-${index}`);
        const item = document.getElementById(`page-item-${index}`);
        if (checkbox) checkbox.checked = true;
        if (item) item.classList.add('selected');
    });
    updateSelectedCount();
}

function clearSelection() {
    selectedPages.clear();
    pages.forEach((page, index) => {
        const checkbox = document.getElementById(`page-checkbox-${index}`);
        const item = document.getElementById(`page-item-${index}`);
        if (checkbox) checkbox.checked = false;
        if (item) item.classList.remove('selected');
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const countSpan = document.getElementById('selectedCount');
    if (countSpan) {
        countSpan.textContent = selectedPages.size;
    }
}

function applyBatchAssignment() {
    const batchSelect = document.getElementById('batchAudioSelect');
    const audioValue = batchSelect.value;

    if (selectedPages.size === 0) {
        alert('Please select at least one page');
        return;
    }

    if (audioValue === '') {
        alert('Please select an audio track');
        return;
    }

    // Apply to all selected pages
    selectedPages.forEach(index => {
        const select = document.getElementById(`audio-page-${index}`);
        if (select) {
            select.value = audioValue;
        }
    });

    alert(`Audio assigned to ${selectedPages.size} page(s)`);
    clearSelection();
}

function updatePageAudio(index) {
    // This is called when individual dropdowns change
    // You can add logic here if needed
}

// When moving to step 4, update the assignment list
document.querySelectorAll('button[onclick^="goToStep(4)"]').forEach(btn => {
    btn.addEventListener('click', () => {
        updateAudioAssignmentList();
    });
});

// Navigate to page reorder step
function goToPageReorder() {
    goToStep(5);
    displayPageReorderGrid();
}

// Display the page reorder grid with drag-and-drop
function displayPageReorderGrid() {
    const grid = document.getElementById('pageReorderGrid');
    grid.innerHTML = '';

    console.log('displayPageReorderGrid - pages:', pages.length, 'pageFilenames:', pageFilenames.length);
    console.log('pageFilenames array:', pageFilenames);

    // Ensure pageFilenames array matches pages array length
    if (pageFilenames.length !== pages.length) {
        console.warn('pageFilenames length mismatch! Regenerating...');
        pageFilenames = pages.map((page, idx) => {
            if (uploadedImages[idx]) {
                return uploadedImages[idx].name;
            } else if (page.isChapterTitle) {
                return `Chapter Title ${idx + 1}`;
            } else {
                return `Page ${idx + 1}`;
            }
        });
    }

    pages.forEach((page, index) => {
        const item = document.createElement('div');
        item.className = 'page-reorder-item';
        item.draggable = true;
        item.dataset.index = index;

        // Get filename with better fallback
        let filename = pageFilenames[index];
        if (!filename || filename === '') {
            if (uploadedImages[index]) {
                filename = uploadedImages[index].name;
            } else if (page.isChapterTitle) {
                filename = '📖 Chapter Title';
            } else {
                filename = `image-${index + 1}.jpg`;
            }
        }

        item.innerHTML = `
            <img src="${page.data}" alt="Page ${index + 1}">
            <div class="page-number">Page ${index + 1}</div>
            <div class="page-filename">${filename}</div>
        `;

        // Drag events
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragenter', handleDragEnter);
        item.addEventListener('dragleave', handleDragLeave);
        item.addEventListener('dragend', handleDragEnd);

        grid.appendChild(item);
    });
}

let draggedIndex = null;

function handleDragStart(e) {
    draggedIndex = parseInt(this.dataset.index);
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }

    const dropIndex = parseInt(this.dataset.index);

    if (draggedIndex !== dropIndex) {
        // Reorder arrays
        const movedPage = pages[draggedIndex];
        const movedFilename = pageFilenames[draggedIndex];

        pages.splice(draggedIndex, 1);
        pageFilenames.splice(draggedIndex, 1);

        pages.splice(dropIndex, 0, movedPage);
        pageFilenames.splice(dropIndex, 0, movedFilename);

        // Update page numbers
        pages.forEach((page, idx) => {
            page.pageNumber = idx + 1;
        });

        // Refresh the grid
        displayPageReorderGrid();
    }

    return false;
}

function handleDragEnd(e) {
    document.querySelectorAll('.page-reorder-item').forEach(item => {
        item.classList.remove('dragging');
        item.classList.remove('drag-over');
    });
}

// Save flipbook
async function saveFlipbook() {
    const title = document.getElementById('flipbookTitle').value.trim();
    const description = document.getElementById('flipbookDescription').value.trim();

    if (pages.length === 0) {
        alert('No pages to save. Please convert a PDF first.');
        return;
    }

    // Detect orientation from first page
    const orientation = await detectOrientation(pages[0].data);

    // Collect audio assignments
    const audioAssignments = {};
    pages.forEach((page, index) => {
        const select = document.getElementById(`audio-page-${index}`);
        if (select && select.value !== '') {
            audioAssignments[index] = parseInt(select.value);
        }
    });

    console.log('Audio assignments being sent:', audioAssignments);
    console.log('Audio library:', audioLibrary.map((a, i) => ({ index: i, name: a.name })));

    // If more than 50 pages, use batch upload to avoid 413 errors
    if (pages.length > 50) {
        console.log(`Large flipbook detected (${pages.length} pages). Using batch upload...`);
        await saveFlipbookInBatches(title, description, orientation, audioAssignments);
        return;
    }

    // Prepare data to send (for small flipbooks)
    const data = {
        title: title,
        description: description,
        orientation: orientation,
        pages: pages,
        audioLibrary: audioLibrary,
        audioAssignments: audioAssignments
    };

    // If editing, include the flipbook ID
    if (currentFlipbookId) {
        data.flipbookId = currentFlipbookId;
    }

    console.log('Saving flipbook...', data);

    try {
        const response = await fetch('flipbook-api-save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const responseText = await response.text();

        // Try to parse as JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('Server returned non-JSON response:', responseText);
            alert('Server error: The server returned an invalid response. Check console for details.');
            return;
        }

        if (result.success) {
            currentFlipbookId = result.flipbookId;
            document.getElementById('successMessage').textContent = `"${title}" has been created successfully!`;
            goToStep(6);
        } else {
            alert('Error saving flipbook: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error saving flipbook:', error);
        alert('Error saving flipbook: ' + error.message);
    }
}

// Save large flipbooks in batches to avoid 413 errors
async function saveFlipbookInBatches(title, description, orientation, audioAssignments) {
    try {
        // Show progress message
        alert(`Saving large flipbook with ${pages.length} pages. This may take a minute...`);

        // Step 1: Create flipbook with just 1 page (to get flipbook ID)
        console.log('Step 1: Creating flipbook...');
        const initialData = {
            title: title,
            description: description,
            orientation: orientation,
            pages: [pages[0]], // Just first page
            audioLibrary: [],
            audioAssignments: {}
        };

        const createResponse = await fetch('flipbook-api-save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(initialData)
        });

        const createResult = await createResponse.json();
        if (!createResponse.ok || !createResult.success) {
            throw new Error(createResult.error || 'Failed to create flipbook');
        }

        const flipbookId = createResult.flipbookId;
        console.log(`Flipbook created with ID: ${flipbookId}`);

        // Step 2: Upload remaining pages in batches of 20
        const BATCH_SIZE = 20;
        const remainingPages = pages.slice(1); // Skip first page (already uploaded)
        const totalBatches = Math.ceil(remainingPages.length / BATCH_SIZE);

        console.log(`Step 2: Uploading ${remainingPages.length} remaining pages in ${totalBatches} batches...`);

        for (let i = 0; i < remainingPages.length; i += BATCH_SIZE) {
            const batch = remainingPages.slice(i, Math.min(i + BATCH_SIZE, remainingPages.length));
            const batchNum = Math.floor(i / BATCH_SIZE) + 1;

            console.log(`Uploading batch ${batchNum}/${totalBatches} (${batch.length} pages)...`);

            const batchData = {
                flipbookId: flipbookId,
                title: title,
                description: description,
                orientation: orientation,
                pages: batch,
                audioLibrary: [],
                audioAssignments: {}
            };

            const batchResponse = await fetch('flipbook-api-save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(batchData)
            });

            const batchResult = await batchResponse.json();
            if (!batchResponse.ok || !batchResult.success) {
                throw new Error(`Batch ${batchNum} failed: ${batchResult.error || 'Unknown error'}`);
            }

            console.log(`Batch ${batchNum}/${totalBatches} completed`);
        }

        // Step 3: Upload audio library
        console.log('Step 3: Uploading audio library...');
        if (audioLibrary.length > 0) {
            const audioData = {
                flipbookId: flipbookId,
                title: title,
                description: description,
                orientation: orientation,
                pages: [], // Empty - already saved
                audioLibrary: audioLibrary,
                audioAssignments: {}
            };

            const audioResponse = await fetch('flipbook-api-save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(audioData)
            });

            const audioResult = await audioResponse.json();
            if (!audioResponse.ok || !audioResult.success) {
                console.error('Warning: Audio upload failed:', audioResult.error);
                // Don't throw - audio is optional
            } else {
                console.log('Audio library uploaded successfully');
            }
        }

        // Step 4: Save audio assignments
        console.log('Step 4: Saving audio assignments...');
        if (Object.keys(audioAssignments).length > 0) {
            const assignData = {
                flipbookId: flipbookId,
                title: title,
                description: description,
                orientation: orientation,
                pages: [], // Empty - already saved
                audioLibrary: [], // Empty - already saved
                audioAssignments: audioAssignments
            };

            const assignResponse = await fetch('flipbook-api-save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(assignData)
            });

            const assignResult = await assignResponse.json();
            if (!assignResponse.ok || !assignResult.success) {
                console.error('Warning: Audio assignments failed:', assignResult.error);
                // Don't throw - assignments are optional
            } else {
                console.log('Audio assignments saved successfully');
            }
        }

        // Success!
        currentFlipbookId = flipbookId;
        document.getElementById('successMessage').textContent = `"${title}" has been created successfully with ${pages.length} pages!`;
        goToStep(6);

    } catch (error) {
        console.error('Error in batch save:', error);
        alert('Error saving flipbook: ' + error.message);
    }
}

// Detect image orientation
function detectOrientation(imageData) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = function() {
            const isLandscape = img.width > img.height;
            resolve(isLandscape ? 'landscape' : 'portrait');
        };
        img.onerror = function() {
            resolve('portrait'); // Default to portrait on error
        };
        img.src = imageData;
    });
}

// View flipbook
function viewFlipbook(id) {
    window.open(`flipbook-public-viewer.php?id=${id}`, '_blank');
}

// Edit flipbook
async function editFlipbook(id) {
    try {
        // Fetch flipbook data
        const response = await fetch(`flipbook-api-get.php?id=${id}`);
        const result = await response.json();

        if (!result.success) {
            alert('Error loading flipbook: ' + (result.error || 'Unknown error'));
            return;
        }

        const data = result.data;

        // Populate form fields
        document.getElementById('flipbookTitle').value = data.flipbook.title;
        document.getElementById('flipbookDescription').value = data.flipbook.description || '';

        // Load pages - use image_path if available (new system), otherwise image_data (old system)
        pages = data.pages.map(p => ({
            pageNumber: p.page_number,
            data: p.image_path || p.image_data
        }));

        // Load audio library
        audioLibrary = data.audioFiles.map(a => ({
            name: a.name,
            data: a.audio_data,
            id: a.id
        }));

        // Store the flipbook ID for updating
        currentFlipbookId = id;

        // Show create section and go to step 2 (skip PDF upload)
        const section = document.getElementById('createSection');
        section.style.display = 'block';
        section.scrollIntoView({ behavior: 'smooth' });

        // Display converted pages
        goToStep(2);
        const pagePreview = document.getElementById('pagePreview');
        pagePreview.innerHTML = '';

        pages.forEach((page, index) => {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'page-preview-item';
            previewDiv.innerHTML = `
                <img src="${page.data}" alt="Page ${page.pageNumber}">
                <div class="page-label">Page ${page.pageNumber}</div>
            `;
            pagePreview.appendChild(previewDiv);
        });

        // Update progress bar
        document.getElementById('conversionProgress').style.width = '100%';
        document.getElementById('conversionProgress').textContent = '100%';
        document.getElementById('audioUploadBtn').style.display = 'block';

        // Move to step 3 to show audio
        setTimeout(() => {
            goToStep(3);
            updateAudioLibrary();

            // Go to step 4 for assignments
            setTimeout(() => {
                goToStep(4);
                updateAudioAssignmentList();

                // Load existing assignments
                data.assignments.forEach(assignment => {
                    const pageIndex = pages.findIndex(p => p.pageNumber === assignment.page_number);
                    const audioIndex = audioLibrary.findIndex(a => a.id === assignment.audio_id);

                    if (pageIndex !== -1 && audioIndex !== -1) {
                        const select = document.getElementById(`audio-page-${pageIndex}`);
                        if (select) {
                            select.value = audioIndex;
                        }
                    }
                });
            }, 500);
        }, 500);

    } catch (error) {
        console.error('Error editing flipbook:', error);
        alert('Error loading flipbook: ' + error.message);
    }
}

// Delete flipbook
async function deleteFlipbook(id) {
    if (!confirm('Are you sure you want to delete this flipbook?')) {
        return;
    }

    try {
        const response = await fetch('flipbook-api-delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert('Error deleting flipbook: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error deleting flipbook:', error);
        alert('Error deleting flipbook: ' + error.message);
    }
}

// Show embed code modal
function showEmbedCode(flipbookId, title) {
    const modal = document.getElementById('embedModal');
    if (!modal) {
        console.error('Embed modal not found');
        return;
    }

    const baseUrl = window.location.origin + window.location.pathname.replace('flipbook-admin-dashboard.php', '');
    const viewerUrl = baseUrl + 'flipbook-public-viewer.php?id=' + flipbookId;

    // Fixed height iframe embed code (like heyzine - simple and clean)
    const iframeCode = `<iframe src="${viewerUrl}"
        allowfullscreen="allowfullscreen"
        scrolling="no"
        style="border: 1px solid lightgray; width: 100%; height: 600px;"
        allow="clipboard-write">
</iframe>`;

    // Responsive container version (maintains aspect ratio)
    const responsiveCode = `<div style="position: relative; width: 100%; padding-bottom: 133.33%; overflow: hidden;">
    <iframe src="${viewerUrl}"
            allowfullscreen="allowfullscreen"
            scrolling="no"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 1px solid lightgray;"
            allow="clipboard-write">
    </iframe>
</div>`;

    // Direct link
    const directLink = viewerUrl;

    // Update modal content
    document.getElementById('embedTitle').textContent = title;
    document.getElementById('embedIframeCode').textContent = iframeCode;
    document.getElementById('embedResponsiveCode').textContent = responsiveCode;
    document.getElementById('embedDirectLink').textContent = directLink;
    document.getElementById('embedPreviewFrame').src = viewerUrl;

    // Show modal
    modal.classList.add('active');
}

// Close embed modal
function closeEmbedModal() {
    const modal = document.getElementById('embedModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

// Copy embed code to clipboard
function copyEmbedCode(type) {
    let elementId;
    if (type === 'iframe') {
        elementId = 'embedIframeCode';
    } else if (type === 'responsive') {
        elementId = 'embedResponsiveCode';
    } else if (type === 'link') {
        elementId = 'embedDirectLink';
    }

    const element = document.getElementById(elementId);
    if (!element) {
        console.error('Element not found:', elementId);
        return;
    }

    const text = element.textContent;

    navigator.clipboard.writeText(text).then(() => {
        // Show copied feedback
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = '✓ Copied!';
        btn.classList.add('copied');

        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('copied');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard. Please copy manually.');
    });
}

console.log('📚 Flipbook Admin Dashboard loaded!');
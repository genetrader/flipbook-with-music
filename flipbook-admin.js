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

// PDF.js setup is done in the HTML file

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
        processBtn.disabled = uploadedImages.length === 0;
    }
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
    document.getElementById('processBtn').disabled = true;

    // Reset to PDF method
    document.getElementById('uploadMethodPDF').checked = true;
    switchUploadMethod('pdf');

    pages = [];
    audioLibrary = [];
    uploadedImages = [];
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
const imagesPreview = document.getElementById('imagesPreview');

imagesUploadArea.addEventListener('click', () => {
    imagesUpload.click();
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

function handleImagesUpload(files) {
    uploadedImages = files;
    imagesPreview.innerHTML = '';

    files.forEach((file, index) => {
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

function removeImage(index) {
    const filesArray = Array.from(uploadedImages);
    filesArray.splice(index, 1);

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
        await processImages();
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

    // Prepare data to send
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
            goToStep(5);
        } else {
            alert('Error saving flipbook: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error saving flipbook:', error);
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

console.log('📚 Flipbook Admin Dashboard loaded!');
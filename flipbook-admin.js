/**
 * Flipbook Admin Dashboard JavaScript
 * Handles PDF conversion, audio upload, and flipbook creation
 */

// Global variables
let currentFlipbookId = null;
let pages = [];
let audioLibrary = [];
let currentStep = 1;

// PDF.js setup is done in the HTML file

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
    document.getElementById('pdfInfo').style.display = 'none';
    document.getElementById('convertBtn').disabled = true;
    pages = [];
    audioLibrary = [];
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
    convertBtn.disabled = false;
}

// Convert PDF to images
async function convertPDF() {
    const title = document.getElementById('flipbookTitle').value.trim();
    const description = document.getElementById('flipbookDescription').value.trim();
    const pdfFile = pdfUpload.files[0];

    if (!title) {
        alert('Please enter a flipbook title');
        return;
    }

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
            // Reduced scale from 2.0 to 1.5 for much smaller file sizes
            const viewport = page.getViewport({ scale: 1.5 });

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;

            // Reduced quality from 0.9 to 0.65 for faster loading
            const imageData = canvas.toDataURL('image/jpeg', 0.65);
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

        // Load pages
        pages = data.pages.map(p => ({
            pageNumber: p.page_number,
            data: p.image_data
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
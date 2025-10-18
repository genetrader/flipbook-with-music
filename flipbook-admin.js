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
            const viewport = page.getViewport({ scale: 2.0 });

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;

            const imageData = canvas.toDataURL('image/jpeg', 0.9);
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

function updateAudioAssignmentList() {
    const assignmentList = document.getElementById('audioAssignmentList');

    if (pages.length === 0) {
        assignmentList.innerHTML = '<p style="color: #666;">No pages available. Please convert a PDF first.</p>';
        return;
    }

    assignmentList.innerHTML = pages.map((page, index) => {
        const options = audioLibrary.map((audio, audioIndex) =>
            `<option value="${audioIndex}">${audio.name}</option>`
        ).join('');

        return `
            <div class="audio-assignment-item">
                <span class="page-number">Page ${page.pageNumber}:</span>
                <select id="audio-page-${index}">
                    <option value="">No audio</option>
                    ${options}
                </select>
            </div>
        `;
    }).join('');
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

    console.log('Saving flipbook...', data);

    try {
        const response = await fetch('flipbook-api-save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

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
function editFlipbook(id) {
    alert('Edit functionality coming soon! ID: ' + id);
    // TODO: Load flipbook data and populate form
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
// Admin Panel JavaScript
const HEYZINE_CLIENT_ID = '5b16e2e5c1eee9bd';
const HEYZINE_API_KEY = 'c766f1423bedb75fc6e08660e86edff5345485a8.5b16e2e5c1eee9bd';

// Simple password protection (in production, use proper authentication)
const ADMIN_PASSWORD = 'admin123'; // Change this password!

document.addEventListener('DOMContentLoaded', function() {
    // Check if already logged in
    const isLoggedIn = sessionStorage.getItem('adminLoggedIn') === 'true';
    if (isLoggedIn) {
        showAdminPanel();
    }
    
    // Auto-load Cork the Mechanic Episode 1 if no flipbook is set
    const savedUrl = localStorage.getItem('flipbookUrl');
    if (!savedUrl) {
        // Set Cork the Mechanic Episode 1 as default
        const corkEpisode1Url = 'https://heyzine.com/flip-book/b1f71ef0a6.html';
        localStorage.setItem('flipbookUrl', corkEpisode1Url);
    }
    
    // Login form handler
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const password = document.getElementById('admin-password').value;
        
        if (password === ADMIN_PASSWORD) {
            sessionStorage.setItem('adminLoggedIn', 'true');
            showAdminPanel();
        } else {
            showMessage('Incorrect password', 'error');
        }
    });
    
    // Flipbook form handler
    document.getElementById('flipbook-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const pdfUrl = document.getElementById('pdf-url').value;
        const useHeyzine = document.getElementById('use-heyzine').checked;
        
        if (useHeyzine) {
            // Convert PDF URL using Heyzine API
            const heyzineUrl = `https://heyzine.com/flip-book/${HEYZINE_CLIENT_ID}/${encodeURIComponent(pdfUrl)}`;
            updateFlipbook(heyzineUrl);
        } else {
            // Use direct PDF URL
            updateFlipbook(pdfUrl);
        }
    });
    
    // Clear flipbook button
    document.getElementById('clear-flipbook').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear the flipbook?')) {
            localStorage.removeItem('flipbookUrl');
            document.getElementById('current-pdf-url').textContent = 'Not set';
            document.getElementById('preview-container').innerHTML = '<p style="color: #666;">No flipbook loaded yet</p>';
            showMessage('Flipbook cleared successfully', 'success');
        }
    });
    
    // Logout button
    document.getElementById('logout-btn').addEventListener('click', function() {
        sessionStorage.removeItem('adminLoggedIn');
        location.reload();
    });
    
    // Quick load Cork the Mechanic Episode 1
    document.getElementById('load-cork-episode1').addEventListener('click', function() {
        // Use the exact Cork the Mechanic Episode 1 URL
        const corkEpisode1Url = 'https://heyzine.com/flip-book/b1f71ef0a6.html';
        
        updateFlipbook(corkEpisode1Url);
        
        // Also fill in the form field for reference
        document.getElementById('pdf-url').value = corkEpisode1Url;
        document.getElementById('use-heyzine').checked = false; // This is already a Heyzine URL
    });
    
    // Load current flipbook URL
    loadCurrentSettings();
});

function showAdminPanel() {
    document.getElementById('login-section').style.display = 'none';
    document.getElementById('admin-panel').style.display = 'block';
}

function updateFlipbook(url) {
    // Save to localStorage
    localStorage.setItem('flipbookUrl', url);
    
    // Update current settings display
    document.getElementById('current-pdf-url').textContent = url;
    
    // Update preview
    const previewContainer = document.getElementById('preview-container');
    previewContainer.innerHTML = `<iframe src="${url}" style="width: 100%; height: 500px; border: none;"></iframe>`;
    
    showMessage('Flipbook updated successfully!', 'success');
}

function loadCurrentSettings() {
    const savedUrl = localStorage.getItem('flipbookUrl');
    if (savedUrl) {
        document.getElementById('current-pdf-url').textContent = savedUrl;
        
        // Show preview
        const previewContainer = document.getElementById('preview-container');
        previewContainer.innerHTML = `<iframe src="${savedUrl}" style="width: 100%; height: 500px; border: none;"></iframe>`;
    }
}

function showMessage(message, type) {
    // Remove any existing messages
    const existingMessages = document.querySelectorAll('.success-message, .error-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = type === 'error' ? 'error-message' : 'success-message';
    messageDiv.textContent = message;
    
    // Insert at the top of admin panel
    const adminPanel = document.getElementById('admin-panel');
    adminPanel.insertBefore(messageDiv, adminPanel.firstChild);
    
    // Remove after 3 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}
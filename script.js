document.addEventListener('DOMContentLoaded', function() {
    // Flipbook URLs
    const flipbooks = {
        'cork-2': 'https://heyzine.com/flip-book/1793b849ee.html',
        'cork-3': 'https://heyzine.com/flip-book/fe18813757.html',
        'cork-6': 'https://heyzine.com/flip-book/91e79197a9.html',
        'cork-1': localStorage.getItem('flipbookUrl') || 'https://heyzine.com/flip-book/b1f71ef0a6.html'
    };
    
    // Lightbox elements
    const lightbox = document.getElementById('flipbook-lightbox');
    const lightboxFrame = document.getElementById('lightbox-flipbook-frame');
    const lightboxClose = document.getElementById('lightbox-close');
    const corkCoverClick = document.getElementById('cork-cover-click');
    
    // Create selection modal
    const selectionModal = document.createElement('div');
    selectionModal.className = 'flipbook-selection-modal';
    selectionModal.innerHTML = `
        <div class="selection-modal-content">
            <h3>Select a Comic</h3>
            <div class="flipbook-options">
                <button class="flipbook-option" data-book="cork-2">Cork 2</button>
                <button class="flipbook-option" data-book="cork-3">Cork 3</button>
            </div>
            <button class="selection-close">&times;</button>
        </div>
    `;
    document.body.appendChild(selectionModal);
    
    // Handle selection modal
    const selectionClose = selectionModal.querySelector('.selection-close');
    selectionClose.addEventListener('click', function() {
        selectionModal.classList.remove('active');
    });
    
    // Handle flipbook selection
    const flipbookOptions = selectionModal.querySelectorAll('.flipbook-option');
    flipbookOptions.forEach(option => {
        option.addEventListener('click', function() {
            const bookId = this.getAttribute('data-book');
            const flipbookUrl = flipbooks[bookId];
            if (flipbookUrl && lightbox && lightboxFrame) {
                selectionModal.classList.remove('active');
                lightbox.classList.add('active');
                let zoomedUrl = flipbookUrl;
                if (!zoomedUrl.includes('#')) {
                    zoomedUrl += '#zoom=page-width';
                }
                lightboxFrame.src = zoomedUrl;
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Handle Cork cover click to open selection modal
    if (corkCoverClick) {
        corkCoverClick.addEventListener('click', function() {
            selectionModal.classList.add('active');
        });
    }
    
    // Close lightbox
    if (lightboxClose) {
        lightboxClose.addEventListener('click', function() {
            closeLightbox();
        });
    }
    
    // Home link in lightbox
    const lightboxHome = document.getElementById('lightbox-home');
    if (lightboxHome) {
        lightboxHome.addEventListener('click', function(e) {
            e.preventDefault();
            closeLightbox();
        });
    }
    
    // Close lightbox when clicking outside
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
    }
    
    // ESC key to close lightbox
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            closeLightbox();
        }
    });
    
    function closeLightbox() {
        lightbox.classList.remove('active');
        lightboxFrame.src = '';
        document.body.style.overflow = '';
    }
    
    // Hamburger menu functionality
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    const body = document.body;
    
    if (hamburgerMenu && mobileMenu) {
        hamburgerMenu.addEventListener('click', function() {
            hamburgerMenu.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        });
        
        // Close menu when clicking on a link
        const mobileNavLinks = mobileMenu.querySelectorAll('.mobile-nav-list a');
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburgerMenu.classList.remove('active');
                mobileMenu.classList.remove('active');
                body.style.overflow = '';
            });
        });
        
        // Close menu when clicking outside
        mobileMenu.addEventListener('click', function(e) {
            if (e.target === mobileMenu) {
                hamburgerMenu.classList.remove('active');
                mobileMenu.classList.remove('active');
                body.style.overflow = '';
            }
        });
    }
    
    const panels = document.querySelectorAll('.panel');
    panels.forEach((panel, index) => {
        panel.addEventListener('mouseenter', function() {
            this.style.transform = 'rotate(0deg) scale(1.05)';
        });
        
        panel.addEventListener('mouseleave', function() {
            const rotations = ['1deg', '-1deg', '0.5deg'];
            this.style.transform = `rotate(${rotations[index % 3]})`;
        });
    });
    
    const navLinks = document.querySelectorAll('.nav-list a, .mobile-nav-list a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('href');
            
            if (target === '#home') {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
    
    const siteTitle = document.querySelector('.site-title');
    siteTitle.addEventListener('click', function() {
        this.style.animation = 'bounce 0.5s ease-in-out';
        setTimeout(() => {
            this.style.animation = '';
        }, 500);
    });
    
    // Handle book showcase clicks
    const bookItems = document.querySelectorAll('.book-item');
    bookItems.forEach(item => {
        item.addEventListener('click', function() {
            const bookId = this.getAttribute('data-book');
            
            // For Cork 1, open the flipbook directly
            if (bookId === 'cork-1' && lightbox && lightboxFrame) {
                lightbox.classList.add('active');
                let zoomedUrl = flipbooks['cork-1'];
                if (!zoomedUrl.includes('#')) {
                    zoomedUrl += '#zoom=page-width';
                }
                lightboxFrame.src = zoomedUrl;
                document.body.style.overflow = 'hidden';
            } 
            // For Cork 2+3, open the selection modal
            else if (bookId === 'cork-2-3') {
                selectionModal.classList.add('active');
            }
            // For Prodigy (Cork 6), open the flipbook directly
            else if (bookId === 'prodigy' && lightbox && lightboxFrame) {
                lightbox.classList.add('active');
                let zoomedUrl = flipbooks['cork-6'];
                if (!zoomedUrl.includes('#')) {
                    zoomedUrl += '#zoom=page-width';
                }
                lightboxFrame.src = zoomedUrl;
                document.body.style.overflow = 'hidden';
            }
            else {
                // For other books, show a message
                alert(`${this.querySelector('h3').textContent} - Coming Soon!`);
            }
        });
    });
});

const style = document.createElement('style');
style.textContent = `
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
`;
document.head.appendChild(style);

// Handle Brevo form submission
document.addEventListener('DOMContentLoaded', function() {
    // Check if already submitted in this session
    if (sessionStorage.getItem('newsletterSubmitted') === 'true') {
        const formSection = document.querySelector('.sib-form-container');
        const customSuccess = document.getElementById('custom-success-message');
        
        if (formSection && customSuccess) {
            formSection.style.display = 'none';
            customSuccess.style.display = 'block';
        }
    }
    
    // Monitor for Brevo form changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            const formContainer = document.getElementById('sib-container');
            const customSuccess = document.getElementById('custom-success-message');
            const formSection = document.querySelector('.sib-form-container');
            
            // Check if form container was hidden (successful submission)
            if (formContainer && mutation.target === formContainer && 
                (formContainer.style.display === 'none' || mutation.attributeName === 'style')) {
                
                if (formContainer.style.display === 'none' && customSuccess) {
                    // Hide the entire Brevo form wrapper
                    if (formSection) {
                        formSection.style.display = 'none';
                    }
                    
                    // Show our custom success message
                    customSuccess.style.display = 'block';
                    
                    // Scroll to success message
                    customSuccess.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Mark as submitted in session storage
                    sessionStorage.setItem('newsletterSubmitted', 'true');
                    
                    // Keep success message visible (don't auto-hide)
                }
            }
        });
    });
    
    // Start observing the form container
    setTimeout(function() {
        const formContainer = document.getElementById('sib-container');
        if (formContainer) {
            observer.observe(formContainer, { 
                attributes: true, 
                attributeOldValue: true,
                attributeFilter: ['style'] 
            });
        }
    }, 1000);
});

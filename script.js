document.addEventListener('DOMContentLoaded', function() {
    // Load saved flipbook URL from localStorage, or use default
    const savedFlipbookUrl = localStorage.getItem('flipbookUrl') || 'https://heyzine.com/flip-book/b1f71ef0a6.html';
    
    // Lightbox elements
    const lightbox = document.getElementById('flipbook-lightbox');
    const lightboxFrame = document.getElementById('lightbox-flipbook-frame');
    const lightboxClose = document.getElementById('lightbox-close');
    const corkCoverClick = document.getElementById('cork-cover-click');
    
    // Handle Cork cover click to open lightbox
    if (corkCoverClick) {
        corkCoverClick.addEventListener('click', function() {
            if (lightbox && lightboxFrame) {
                lightbox.classList.add('active');
                // Add zoom parameter to URL if not already present
                let zoomedUrl = savedFlipbookUrl;
                if (!zoomedUrl.includes('#')) {
                    zoomedUrl += '#zoom=page-width';
                }
                lightboxFrame.src = zoomedUrl;
                document.body.style.overflow = 'hidden';
            }
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
            
            // For Cork 1, open the flipbook
            if (bookId === 'cork-1' && lightbox && lightboxFrame) {
                lightbox.classList.add('active');
                // Add zoom parameter to URL if not already present
                let zoomedUrl = savedFlipbookUrl;
                if (!zoomedUrl.includes('#')) {
                    zoomedUrl += '#zoom=page-width';
                }
                lightboxFrame.src = zoomedUrl;
                document.body.style.overflow = 'hidden';
            } else {
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


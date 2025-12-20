document.addEventListener('DOMContentLoaded', function() {
    // Gallery functionality
    const galleryItems = document.querySelectorAll('.gallery-item');
    const galleryLightbox = document.getElementById('gallery-lightbox');
    const galleryImage = document.getElementById('gallery-lightbox-image');
    const galleryClose = document.getElementById('gallery-close');
    const galleryPrev = document.getElementById('gallery-prev');
    const galleryNext = document.getElementById('gallery-next');
    const currentImageSpan = document.getElementById('current-image');
    const totalImagesSpan = document.getElementById('total-images');
    
    let currentIndex = 0;
    const images = [];
    
    // Collect all image sources
    galleryItems.forEach((item, index) => {
        images.push(item.getAttribute('data-src'));
        
        // Add click event to each gallery item
        item.addEventListener('click', function() {
            currentIndex = index;
            openGalleryLightbox();
        });
    });
    
    // Update total images count
    if (totalImagesSpan) {
        totalImagesSpan.textContent = images.length;
    }
    
    function openGalleryLightbox() {
        if (galleryLightbox && galleryImage) {
            galleryLightbox.classList.add('active');
            updateGalleryImage();
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeGalleryLightbox() {
        if (galleryLightbox) {
            galleryLightbox.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    function updateGalleryImage() {
        if (galleryImage && images[currentIndex]) {
            galleryImage.src = images[currentIndex];
            if (currentImageSpan) {
                currentImageSpan.textContent = currentIndex + 1;
            }
        }
    }
    
    function showPrevImage() {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateGalleryImage();
    }
    
    function showNextImage() {
        currentIndex = (currentIndex + 1) % images.length;
        updateGalleryImage();
    }
    
    // Event listeners
    if (galleryClose) {
        galleryClose.addEventListener('click', closeGalleryLightbox);
    }
    
    if (galleryPrev) {
        galleryPrev.addEventListener('click', showPrevImage);
    }
    
    if (galleryNext) {
        galleryNext.addEventListener('click', showNextImage);
    }
    
    // Close on background click
    if (galleryLightbox) {
        galleryLightbox.addEventListener('click', function(e) {
            if (e.target === galleryLightbox) {
                closeGalleryLightbox();
            }
        });
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (galleryLightbox && galleryLightbox.classList.contains('active')) {
            if (e.key === 'Escape') {
                closeGalleryLightbox();
            } else if (e.key === 'ArrowLeft') {
                showPrevImage();
            } else if (e.key === 'ArrowRight') {
                showNextImage();
            }
        }
    });
    
    // Touch/swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    if (galleryLightbox) {
        galleryLightbox.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        galleryLightbox.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
    }
    
    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            showNextImage(); // Swipe left
        }
        if (touchEndX > touchStartX + 50) {
            showPrevImage(); // Swipe right
        }
    }
});
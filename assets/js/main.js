/**
 * Main JavaScript
 * Naomi Wendot Writer & Ministry Website
 * Benchmarked against BCH for seamless navigation
 */

// Check if user prefers reduced motion
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Fade In Up Animation on Scroll
 * Uses IntersectionObserver to trigger animations when elements enter viewport
 */
function initFadeInUp() {
    const elements = document.querySelectorAll('.fade-in-up');
    
    if (elements.length === 0) return;
    
    // Skip animation if user prefers reduced motion
    if (prefersReducedMotion) {
        elements.forEach(el => el.classList.add('visible'));
        return;
    }
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    });
    
    elements.forEach(el => observer.observe(el));
}

/**
 * Navbar Scroll Behavior
 * Changes navbar background to plum and text to cream when scrolled
 */
function initNavbarScroll() {
    const nav = document.getElementById('main-nav');
    if (!nav) return;
    
    const scrollThreshold = 80;
    
    function updateNavbar() {
        if (window.scrollY > scrollThreshold) {
            nav.classList.add('nav-scrolled');
        } else {
            nav.classList.remove('nav-scrolled');
        }
    }
    
    // Check on load
    updateNavbar();
    
    // Check on scroll
    window.addEventListener('scroll', updateNavbar, { passive: true });
}

/**
 * Mobile Menu Toggle
 * Simple show/hide with hamburger animation
 */
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (!mobileMenuBtn || !mobileMenu) return;
    
    mobileMenuBtn.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('hidden');
        mobileMenuBtn.classList.toggle('active');
        mobileMenuBtn.setAttribute('aria-expanded', !isOpen);
    });
    
    // Close mobile menu when clicking on a link
    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            mobileMenuBtn.classList.remove('active');
            mobileMenuBtn.setAttribute('aria-expanded', 'false');
        });
    });
}

/**
 * Mobile Accordion Submenus
 * Toggle visibility of submenu items
 */
function initMobileAccordions() {
    const accordionBtns = document.querySelectorAll('.mobile-accordion-btn');
    
    accordionBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            
            const targetId = btn.getAttribute('data-target');
            const targetMenu = document.getElementById(targetId);
            const chevron = btn.querySelector('svg');
            
            if (!targetMenu) return;
            
            // Toggle the submenu
            targetMenu.classList.toggle('hidden');
            
            // Rotate the chevron
            if (chevron) {
                chevron.classList.toggle('rotate-180');
            }
        });
    });
}

/**
 * Smooth Anchor Scroll
 * Smoothly scrolls to anchor targets with navbar offset
 */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    const navbarHeight = 80;
    
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            
            // Skip if it's just "#"
            if (href === '#') return;
            
            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = targetPosition - navbarHeight;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: prefersReducedMotion ? 'auto' : 'smooth'
                });
                
                // Update URL hash
                if (history.pushState) {
                    history.pushState(null, null, href);
                }
            }
        });
    });
}

// WhatsApp function removed - contact via email only

/**
 * Flash Message Auto-Dismiss
 * Automatically hides flash messages and toasts after delay
 */
function initFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message, .toast');
    
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.classList.add('fade-out');
            
            // Remove from DOM after animation
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 4000);
    });
}

/**
 * Initialize all functions when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    initFadeInUp();
    initNavbarScroll();
    initMobileMenu();
    initMobileAccordions();
    initSmoothScroll();
    initFlashMessages();
    initVideoAutoplay();
    initVideoLightbox();
});

/**
 * Re-initialize fade-in animations for dynamically loaded content
 * Call this function after loading new content via AJAX
 */
window.reinitAnimations = function() {
    initFadeInUp();
};

/**
 * Video Scroll Autoplay
 * Autoplays muted videos when scrolled into viewport (TikTok/Instagram style)
 */
function initVideoAutoplay() {
    const videoCards = document.querySelectorAll('.video-autoplay-card');
    
    if (videoCards.length === 0) return;
    
    // IntersectionObserver to detect when videos enter/exit viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;
            
            if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                // Video is 60%+ visible - lazy load and play
                if (!video.src && video.dataset.src) {
                    video.src = video.dataset.src;
                }
                
                // Attempt to play (will fail silently if browser blocks autoplay)
                const playPromise = video.play();
                
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        // Video started playing successfully - track view (once per session)
                        const postId = video.dataset.postId;
                        if (postId) {
                            trackVideoView(postId);
                        }
                    }).catch(() => {
                        // Autoplay blocked - that's fine, poster will show until user interaction
                    });
                }
            } else {
                // Video exited viewport - pause it
                if (!video.paused) {
                    video.pause();
                }
            }
        });
    }, {
        threshold: 0.6,
        rootMargin: '0px'
    });
    
    // Observe all video cards
    videoCards.forEach(video => observer.observe(video));
}

/**
 * Track Video View
 * Increments video_views count once per session per video
 */
function trackVideoView(postId) {
    const storageKey = `viewed_video_${postId}`;
    
    // Check if already viewed this session
    if (sessionStorage.getItem(storageKey)) {
        return; // Already counted
    }
    
    // Fire and forget - don't block playback
    fetch('/api/track-view.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ post_id: parseInt(postId) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mark as viewed in this session
            sessionStorage.setItem(storageKey, '1');
        }
    })
    .catch(() => {
        // Silent fail - don't disrupt user experience
    });
}

/**
 * Video Lightbox
 * Opens full-screen video player with controls and sound on click
 */
function initVideoLightbox() {
    const videoLinks = document.querySelectorAll('.video-card-link');
    
    if (videoLinks.length === 0) return;
    
    // Create lightbox HTML (only once)
    const lightboxHTML = `
        <div id="video-lightbox" class="fixed inset-0 z-[9999] hidden">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black bg-opacity-95" id="video-lightbox-backdrop"></div>
            
            <!-- Close button -->
            <button 
                type="button" 
                id="video-lightbox-close" 
                class="absolute top-4 right-4 z-10 text-white hover:text-gold transition-colors p-2"
                aria-label="Close video"
            >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <!-- Video container -->
            <div class="relative w-full h-full flex items-center justify-center p-4 md:p-8">
                <video 
                    id="video-lightbox-player" 
                    class="max-w-full max-h-full rounded-lg shadow-2xl"
                    controls
                    autoplay
                    playsinline
                >
                </video>
            </div>
        </div>
    `;
    
    // Append lightbox to body if it doesn't exist
    if (!document.getElementById('video-lightbox')) {
        document.body.insertAdjacentHTML('beforeend', lightboxHTML);
    }
    
    const lightbox = document.getElementById('video-lightbox');
    const lightboxPlayer = document.getElementById('video-lightbox-player');
    const lightboxClose = document.getElementById('video-lightbox-close');
    const lightboxBackdrop = document.getElementById('video-lightbox-backdrop');
    
    let currentFeedVideo = null; // Track the in-feed video currently in lightbox
    
    // Open lightbox on video card click
    videoLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            const videoSrc = link.dataset.videoSrc;
            if (!videoSrc) return;
            
            // Find the feed video element to pause it
            currentFeedVideo = link.querySelector('.video-autoplay-card');
            if (currentFeedVideo && !currentFeedVideo.paused) {
                currentFeedVideo.pause();
            }
            
            // Set video source and open lightbox
            lightboxPlayer.src = videoSrc;
            lightbox.classList.remove('hidden');
            
            // Disable body scroll
            document.body.style.overflow = 'hidden';
            
            // Play with sound (user interaction has occurred)
            lightboxPlayer.muted = false;
            lightboxPlayer.volume = 0.8;
            lightboxPlayer.play();
        });
    });
    
    // Close lightbox function
    function closeLightbox() {
        lightbox.classList.add('hidden');
        lightboxPlayer.pause();
        lightboxPlayer.src = '';
        currentFeedVideo = null;
        
        // Re-enable body scroll
        document.body.style.overflow = '';
    }
    
    // Close on close button click
    lightboxClose.addEventListener('click', closeLightbox);
    
    // Close on backdrop click
    lightboxBackdrop.addEventListener('click', closeLightbox);
    
    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !lightbox.classList.contains('hidden')) {
            closeLightbox();
        }
    });
}

/**
 * Video Like Button Handler
 * One-time per session like functionality with optimistic UI updates
 */
function initVideoLikeButtons() {
    const likeButtons = document.querySelectorAll('.video-like-btn');
    
    if (likeButtons.length === 0) return;
    
    // On page load, check sessionStorage and update UI for already-liked videos
    likeButtons.forEach(button => {
        const postId = button.dataset.postId;
        const storageKey = `liked_video_${postId}`;
        
        if (sessionStorage.getItem(storageKey)) {
            setLikedState(button);
        }
    });
    
    // Handle like button clicks
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const postId = this.dataset.postId;
            const storageKey = `liked_video_${postId}`;
            
            // Check if already liked this session
            if (sessionStorage.getItem(storageKey)) {
                return; // Already liked - button should be disabled
            }
            
            // Get current count
            const likeCountEl = this.querySelector('.like-count');
            const currentCount = parseInt(this.dataset.likes) || 0;
            const newCount = currentCount + 1;
            
            // Optimistic UI update
            likeCountEl.textContent = newCount;
            this.dataset.likes = newCount;
            setLikedState(this);
            
            // Send like to server
            fetch('/api/track-like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ post_id: parseInt(postId) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Server confirmed - mark as liked in session
                    sessionStorage.setItem(storageKey, '1');
                    
                    // Update count with server's authoritative count (in case of race conditions)
                    likeCountEl.textContent = data.likes;
                    this.dataset.likes = data.likes;
                } else {
                    // Server rejected - revert optimistic update
                    likeCountEl.textContent = currentCount;
                    this.dataset.likes = currentCount;
                    revertLikedState(this);
                    
                    console.error('Like failed:', data.error);
                }
            })
            .catch(error => {
                // Network error - revert optimistic update and allow retry
                likeCountEl.textContent = currentCount;
                this.dataset.likes = currentCount;
                revertLikedState(this);
                
                console.error('Like request failed:', error);
            });
        });
    });
}

/**
 * Set button to "liked" visual state
 */
function setLikedState(button) {
    const likeIcon = button.querySelector('.like-icon');
    const likeText = button.querySelector('.like-text');
    
    // Change to filled heart
    likeIcon.textContent = '♥';
    
    // Update text
    if (likeText) {
        likeText.textContent = 'Blessed!';
    }
    
    // Update styles
    button.style.borderColor = '#4A1942';
    button.style.background = 'rgba(74,25,66,0.1)';
    button.style.color = '#4A1942';
    button.style.cursor = 'default';
    button.style.opacity = '0.8';
    
    // Disable button
    button.disabled = true;
}

/**
 * Revert button to unlicked state (for failed requests)
 */
function revertLikedState(button) {
    const likeIcon = button.querySelector('.like-icon');
    const likeText = button.querySelector('.like-text');
    
    // Change back to outline heart
    likeIcon.textContent = '♡';
    
    // Revert text based on button size (carousel uses shorter text)
    if (likeText) {
        const originalText = likeText.textContent === 'Blessed!' ? 
            (button.classList.contains('text-xs') && button.querySelector('.like-text').textContent.length < 10 ? 'Bless' : 'Did this bless you?') 
            : likeText.textContent;
        likeText.textContent = originalText.includes('Bless') ? (originalText === 'Bless' ? 'Bless' : 'Did this bless you?') : likeText.textContent;
    }
    
    // Revert styles
    button.style.borderColor = '#D4A017';
    button.style.background = 'rgba(212,160,23,0.05)';
    button.style.color = '#4A1942';
    button.style.cursor = 'pointer';
    button.style.opacity = '1';
    
    // Re-enable button
    button.disabled = false;
}

// Initialize like buttons when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initVideoLikeButtons();
    initCarouselIndicators();
});

/**
 * Carousel Scroll Position Indicators (Mobile Only)
 * Shows dots below carousel to indicate scroll position
 */
function initCarouselIndicators() {
    const carousel = document.getElementById('latest-carousel');
    const indicatorContainer = document.getElementById('carousel-indicators');
    const carouselTrack = document.getElementById('carousel-track');
    
    if (!carousel || !indicatorContainer || !carouselTrack) return;
    
    // Count carousel items (cards)
    const cards = carouselTrack.querySelectorAll('.snap-center');
    const cardCount = cards.length;
    
    if (cardCount === 0) return;
    
    // Create dots
    for (let i = 0; i < cardCount; i++) {
        const dot = document.createElement('div');
        dot.className = 'w-2 h-2 rounded-full bg-plum transition-all duration-300';
        dot.style.opacity = i === 0 ? '1' : '0.3';
        dot.dataset.index = i;
        indicatorContainer.appendChild(dot);
    }
    
    const dots = indicatorContainer.querySelectorAll('div');
    
    // Update active dot on scroll
    carousel.addEventListener('scroll', () => {
        // Calculate which card is most visible
        const scrollLeft = carousel.scrollLeft;
        const carouselWidth = carousel.offsetWidth;
        
        // Find the card that's most centered in view
        let activeIndex = 0;
        let minDistance = Infinity;
        
        cards.forEach((card, index) => {
            const cardLeft = card.offsetLeft - carousel.offsetLeft;
            const cardCenter = cardLeft + (card.offsetWidth / 2);
            const viewportCenter = scrollLeft + (carouselWidth / 2);
            const distance = Math.abs(cardCenter - viewportCenter);
            
            if (distance < minDistance) {
                minDistance = distance;
                activeIndex = index;
            }
        });
        
        // Update dot states
        dots.forEach((dot, index) => {
            if (index === activeIndex) {
                dot.style.opacity = '1';
                dot.style.transform = 'scale(1.2)';
            } else {
                dot.style.opacity = '0.3';
                dot.style.transform = 'scale(1)';
            }
        });
    }, { passive: true });
    
    // Optional: Make dots clickable to jump to card
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            const targetCard = cards[index];
            if (targetCard) {
                targetCard.scrollIntoView({ 
                    behavior: prefersReducedMotion ? 'auto' : 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        });
        dot.style.cursor = 'pointer';
    });
}

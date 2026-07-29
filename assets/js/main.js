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
});

/**
 * Re-initialize fade-in animations for dynamically loaded content
 * Call this function after loading new content via AJAX
 */
window.reinitAnimations = function() {
    initFadeInUp();
};

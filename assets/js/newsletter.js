/**
 * Newsletter Signup Form Handler
 * Naomi Wendot Writer & Ministry Website
 */

document.addEventListener('DOMContentLoaded', () => {
    // Find all newsletter forms on the page
    const newsletterForms = document.querySelectorAll('[data-newsletter-form], #newsletter-form');
    
    if (newsletterForms.length === 0) return;
    
    newsletterForms.forEach(form => {
        form.addEventListener('submit', handleNewsletterSubmit);
    });
});

/**
 * Handle newsletter form submission
 * 
 * @param {Event} e Submit event
 */
function handleNewsletterSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const emailInput = form.querySelector('input[type="email"]');
    const email = emailInput ? emailInput.value.trim() : '';
    
    // Basic email validation
    if (!isValidEmail(email)) {
        showNewsletterMessage(form, 'Please enter a valid email address.', 'error');
        return;
    }
    
    // TODO: Replace with actual backend API call
    // For now, show success message immediately
    
    // Hide the form
    form.style.display = 'none';
    
    // Show success message
    showNewsletterSuccess(form);
}

/**
 * Validate email format
 * 
 * @param {string} email Email address to validate
 * @return {boolean} True if valid
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Show newsletter success message
 * 
 * @param {HTMLElement} form Form element
 */
function showNewsletterSuccess(form) {
    // Check if success element already exists
    let successEl = form.nextElementSibling;
    if (!successEl || !successEl.id || !successEl.id.includes('success')) {
        successEl = document.getElementById('newsletter-success');
    }
    
    if (successEl) {
        successEl.classList.remove('hidden');
        successEl.style.display = 'block';
    } else {
        // Create success message dynamically
        const successDiv = document.createElement('div');
        successDiv.className = 'mt-6 text-center';
        successDiv.innerHTML = `
            <div class="inline-flex items-center gap-2 text-forest">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-semibold">Thank you — you're on the list!</span>
            </div>
        `;
        form.parentNode.insertBefore(successDiv, form.nextSibling);
    }
}

/**
 * Show error or info message
 * 
 * @param {HTMLElement} form Form element
 * @param {string} message Message to display
 * @param {string} type Message type (error, info)
 */
function showNewsletterMessage(form, message, type = 'info') {
    // Remove existing messages
    const existingMessages = form.querySelectorAll('.newsletter-message');
    existingMessages.forEach(msg => msg.remove());
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `newsletter-message mt-3 text-sm ${type === 'error' ? 'text-red-600' : 'text-forest'}`;
    messageDiv.textContent = message;
    
    form.appendChild(messageDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

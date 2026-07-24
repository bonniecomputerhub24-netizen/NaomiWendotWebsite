<?php
/**
 * Newsletter Signup Form Component
 * Reusable newsletter form partial
 * 
 * @var string $context Optional context identifier (e.g., 'homepage', 'footer', 'nature-book')
 */

$context = $context ?? 'default';
?>
<form data-newsletter-form class="newsletter-form w-full" data-context="<?php echo e($context); ?>">
    <div class="flex flex-col md:flex-row gap-3 max-w-md mx-auto w-full">
        <input 
            type="email" 
            name="email" 
            placeholder="Your email address" 
            required
            class="flex-1 px-5 py-3 rounded-full border focus:outline-none focus:ring-2 w-full"
            style="background-color: white; border-color: #E5E7EB; font-family: 'Inter', sans-serif; max-width: 100%;"
            onfocus="this.style.borderColor='#D4A017'; this.style.boxShadow='0 0 0 3px rgba(212, 160, 23, 0.1)'"
            onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none'"
        >
        <button 
            type="submit" 
            class="px-6 py-3 rounded-full font-bold transition-all duration-300 whitespace-nowrap flex-shrink-0"
            style="background-color: #D4A017; color: #4A1942; font-family: 'Inter', sans-serif;"
            onmouseover="this.style.opacity='0.9'"
            onmouseout="this.style.opacity='1'"
        >
            Subscribe →
        </button>
    </div>
    
    <p class="text-sm italic mt-4 text-center" style="opacity: 0.8; font-family: 'Inter', sans-serif;">
        No spam. Just words that matter.
    </p>
</form>

<!-- Success Message (hidden initially) -->
<div id="newsletter-success-<?php echo e($context); ?>" class="hidden mt-6 text-center">
    <div class="inline-flex items-center gap-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D4A017;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-semibold" style="font-family: 'Inter', sans-serif;">Thank you — you're on the list!</span>
    </div>
</div>

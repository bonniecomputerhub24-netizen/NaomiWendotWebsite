<?php
/**
 * Newsletter Signup Form Component
 * Reusable newsletter form partial
 * 
 * @var string $context Optional context identifier (e.g., 'homepage', 'footer', 'nature-book')
 */

$context = $context ?? 'default';
$formId = 'newsletter-form-' . $context;
$successId = 'newsletter-success-' . $context;
?>
<form class="newsletter-form w-full" data-context="<?php echo e($context); ?>" id="<?php echo $formId; ?>">
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
        <!-- Honeypot for spam prevention -->
        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
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
    
    <p class="text-sm italic mt-4 text-center newsletter-note" style="opacity: 0.8; font-family: 'Inter', sans-serif;">
        No spam. Just words that matter.
    </p>
</form>

<!-- Success Message (hidden initially) -->
<div id="<?php echo $successId; ?>" class="hidden mt-6 text-center newsletter-success">
    <div class="inline-flex items-center gap-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D4A017;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-semibold" style="font-family: 'Inter', sans-serif;">Thank you — you're on the list!</span>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('<?php echo $formId; ?>');
    const successMsg = document.getElementById('<?php echo $successId; ?>');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            formData.append('context', '<?php echo $context; ?>');
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            
            try {
                const response = await fetch('<?php echo basePath(); ?>api/newsletter.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    form.style.display = 'none';
                    form.parentElement.querySelector('.newsletter-note').style.display = 'none';
                    successMsg.classList.remove('hidden');
                } else {
                    alert(data.message || 'An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
})();
</script>

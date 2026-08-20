<?php
/**
 * Testimonial Submission Form Component
 * Can be placed anywhere on the site
 */

$formContext = $formContext ?? 'default';
$formId = 'testimonial-form-' . $formContext;
$successId = 'testimonial-success-' . $formContext;
?>

<div class="testimonial-form-wrapper">
    <form id="<?php echo $formId; ?>" class="testimonial-form space-y-4">
        <!-- Name -->
        <div>
            <label for="testimony-name-<?php echo $formContext; ?>" class="block text-sm font-semibold text-plum mb-1">
                Your Name <span class="text-gold">*</span>
            </label>
            <input 
                type="text" 
                id="testimony-name-<?php echo $formContext; ?>"
                name="name" 
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                placeholder="Enter your name"
            >
        </div>
        
        <!-- Email (Optional) -->
        <div>
            <label for="testimony-email-<?php echo $formContext; ?>" class="block text-sm font-semibold text-plum mb-1">
                Your Email <span class="text-gray-400 text-xs">(Optional)</span>
            </label>
            <input 
                type="email" 
                id="testimony-email-<?php echo $formContext; ?>"
                name="email"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                placeholder="your.email@example.com"
            >
        </div>
        
        <!-- Location (Optional) -->
        <div>
            <label for="testimony-location-<?php echo $formContext; ?>" class="block text-sm font-semibold text-plum mb-1">
                Your Location <span class="text-gray-400 text-xs">(Optional)</span>
            </label>
            <input 
                type="text" 
                id="testimony-location-<?php echo $formContext; ?>"
                name="location"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                placeholder="e.g., Nairobi, Kenya"
            >
        </div>
        
        <!-- Testimony -->
        <div>
            <label for="testimony-text-<?php echo $formContext; ?>" class="block text-sm font-semibold text-plum mb-1">
                Your Testimony <span class="text-gold">*</span>
            </label>
            <textarea 
                id="testimony-text-<?php echo $formContext; ?>"
                name="testimony" 
                rows="5" 
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent resize-none"
                placeholder="Share how Naomi's writing has impacted your faith journey..."
            ></textarea>
        </div>
        
        <!-- Honeypot -->
        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
        
        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full bg-plum text-cream py-4 rounded-xl font-bold font-inter hover:bg-opacity-90 transition-all duration-300"
        >
            Submit Testimony
        </button>
        
        <p class="text-plum text-xs italic text-center">
            Your testimony will be reviewed before being published on the site.
        </p>
    </form>
    
    <!-- Success Message -->
    <div id="<?php echo $successId; ?>" class="hidden text-center py-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gold bg-opacity-20 rounded-full mb-4">
            <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h3 class="text-plum font-bold text-xl mb-2 font-playfair">Thank You!</h3>
        <p class="text-gray-700">Your testimony has been submitted and will be reviewed soon.</p>
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
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin w-6 h-6 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            
            try {
                const response = await fetch('<?php echo basePath(); ?>api/testimonial.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    form.style.display = 'none';
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

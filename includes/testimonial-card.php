<?php
/**
 * Testimonial Card Component
 * Reusable testimonial card for reader testimonials
 * 
 * @var string $quote The testimonial quote text
 * @var string $name The person's name
 * @var string $location The person's location
 */

// Ensure required variables are set
if (!isset($quote) || !isset($name)) {
    return;
}

$location = $location ?? '';
?>
<div class="rounded-2xl shadow-sm p-8 fade-in-up hover-lift" style="background-color: #FDEAEA; border-left: 4px solid #D4A017;">
    <!-- Decorative Quote Icon -->
    <div class="text-4xl mb-4 font-serif leading-none" style="color: #D4A017; opacity: 0.3;">"</div>
    
    <!-- Quote Text -->
    <p class="italic text-base leading-relaxed" style="color: #4A1942; font-family: 'Inter', sans-serif;">
        <?php echo e($quote); ?>
    </p>
    
    <!-- Attribution -->
    <div class="mt-4">
        <p class="font-bold" style="color: #4A1942; font-family: 'Inter', sans-serif;"><?php echo e($name); ?></p>
        <?php if (!empty($location)): ?>
            <p class="text-sm" style="color: #D4A017; font-family: 'Inter', sans-serif;"><?php echo e($location); ?></p>
        <?php endif; ?>
    </div>
</div>

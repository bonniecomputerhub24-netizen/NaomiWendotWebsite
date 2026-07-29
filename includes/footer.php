<?php
/**
 * Footer Component
 * Naomi Wendot Writer & Ministry Website
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$base = basePath();
?>
<footer style="background-color: #4A1942; color: #FFFDF5;">
    <div class="max-w-7xl mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Column 1: Brand & Contact -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span class="font-playfair italic text-xl font-semibold"><?php echo e(SITE_NAME); ?></span>
                </div>
                
                <p class="italic text-sm mb-3" style="color: #D4A017; opacity: 0.9; font-family: 'Inter', sans-serif;">
                    <?php echo e(SITE_TAGLINE); ?>
                </p>
                
                <p class="text-sm md:text-base leading-relaxed mb-4" style="color: #FFFDF5; opacity: 0.8; font-family: 'Inter', sans-serif;">
                    I'm a Kenyan-born poet and writer, sharing words rooted in Scripture, offered freely to encourage hearts of all ages.
                </p>
                
                <div class="flex flex-col gap-2">
                    <a href="mailto:<?php echo e(SITE_EMAIL); ?>" class="flex items-center gap-2 transition-colors" style="color: #FFFDF5; font-family: 'Inter', sans-serif;" onmouseover="this.style.color='#D4A017'" onmouseout="this.style.color='#FFFDF5'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm" style="font-family: 'Inter', sans-serif;"><?php echo e(SITE_EMAIL); ?></span>
                    </a>
                </div>
            </div>
            
            <!-- Column 2: Explore -->
            <div>
                <h3 class="font-playfair font-semibold text-lg mb-4" style="color: #D4A017;">Explore</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?php echo $base; ?>public/" class="transition-colors text-sm" style="color: #FFFDF5; font-family: 'Inter', sans-serif;" onmouseover="this.style.color='#D4A017'" onmouseout="this.style.color='#FFFDF5'">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base; ?>public/about" class="transition-colors text-sm" style="color: #FFFDF5; font-family: 'Inter', sans-serif;" onmouseover="this.style.color='#D4A017'" onmouseout="this.style.color='#FFFDF5'">
                            About
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base; ?>public/body-of-work" class="transition-colors text-sm" style="color: #FFFDF5; font-family: 'Inter', sans-serif;" onmouseover="this.style.color='#D4A017'" onmouseout="this.style.color='#FFFDF5'">
                            Body of Work
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base; ?>nature-bible-verses/" class="transition-colors text-sm" style="color: #FFFDF5; font-family: 'Inter', sans-serif;" onmouseover="this.style.color='#D4A017'" onmouseout="this.style.color='#FFFDF5'">
                            Nature & Bible Verses
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base; ?>public/contact" class="transition-colors text-sm" style="color: #FFFDF5; font-family: 'Inter', sans-serif;" onmouseover="this.style.color='#D4A017'" onmouseout="this.style.color='#FFFDF5'">
                            Contact
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Column 3: Connect -->
            <div>
                <h3 class="font-playfair font-semibold text-lg mb-4" style="color: #D4A017;">Connect</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="mailto:<?php echo e(SITE_EMAIL); ?>" class="transition-colors text-sm" style="color: #FFFDF5; font-family: 'Inter', sans-serif;" onmouseover="this.style.color='#D4A017'" onmouseout="this.style.color='#FFFDF5'">
                            <?php echo e(SITE_EMAIL); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base; ?>public/#newsletter" class="transition-colors text-sm font-semibold" style="color: #D4A017; font-family: 'Inter', sans-serif;" onmouseover="this.style.color='#FFFDF5'" onmouseout="this.style.color='#D4A017'">
                            Subscribe to Inspirations →
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Divider -->
        <div class="mt-12 pt-8" style="border-top: 1px solid rgba(212, 160, 23, 0.3);">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
                <p style="color: #D4A017; opacity: 0.8; font-family: 'Inter', sans-serif;">
                    © <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. All rights reserved.
                </p>
                <p class="text-xs" style="color: #9CA3AF; font-family: 'Inter', sans-serif;">
                    Developed and maintained by <a href="https://bonniecomputerhub.co.ke" target="_blank" rel="noopener noreferrer" class="transition-colors" style="color: #D4A017; font-weight: 600;" onmouseover="this.style.color='#FFFDF5'" onmouseout="this.style.color='#D4A017'">Bonnie Computer Hub</a>
                </p>
            </div>
        </div>
    </div>
</footer>

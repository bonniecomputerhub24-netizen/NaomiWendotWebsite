<?php
/**
 * Main Navigation Component
 * Naomi Wendot Writer & Ministry Website
 * Benchmarked against BCH for seamless dropdown experience
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$base = basePath();
?>
<nav id="main-nav" class="fixed top-0 left-0 right-0 z-50 bg-cream bg-opacity-95 backdrop-blur-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-20">
            <!-- Logo / Brand -->
            <a href="<?php echo $base; ?>public/index.php" class="flex items-center gap-2 group">
                <svg class="w-8 h-8 text-gold transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="brand-name font-playfair italic text-xl font-semibold text-plum"><?php echo e(SITE_NAME); ?></span>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-8">
                <!-- Home -->
                <a 
                    href="<?php echo $base; ?>public/index.php" 
                    class="nav-link text-plum hover:text-gold transition-colors duration-200 font-medium relative group font-inter"
                >
                    Home
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold transition-all duration-200 group-hover:w-full"></span>
                </a>
                
                <!-- About (Dropdown) -->
                <div class="relative group">
                    <button 
                        class="nav-link flex items-center gap-1 text-plum hover:text-gold transition-colors duration-200 font-medium font-inter"
                    >
                        About
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border-t-2 border-gold opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2">
                        <a href="<?php echo $base; ?>public/about.php#who-she-is" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Who She Is</a>
                        <a href="<?php echo $base; ?>public/about.php#her-journey" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Her Journey</a>
                    </div>
                </div>
                
                <!-- Body of Work (Dropdown) -->
                <div class="relative group">
                    <button 
                        class="nav-link flex items-center gap-1 text-plum hover:text-gold transition-colors duration-200 font-medium font-inter"
                    >
                        Body of Work
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute left-0 mt-2 w-52 bg-white rounded-xl shadow-xl border-t-2 border-gold opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2">
                        <a href="<?php echo $base; ?>public/body-of-work.php#poems" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Poems</a>
                        <a href="<?php echo $base; ?>public/body-of-work.php#articles" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Articles</a>
                        <a href="<?php echo $base; ?>public/body-of-work.php#daily-inspirations" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Daily Inspirations</a>
                        <a href="<?php echo $base; ?>public/body-of-work.php#stories" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Stories</a>
                        <a href="<?php echo $base; ?>public/body-of-work.php#testimonies" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Testimonies</a>
                    </div>
                </div>
                
                <!-- Nature & Bible Verses (Elevated) -->
                <a 
                    href="<?php echo $base; ?>nature-bible-verses/index.php" 
                    class="px-4 py-2 rounded-full border-2 border-gold text-gold hover:bg-gold hover:text-plum transition-all duration-200 font-medium font-inter text-sm"
                >
                    Nature & Bible Verses
                </a>
                
                <!-- Contact -->
                <a 
                    href="<?php echo $base; ?>public/contact.php" 
                    class="nav-link text-plum hover:text-gold transition-colors duration-200 font-medium relative group font-inter"
                >
                    Contact
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold transition-all duration-200 group-hover:w-full"></span>
                </a>
            </div>
            
            <!-- Subscribe Button (Desktop) -->
            <a 
                href="<?php echo $base; ?>public/index.php#newsletter" 
                class="hidden md:inline-block px-6 py-2 rounded-full border-2 border-gold text-gold hover:bg-gold hover:text-plum font-semibold transition-all duration-200 font-inter"
            >
                Subscribe
            </a>
            
            <!-- Mobile Hamburger Button -->
            <button 
                id="mobile-menu-btn" 
                class="md:hidden flex flex-col gap-1.5 p-2"
                aria-label="Toggle menu"
                aria-expanded="false"
            >
                <span class="hamburger-line block w-6 h-0.5 bg-plum transition-all"></span>
                <span class="hamburger-line block w-6 h-0.5 bg-plum transition-all"></span>
                <span class="hamburger-line block w-6 h-0.5 bg-plum transition-all"></span>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-plum">
        <div class="py-4 space-y-1">
            <!-- Home -->
            <a 
                href="<?php echo $base; ?>public/index.php" 
                class="block px-4 py-3 text-cream hover:bg-gold hover:text-plum transition-colors font-inter rounded mx-2"
            >
                Home
            </a>
            
            <!-- About Accordion -->
            <div>
                <button 
                    class="mobile-accordion-btn w-full text-left px-4 py-3 text-cream hover:bg-gold hover:text-plum transition-colors font-inter rounded mx-2 flex items-center justify-between"
                    data-target="about-submenu"
                >
                    <span>About</span>
                    <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="about-submenu" class="hidden pl-8 space-y-1 mt-1">
                    <a href="<?php echo $base; ?>public/about.php#who-she-is" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Who She Is</a>
                    <a href="<?php echo $base; ?>public/about.php#her-journey" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Her Journey</a>
                </div>
            </div>
            
            <!-- Body of Work Accordion -->
            <div>
                <button 
                    class="mobile-accordion-btn w-full text-left px-4 py-3 text-cream hover:bg-gold hover:text-plum transition-colors font-inter rounded mx-2 flex items-center justify-between"
                    data-target="work-submenu"
                >
                    <span>Body of Work</span>
                    <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="work-submenu" class="hidden pl-8 space-y-1 mt-1">
                    <a href="<?php echo $base; ?>public/body-of-work.php#poems" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Poems</a>
                    <a href="<?php echo $base; ?>public/body-of-work.php#articles" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Articles</a>
                    <a href="<?php echo $base; ?>public/body-of-work.php#daily-inspirations" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Daily Inspirations</a>
                    <a href="<?php echo $base; ?>public/body-of-work.php#stories" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Stories</a>
                    <a href="<?php echo $base; ?>public/body-of-work.php#testimonies" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Testimonies</a>
                </div>
            </div>
            
            <!-- Nature & Bible Verses (Elevated in Mobile) -->
            <a 
                href="<?php echo $base; ?>nature-bible-verses/index.php" 
                class="block mx-4 my-3 px-4 py-2 text-center rounded-full border-2 border-gold text-gold transition-all font-medium font-inter hover:bg-gold hover:text-plum"
            >
                Nature & Bible Verses
            </a>
            
            <!-- Contact -->
            <a 
                href="<?php echo $base; ?>public/contact.php" 
                class="block px-4 py-3 text-cream hover:bg-gold hover:text-plum transition-colors font-inter rounded mx-2"
            >
                Contact
            </a>
            
            <!-- Subscribe -->
            <a 
                href="<?php echo $base; ?>public/index.php#newsletter" 
                class="block mx-4 mt-4 px-6 py-3 text-center rounded-full font-semibold transition-all border-2 border-gold text-gold font-inter hover:bg-gold hover:text-plum"
            >
                Subscribe
            </a>
        </div>
    </div>
</nav>

<!-- Spacer to prevent content from hiding under fixed nav -->
<div class="h-20"></div>

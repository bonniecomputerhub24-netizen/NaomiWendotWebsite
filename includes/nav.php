<?php
/**
 * Main Navigation Component
 * Naomi Wendot Writer & Ministry Website
 * Benchmarked against BCH for seamless dropdown experience
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$base = basePath();

// Fetch categories from database (with caching to reduce queries)
$navCategories = [];
$cacheKey = 'nav_categories';
$cacheFile = sys_get_temp_dir() . '/' . $cacheKey . '.json';
$cacheTime = 300; // 5 minutes

// Try to get from cache first
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    $navCategories = json_decode(file_get_contents($cacheFile), true);
} else {
    // Fetch from database
    try {
        $db = getDb();
        $stmt = $db->query("
            SELECT DISTINCT c.id, c.name, c.slug 
            FROM categories c 
            INNER JOIN posts p ON c.id = p.category_id 
            WHERE p.status = 'published'
            ORDER BY c.display_order ASC, c.name ASC
            LIMIT 10
        ");
        $navCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache the result
        @file_put_contents($cacheFile, json_encode($navCategories));
    } catch (Exception $e) {
        // Fallback to predefined categories if DB fails
        error_log("Nav categories fetch error: " . $e->getMessage());
        $navCategories = [
            ['slug' => 'poems', 'name' => 'Poems'],
            ['slug' => 'articles', 'name' => 'Articles'],
            ['slug' => 'daily-inspirations', 'name' => 'Daily Inspirations'],
            ['slug' => 'stories', 'name' => 'Stories'],
            ['slug' => 'testimonies', 'name' => 'Testimonies']
        ];
    }
}
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
                        <a href="<?php echo $base; ?>public/body-of-work.php" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm font-semibold border-b border-gray-100">All Writing</a>
                        <a href="<?php echo $base; ?>body-of-work/poems.php" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Poems</a>
                        <a href="<?php echo $base; ?>body-of-work/articles.php" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Articles</a>
                        <a href="<?php echo $base; ?>body-of-work/daily-inspirations.php" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Daily Inspirations</a>
                        <a href="<?php echo $base; ?>body-of-work/stories.php" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Stories</a>
                        <a href="<?php echo $base; ?>body-of-work/testimonies.php" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Testimonies</a>
                        <a href="<?php echo $base; ?>body-of-work/videos.php" class="block px-4 py-2 text-plum hover:bg-rose hover:text-gold transition-colors font-inter text-sm">Videos</a>
                    </div>
                </div>
                
                <!-- Shop (Elevated) -->
                <a 
                    href="<?php echo $base; ?>shop/index.php" 
                    class="px-4 py-2 rounded-full border-2 border-gold text-gold hover:bg-gold hover:text-plum transition-all duration-200 font-medium font-inter text-sm"
                >
                    Shop
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
            
            <!-- Action Buttons (Desktop) -->
            <div class="hidden md:flex items-center gap-3">
                <!-- Cart Icon with Badge -->
                <a 
                    href="<?php echo $base; ?>shop/cart.php" 
                    class="relative p-2 text-plum hover:text-gold transition-all duration-200 group"
                    title="Shopping Cart"
                >
                    <svg class="w-7 h-7 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span id="cart-badge-desktop" class="hidden absolute -top-1 -right-1 bg-gold text-plum text-xs font-bold rounded-full min-w-[22px] h-[22px] px-1.5 flex items-center justify-center font-montserrat shadow-lg border-2 border-cream ring-2 ring-white">0</span>
                </a>
                
                <a 
                    href="<?php echo $base; ?>public/index.php#newsletter" 
                    class="px-6 py-2 rounded-full border-2 border-gold text-gold hover:bg-gold hover:text-plum font-semibold transition-all duration-200 font-inter"
                >
                    Subscribe
                </a>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                    <a 
                        href="<?php echo $base; ?>admin/dashboard.php" 
                        class="px-5 py-2 rounded-full bg-plum text-cream hover:bg-opacity-90 font-medium transition-all duration-200 font-inter flex items-center gap-2"
                        title="Admin Dashboard"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        Dashboard
                    </a>
                <?php else: ?>
                    <a 
                        href="<?php echo $base; ?>admin/login.php" 
                        class="px-5 py-2 rounded-full bg-plum text-cream hover:bg-opacity-90 font-medium transition-all duration-200 font-inter flex items-center gap-2"
                        title="Admin Login"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Login
                    </a>
                <?php endif; ?>
            </div>
            
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
            <!-- Cart (Mobile) -->
            <a 
                href="<?php echo $base; ?>shop/cart.php" 
                class="flex items-center justify-between px-4 py-3 text-cream hover:bg-gold hover:text-plum transition-colors font-inter rounded mx-2"
            >
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Cart
                </span>
                <span id="cart-badge-mobile" class="hidden bg-gold text-plum text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center font-montserrat">0</span>
            </a>
            
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
                    <a href="<?php echo $base; ?>public/body-of-work.php" class="block px-4 py-2 text-gold text-sm font-semibold hover:text-cream transition-colors font-inter">All Writing</a>
                    <a href="<?php echo $base; ?>body-of-work/poems.php" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Poems</a>
                    <a href="<?php echo $base; ?>body-of-work/articles.php" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Articles</a>
                    <a href="<?php echo $base; ?>body-of-work/daily-inspirations.php" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Daily Inspirations</a>
                    <a href="<?php echo $base; ?>body-of-work/stories.php" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Stories</a>
                    <a href="<?php echo $base; ?>body-of-work/testimonies.php" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Testimonies</a>
                    <a href="<?php echo $base; ?>body-of-work/videos.php" class="block px-4 py-2 text-cream text-sm hover:text-gold transition-colors font-inter">Videos</a>
                </div>
            </div>
            
            <!-- Shop (Elevated in Mobile) -->
            <a 
                href="<?php echo $base; ?>shop/index.php" 
                class="block mx-4 my-3 px-4 py-2 text-center rounded-full border-2 border-gold text-gold transition-all font-medium font-inter hover:bg-gold hover:text-plum"
            >
                Shop
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
            
            <!-- Login / Dashboard Toggle -->
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                <a 
                    href="<?php echo $base; ?>admin/dashboard.php" 
                    class="block mx-4 mt-2 px-6 py-3 text-center rounded-full font-medium transition-all bg-gold text-plum font-inter hover:bg-opacity-90 flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Dashboard
                </a>
            <?php else: ?>
                <a 
                    href="<?php echo $base; ?>admin/login.php" 
                    class="block mx-4 mt-2 px-6 py-3 text-center rounded-full font-medium transition-all bg-gold text-plum font-inter hover:bg-opacity-90 flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Spacer to prevent content from hiding under fixed nav -->
<div class="h-20"></div>

<script>
// Update cart badge on page load
function updateCartBadge() {
    const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
    const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    
    const badgeDesktop = document.getElementById('cart-badge-desktop');
    const badgeMobile = document.getElementById('cart-badge-mobile');
    
    if (totalItems > 0) {
        if (badgeDesktop) {
            badgeDesktop.textContent = totalItems;
            badgeDesktop.classList.remove('hidden');
        }
        if (badgeMobile) {
            badgeMobile.textContent = totalItems;
            badgeMobile.classList.remove('hidden');
        }
    } else {
        if (badgeDesktop) badgeDesktop.classList.add('hidden');
        if (badgeMobile) badgeMobile.classList.add('hidden');
    }
}

// Update on page load
document.addEventListener('DOMContentLoaded', updateCartBadge);

// Update when storage changes (from other tabs)
window.addEventListener('storage', function(e) {
    if (e.key === 'naomi_cart') {
        updateCartBadge();
    }
});
</script>

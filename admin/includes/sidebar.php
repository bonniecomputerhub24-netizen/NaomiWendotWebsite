<?php
/**
 * Admin Sidebar Component
 * Naomi Wendot Admin Panel
 */

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Get the admin base path
$adminBase = '/admin/';

function isActive($page, $dir = 'admin') {
    global $currentPage, $currentDir;
    if ($dir === 'admin') {
        return $currentPage === $page ? 'bg-gold text-gray-900 border-l-4 border-gold font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
    }
    return $currentDir === $dir ? 'bg-gold text-gray-900 border-l-4 border-gold font-semibold' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
}
?>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar fixed left-0 top-0 h-screen w-64 bg-plum shadow-lg z-50 overflow-y-auto">
    <div class="p-6">
        <!-- Brand -->
        <div class="mb-8 pb-6 border-b border-gold/30">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <div>
                    <h2 class="font-montserrat font-bold text-cream text-lg">Naomi Wendot</h2>
                    <p class="text-xs text-gold/70">Admin Panel</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="space-y-1">
            <!-- Dashboard -->
            <a href="<?php echo $adminBase; ?>dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo isActive('dashboard.php'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <!-- Content Section -->
            <div class="mt-6 mb-2">
                <p class="px-4 text-xs font-semibold text-gold/60 uppercase tracking-wider">Content</p>
            </div>
            
            <a href="<?php echo $adminBase; ?>content/manage.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo isActive('manage.php', 'content'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="font-medium">Manage Writing</span>
            </a>
            
            <!-- Engagement Section -->
            <div class="mt-6 mb-2">
                <p class="px-4 text-xs font-semibold text-gold/60 uppercase tracking-wider">Engagement</p>
            </div>
            
            <a href="<?php echo $adminBase; ?>engagement/messages.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo isActive('messages.php', 'engagement'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="font-medium">Contact Messages</span>
            </a>
            
            <a href="<?php echo $adminBase; ?>testimonies/manage.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo isActive('manage.php', 'testimonies'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                <span class="font-medium">Testimonies</span>
            </a>
            
            <a href="<?php echo $adminBase; ?>newsletter/compose.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo isActive('compose.php', 'newsletter'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span class="font-medium">Newsletter</span>
            </a>
            
            <!-- Books & Shop Section -->
            <div class="mt-6 mb-2">
                <p class="px-4 text-xs font-semibold text-gold/60 uppercase tracking-wider">Books & Shop</p>
            </div>
            
            <a href="<?php echo $adminBase; ?>books/manage.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo isActive('manage.php', 'books'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="font-medium">All Books</span>
            </a>
            
            <!-- System Section -->
            <div class="mt-6 mb-2">
                <p class="px-4 text-xs font-semibold text-gold/60 uppercase tracking-wider">System</p>
            </div>
            
            <a href="<?php echo $adminBase; ?>settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo isActive('settings.php'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="font-medium">Settings</span>
            </a>
            
            <a href="<?php echo $adminBase; ?>logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-red-300 hover:bg-red-900/20 hover:text-red-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="font-medium">Logout</span>
            </a>
        </nav>
    </div>
</aside>

<!-- Main Container -->
<div class="flex pt-16">
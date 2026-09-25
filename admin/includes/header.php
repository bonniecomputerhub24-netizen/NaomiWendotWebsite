<?php
/**
 * Admin Header Component
 * Naomi Wendot Admin Panel
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$greeting = getGreeting();
$adminName = getAdminName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' — ' : ''; ?>Admin Panel — Naomi Wendot</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        plum: '#4A1942',
                        gold: '#D4A017',
                        cream: '#FFFDF5',
                        rose: '#FDEAEA',
                        charcoal: '#1C1C1C'
                    },
                    fontFamily: {
                        montserrat: ['Montserrat', 'sans-serif'],
                        century: ['Century Gothic', 'CenturyGothic', 'AppleGothic', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Sidebar styles */
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #D4A017;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #4A1942;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Menu Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>
    
    <!-- Top Header Bar -->
    <header class="bg-white shadow-sm fixed top-0 left-0 right-0 z-30 md:left-64">
        <div class="flex items-center justify-between px-4 py-4 md:px-6">
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-plum hover:text-gold transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <!-- Greeting -->
            <div class="flex-1 md:flex-none">
                <h1 class="text-lg md:text-xl font-semibold text-plum font-montserrat">
                    <?php echo $greeting; ?>, <?php echo htmlspecialchars($adminName); ?>! 👋
                </h1>
                <p class="text-xs text-gray-500 font-century hidden md:block">
                    <?php echo date('l, F j, Y'); ?>
                </p>
            </div>
            
            <!-- Header Actions -->
            <div class="flex items-center gap-2 md:gap-4">
                <!-- View Website -->
                <a href="/public/index.php" target="_blank" class="hidden md:flex items-center gap-2 px-4 py-2 text-sm font-medium text-plum hover:text-gold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    View Site
                </a>
                
                <!-- Logout Button -->
                <a href="/admin/logout.php" class="flex items-center gap-2 px-3 md:px-4 py-2 bg-plum text-cream rounded-lg hover:bg-opacity-90 transition-all text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="hidden md:inline">Logout</span>
                </a>
            </div>
        </div>
    </header>

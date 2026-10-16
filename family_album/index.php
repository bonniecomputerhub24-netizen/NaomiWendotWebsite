<?php
/**
 * Family Album Landing Page
 * Protected access with admin approval required
 */

session_start();

// Debug path information
$configPath = __DIR__ . '/../config/db.php';
$functionsPath = __DIR__ . '/../includes/functions.php';

if (!file_exists($configPath)) {
    die("Config file not found at: " . $configPath . " (Current dir: " . __DIR__ . ")");
}

require_once $configPath;
require_once $functionsPath;

// Check if user has valid family member login OR is an admin
$hasAccess = false;
$currentMember = null;

// Check for admin access first (for admin panel)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $hasAccess = true;
}
// Check for family member access
elseif (isset($_SESSION['family_member_id'])) {
    try {
        $db = getDb();
        $stmt = $db->prepare("
            SELECT * FROM family_members 
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$_SESSION['family_member_id']]);
        $currentMember = $stmt->fetch();
        
        if ($currentMember) {
            $hasAccess = true;
        }
    } catch (PDOException $e) {
        error_log("Family Album Member Check Error: " . $e->getMessage());
    }
}

// If no access, redirect to login page
if (!$hasAccess) {
    header('Location: access');
    exit;
}

// Get photos for display
$photos = [];
try {
    $db = getDb();
    $stmt = $db->query("
        SELECT * FROM family_album_photos 
        ORDER BY display_order ASC, created_at DESC
    ");
    $photos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Family Album Photos Fetch Error: " . $e->getMessage());
}

$pageTitle = "Family Album";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> — Naomi Wendot</title>
    
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
                        century: ['Century Gothic', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Century Gothic', sans-serif;
            background: linear-gradient(135deg, #FFFDF5 0%, #FDEAEA 100%);
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .photo-card {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(74, 25, 66, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .photo-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(74, 25, 66, 0.15);
        }
        
        .photo-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .caption-text {
            font-family: 'Dancing Script', cursive;
            font-size: 1.1rem;
            line-height: 1.6;
            color: #4A1942;
        }
        
        .scripture-reference {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #D4A017;
            font-size: 0.9rem;
        }
        
        /* Custom Scrollbar Styling */
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #D4A017 #E5E7EB;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #E5E7EB;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #D4A017;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #B8900F;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <header class="bg-plum shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-cream font-montserrat flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Family Album
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-gold text-sm font-medium">Welcome back, <?php echo htmlspecialchars($currentMember['full_name']); ?>!</div>
                        <div class="text-cream/70 text-xs"><?php echo ucfirst($currentMember['role']); ?> • Last login: <?php echo $currentMember['last_login_at'] ? date('M j, g:i A', strtotime($currentMember['last_login_at'])) : 'First time'; ?></div>
                    </div>
                    <?php if ($currentMember['can_upload']): ?>
                        <a href="upload" class="bg-gold text-plum px-4 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Upload Photos
                        </a>
                    <?php endif; ?>
                    <a href="logout" class="bg-cream text-plum px-4 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors">
                        Sign Out
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Welcome Message -->
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-plum font-montserrat mb-4">
                Welcome to Our Family Album
            </h2>
            <p class="text-lg text-gray-700 max-w-2xl mx-auto">
                A collection of precious moments and memories, each accompanied by words of faith and inspiration.
            </p>
        </div>

        <?php if (empty($photos)): ?>
            <!-- No Photos Message -->
            <div class="text-center py-16">
                <div class="bg-white rounded-2xl p-12 shadow-lg max-w-md mx-auto">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-plum mb-2">Album Coming Soon</h3>
                    <p class="text-gray-600">Photos are being prepared with love. Please check back soon!</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Photo Grid -->
            <div class="photo-grid">
                <?php foreach ($photos as $photo): ?>
                    <article class="photo-card" data-featured="<?php echo $photo['is_featured'] ? 'true' : 'false'; ?>">
                        <div class="relative">
                            <?php if ($photo['is_featured']): ?>
                                <div class="absolute top-3 right-3 z-10">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gold text-plum shadow-lg">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                        Featured
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <img 
                                src="/image-proxy.php?path=<?php echo urlencode(basename($photo['file_path'])); ?>" 
                                alt="<?php echo htmlspecialchars($photo['alt_text'] ?? 'Family photo'); ?>"
                                class="photo-image cursor-pointer"
                                onclick="openLightbox(this, <?php echo htmlspecialchars(json_encode([
                                    'caption' => $photo['caption'] ?? '',
                                    'scripture' => $photo['scripture_reference'] ?? '',
                                    'date' => date('F j, Y', strtotime($photo['created_at']))
                                ])); ?>)"
                                loading="lazy"
                            />
                        </div>
                        
                        <div class="p-6">
                            <?php if ($photo['caption']): ?>
                                <blockquote class="caption-text mb-4">
                                    "<?php echo nl2br(htmlspecialchars($photo['caption'])); ?>"
                                </blockquote>
                            <?php endif; ?>
                            
                            <?php if ($photo['scripture_reference']): ?>
                                <cite class="scripture-reference block">
                                    — <?php echo htmlspecialchars($photo['scripture_reference']); ?>
                                </cite>
                            <?php endif; ?>
                            
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-xs text-gray-400">
                                    Added <?php echo date('F j, Y', strtotime($photo['created_at'])); ?>
                                </div>
                                <button 
                                    onclick="openLightbox(this.closest('.photo-card').querySelector('.photo-image'), <?php echo htmlspecialchars(json_encode([
                                        'caption' => $photo['caption'] ?? '',
                                        'scripture' => $photo['scripture_reference'] ?? '',
                                        'date' => date('F j, Y', strtotime($photo['created_at']))
                                    ])); ?>)"
                                    class="text-plum hover:text-gold text-sm font-medium transition-colors"
                                >
                                    View Full Size
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-plum text-cream py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gold font-medium">
                "Every good and perfect gift is from above" — James 1:17
            </p>
            <p class="text-sm text-cream/70 mt-2">
                © <?php echo date('Y'); ?> Naomi Wendot Family Album
            </p>
        </div>
    </footer>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden">
        <div class="h-full flex flex-col">
            <!-- Close Button -->
            <div class="flex justify-end p-4 flex-shrink-0">
                <button onclick="closeLightbox()" class="text-white hover:text-gold text-2xl transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Image Container -->
            <div class="flex-1 flex items-center justify-center px-4 min-h-0">
                <div class="w-full h-full flex items-center justify-center">
                    <img 
                        id="lightbox-image" 
                        src="" 
                        alt="" 
                        class="max-w-full max-h-full object-contain"
                    />
                </div>
            </div>
            
            <!-- Scrollable Content Panel -->
            <div class="bg-white bg-opacity-95 backdrop-blur-sm flex-shrink-0 max-h-48 overflow-hidden">
                <div class="max-w-4xl mx-auto">
                    <div id="lightbox-content" class="text-center px-6 py-4 max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gold scrollbar-track-gray-200">
                        <div id="lightbox-caption" class="caption-text mb-3 text-lg leading-relaxed"></div>
                        <div id="lightbox-scripture" class="scripture-reference text-base mb-2"></div>
                        <div id="lightbox-date" class="text-xs text-gray-500"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openLightbox(img, data) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightbox-image');
            const lightboxCaption = document.getElementById('lightbox-caption');
            const lightboxScripture = document.getElementById('lightbox-scripture');
            const lightboxDate = document.getElementById('lightbox-date');
            
            lightboxImage.src = img.src;
            lightboxImage.alt = img.alt;
            
            // Handle caption display
            if (data.caption) {
                lightboxCaption.innerHTML = '"' + data.caption.replace(/\n/g, '<br>') + '"';
                lightboxCaption.style.display = 'block';
            } else {
                lightboxCaption.style.display = 'none';
            }
            
            // Handle scripture display
            if (data.scripture) {
                lightboxScripture.textContent = '— ' + data.scripture;
                lightboxScripture.style.display = 'block';
            } else {
                lightboxScripture.style.display = 'none';
            }
            
            lightboxDate.textContent = 'Added ' + data.date;
            
            // Show lightbox with animation
            lightbox.classList.remove('hidden');
            lightbox.style.opacity = '0';
            document.body.style.overflow = 'hidden';
            
            // Fade in animation
            setTimeout(() => {
                lightbox.style.transition = 'opacity 0.3s ease';
                lightbox.style.opacity = '1';
            }, 10);
            
            // Ensure image fits properly after load
            lightboxImage.onload = function() {
                adjustImageSize(this);
            };
            
            // If image is already loaded
            if (lightboxImage.complete) {
                adjustImageSize(lightboxImage);
            }
        }
        
        function adjustImageSize(img) {
            const windowHeight = window.innerHeight;
            const windowWidth = window.innerWidth;
            const headerHeight = 64; // Close button area
            const captionMaxHeight = 192; // Max height for caption area (max-h-48)
            const padding = 32; // Padding around image
            
            // Calculate available space for the image
            const availableHeight = windowHeight - headerHeight - captionMaxHeight - padding;
            const availableWidth = windowWidth - padding;
            
            // Get natural dimensions
            const naturalWidth = img.naturalWidth;
            const naturalHeight = img.naturalHeight;
            const aspectRatio = naturalWidth / naturalHeight;
            
            // Calculate optimal dimensions to fill available space
            let optimalWidth, optimalHeight;
            
            if (aspectRatio > availableWidth / availableHeight) {
                // Landscape or wide image - fit to width
                optimalWidth = Math.min(availableWidth, naturalWidth);
                optimalHeight = optimalWidth / aspectRatio;
                
                // If height is too tall, scale down
                if (optimalHeight > availableHeight) {
                    optimalHeight = availableHeight;
                    optimalWidth = optimalHeight * aspectRatio;
                }
            } else {
                // Portrait or tall image - fit to height
                optimalHeight = Math.min(availableHeight, naturalHeight);
                optimalWidth = optimalHeight * aspectRatio;
                
                // If width is too wide, scale down
                if (optimalWidth > availableWidth) {
                    optimalWidth = availableWidth;
                    optimalHeight = optimalWidth / aspectRatio;
                }
            }
            
            // Apply the calculated dimensions
            img.style.width = optimalWidth + 'px';
            img.style.height = optimalHeight + 'px';
            img.style.maxWidth = availableWidth + 'px';
            img.style.maxHeight = availableHeight + 'px';
        }
        
        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            
            // Fade out animation
            lightbox.style.transition = 'opacity 0.3s ease';
            lightbox.style.opacity = '0';
            
            setTimeout(() => {
                lightbox.classList.add('hidden');
                lightbox.style.opacity = '';
                lightbox.style.transition = '';
                document.body.style.overflow = 'auto';
            }, 300);
        }
        
        // Close lightbox on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });
        
        // Close lightbox on click outside image
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this || e.target.closest('#lightbox-content')) {
                closeLightbox();
            }
        });
        
        // Prevent closing when clicking on the image
        document.getElementById('lightbox-image').addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const lightboxImage = document.getElementById('lightbox-image');
            if (lightboxImage.src && !document.getElementById('lightbox').classList.contains('hidden')) {
                adjustImageSize(lightboxImage);
            }
        });
        
        // Lazy loading and fade-in animation for photo cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.photo-card');
            
            // Intersection Observer for smooth card animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '0';
                        entry.target.style.transform = 'translateY(30px)';
                        entry.target.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                        
                        // Stagger animation timing
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                        
                        observer.unobserve(entry.target);
                    }
                });
            }, { 
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });
            
            cards.forEach(card => observer.observe(card));
            
            // Add keyboard navigation for accessibility
            document.addEventListener('keydown', function(e) {
                const lightbox = document.getElementById('lightbox');
                if (!lightbox.classList.contains('hidden')) {
                    switch(e.key) {
                        case 'ArrowLeft':
                        case 'ArrowRight':
                            // Could implement next/previous photo navigation here
                            break;
                    }
                }
            });
        });
        
        // Add touch/swipe support for mobile
        let touchStartX = 0;
        let touchStartY = 0;
        
        document.getElementById('lightbox').addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        });
        
        document.getElementById('lightbox').addEventListener('touchend', function(e) {
            if (!e.changedTouches[0]) return;
            
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            
            // Swipe down to close
            if (Math.abs(deltaY) > Math.abs(deltaX) && deltaY > 50) {
                closeLightbox();
            }
        });
    </script>
</body>
</html>
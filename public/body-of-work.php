<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/writing-pieces.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Body of Work — Naomi Wendot";
$metaDescription = "Browse Naomi Wendot's poems, articles, daily inspirations, stories, and testimonies — writing rooted in faith and hope.";

// Get all writing pieces
$writings = $writingPieces;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
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
                        playfair: ['"Playfair Display"', 'serif'],
                        inter: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600;1,700;1,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo basePath(); ?>assets/css/custom.css">
    
    <style>
        /* Prevent horizontal overflow */
        html, body {
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        /* Ensure all sections respect viewport width */
        section {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Fix any potential flex/grid overflow */
        .grid, .flex {
            max-width: 100%;
        }
    </style>
</head>
<body class="font-inter">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- HERO (Dynamic based on category filter) -->
    <section id="hero" class="hero-parallax relative h-[380px] flex items-center justify-center" style="background-image: url('<?php echo basePath(); ?>assets/images/Archives2.png');">
        <!-- Overlay -->
        <div class="hero-overlay absolute inset-0" style="background: linear-gradient(135deg, rgba(74,25,66,0.85), rgba(74,25,66,0.75));"></div>
        
        <!-- Content -->
        <div class="relative z-20 text-center px-4 fade-in-up">
            <!-- Breadcrumb -->
            <div class="text-sm mb-6">
                <a href="index.php" class="text-gold hover:underline font-inter">Home</a>
                <span class="text-cream text-opacity-60 mx-2">></span>
                <span class="text-cream font-bold font-inter" id="breadcrumb-category">Body of Work</span>
            </div>
            
            <!-- Heading (Dynamic) -->
            <h1 id="hero-heading" class="font-playfair font-bold text-cream text-5xl md:text-6xl mb-4">
                Body of Work
            </h1>
            
            <!-- Subtext (Dynamic) -->
            <p id="hero-subtext" class="text-gold italic text-lg font-playfair">
                Poems. Articles. Daily Inspirations. Stories. Testimonies.
            </p>
        </div>
    </section>

    <!-- SIYASA-STYLE GRID WITH SIDEBAR -->
    <section id="writing-grid" class="bg-rose py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- MAIN CONTENT: 3 columns -->
                <div class="lg:col-span-3 space-y-8">
                    <!-- Featured First Post (Large Card) -->
                    <?php if (!empty($writings)): ?>
                        <?php 
                        $featuredPiece = $writings[0];
                        // Category pill styling
                        $featuredPillClass = '';
                        switch($featuredPiece['category']) {
                            case 'poems':
                                $featuredPillClass = 'bg-plum bg-opacity-10 text-plum';
                                break;
                            case 'articles':
                                $featuredPillClass = 'bg-gold bg-opacity-10 text-gold';
                                break;
                            case 'daily-inspirations':
                                $featuredPillClass = 'bg-rose border border-plum text-plum';
                                break;
                            case 'stories':
                                $featuredPillClass = 'bg-green-100 text-green-800';
                                break;
                            case 'testimonies':
                                $featuredPillClass = 'bg-transparent border border-gold text-gold';
                                break;
                        }
                        ?>
                        <article class="bg-white rounded-2xl shadow-md overflow-hidden hover-lift fade-in-up writing-card" 
                                 data-category="<?php echo e($featuredPiece['category']); ?>">
                            <!-- Featured Image -->
                            <?php if ($featuredPiece['image']): ?>
                                <div class="h-72 overflow-hidden">
                                    <img src="<?php echo basePath(); ?>assets/images/<?php echo e($featuredPiece['image']); ?>" 
                                         alt="<?php echo e($featuredPiece['title']); ?>" 
                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                                </div>
                            <?php else: ?>
                                <div class="h-72 bg-gradient-to-br from-plum to-gold flex items-center justify-center">
                                    <span class="text-6xl opacity-30">📝</span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Content -->
                            <div class="p-8">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?php echo $featuredPillClass; ?>">
                                        FEATURED · <?php echo e($featuredPiece['category_label']); ?>
                                    </span>
                                    <span class="text-gold text-xs font-inter"><?php echo e($featuredPiece['date']); ?></span>
                                </div>
                                
                                <h2 class="font-playfair font-bold text-plum text-2xl md:text-3xl mb-3 hover:text-gold transition-colors">
                                    <a href="piece-single.php?slug=<?php echo e($featuredPiece['slug']); ?>">
                                        <?php echo e($featuredPiece['title']); ?>
                                    </a>
                                </h2>
                                
                                <p class="text-charcoal text-base font-inter leading-relaxed mb-5 line-clamp-3">
                                    <?php echo e($featuredPiece['excerpt']); ?>
                                </p>
                                
                                <a href="piece-single.php?slug=<?php echo e($featuredPiece['slug']); ?>" 
                                   class="inline-flex items-center gap-2 text-plum text-sm font-semibold hover:text-gold transition-colors font-inter">
                                    Read Full Piece 
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php endif; ?>
                    
                    <!-- Remaining Posts Grid (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php 
                        $remainingPieces = array_slice($writings, 1);
                        foreach ($remainingPieces as $index => $piece): 
                            // Category pill styling
                            $pillClass = '';
                            switch($piece['category']) {
                                case 'poems':
                                    $pillClass = 'bg-plum bg-opacity-10 text-plum';
                                    break;
                                case 'articles':
                                    $pillClass = 'bg-gold bg-opacity-10 text-gold';
                                    break;
                                case 'daily-inspirations':
                                    $pillClass = 'bg-rose border border-plum text-plum';
                                    break;
                                case 'stories':
                                    $pillClass = 'bg-green-100 text-green-800';
                                    break;
                                case 'testimonies':
                                    $pillClass = 'bg-transparent border border-gold text-gold';
                                    break;
                            }
                        ?>
                            <article class="bg-white rounded-2xl shadow-sm overflow-hidden hover-lift fade-in-up writing-card" 
                                     data-category="<?php echo e($piece['category']); ?>"
                                     style="transition-delay: <?php echo ($index % 2) * 100; ?>ms;">
                                <!-- Image or Placeholder -->
                                <?php if ($piece['image']): ?>
                                    <div class="h-48 overflow-hidden">
                                        <img src="<?php echo basePath(); ?>assets/images/<?php echo e($piece['image']); ?>" 
                                             alt="<?php echo e($piece['title']); ?>" 
                                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                                    </div>
                                <?php else: ?>
                                    <div class="h-48 bg-gradient-to-br from-plum to-gold flex items-center justify-center">
                                        <span class="text-4xl opacity-30">📝</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Content -->
                                <div class="p-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?php echo $pillClass; ?>">
                                            <?php echo e($piece['category_label']); ?>
                                        </span>
                                    </div>
                                    
                                    <h3 class="font-playfair font-bold text-plum text-lg mb-2 line-clamp-2 hover:text-gold transition-colors">
                                        <a href="piece-single.php?slug=<?php echo e($piece['slug']); ?>">
                                            <?php echo e($piece['title']); ?>
                                        </a>
                                    </h3>
                                    
                                    <p class="text-charcoal text-sm font-inter leading-relaxed mb-3 line-clamp-2">
                                        <?php echo e($piece['excerpt']); ?>
                                    </p>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-gold text-xs font-inter"><?php echo e($piece['date']); ?></span>
                                        <a href="piece-single.php?slug=<?php echo e($piece['slug']); ?>" 
                                           class="text-plum text-xs font-semibold hover:text-gold transition-colors font-inter">
                                            Read →
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Empty State -->
                    <div id="empty-state" class="hidden text-center py-16">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg font-inter">
                            Nothing here yet — check back soon.
                        </p>
                    </div>
                </div>
                
                <!-- SIDEBAR: 1 column -->
                <aside class="space-y-6">
                    <!-- Categories Widget -->
                    <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-gold reveal sticky top-24">
                        <h3 class="text-gold uppercase text-xs tracking-widest font-bold mb-5 font-inter flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Categories
                        </h3>
                        <ul class="space-y-2">
                            <li>
                                <button 
                                    class="tab-button w-full text-left text-sm font-semibold hover:text-gold transition-colors font-inter flex items-center justify-between active"
                                    data-filter="all"
                                    style="color: #4A1942;"
                                >
                                    All Writing
                                    <span class="text-xs opacity-50"><?php echo count($writings); ?></span>
                                </button>
                            </li>
                            <?php
                            $allCategories = [
                                'poems' => 'Poems',
                                'articles' => 'Articles',
                                'daily-inspirations' => 'Daily Inspirations',
                                'stories' => 'Stories',
                                'testimonies' => 'Testimonies'
                            ];
                            
                            foreach ($allCategories as $catSlug => $catName):
                                $count = count(array_filter($writings, function($p) use ($catSlug) {
                                    return $p['category'] === $catSlug;
                                }));
                            ?>
                                <li>
                                    <button 
                                        class="tab-button w-full text-left text-sm font-semibold hover:text-gold transition-colors font-inter flex items-center justify-between"
                                        data-filter="<?php echo e($catSlug); ?>"
                                        style="color: #4A1942;"
                                    >
                                        <?php echo e($catName); ?>
                                        <span class="text-xs opacity-50"><?php echo $count; ?></span>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Visual Separator -->
                    <div class="h-px bg-gradient-to-r from-transparent via-gold to-transparent opacity-30"></div>
                    
                    <!-- Recent Posts Widget -->
                    <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-plum reveal">
                        <h3 class="text-plum uppercase text-xs tracking-widest font-bold mb-5 font-inter flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Recent Pieces
                        </h3>
                        <ul class="space-y-4">
                            <?php 
                            $recentPieces = array_slice($writings, 0, 3);
                            foreach ($recentPieces as $rp): 
                            ?>
                                <li>
                                    <a href="piece-single.php?slug=<?php echo e($rp['slug']); ?>" class="group block">
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-gold bg-opacity-10 text-gold font-semibold inline-block mb-1">
                                            <?php echo e($rp['category_label']); ?>
                                        </span>
                                        <p class="text-sm font-bold leading-snug group-hover:text-gold transition-colors text-plum font-playfair line-clamp-2">
                                            <?php echo e($rp['title']); ?>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1 font-inter"><?php echo e($rp['date']); ?></p>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Newsletter CTA -->
                    <div class="rounded-2xl p-6 reveal" style="background: linear-gradient(135deg, #4A1942, #5A2952);">
                        <h3 class="text-sm font-bold text-cream mb-2 font-playfair">Stay Encouraged</h3>
                        <p class="text-xs mb-4 font-inter" style="color: rgba(255, 255, 255, 0.75);">
                            Get new poems, daily inspirations, and stories straight to your inbox.
                        </p>
                        <?php
                        $context = 'body-of-work';
                        include __DIR__ . '/../includes/newsletter-signup.php';
                        ?>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
    
    <!-- Inline Tab Filtering Script with Dynamic Hero Updates -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabButtons = document.querySelectorAll('.tab-button');
            const writingCards = document.querySelectorAll('.writing-card');
            const emptyState = document.getElementById('empty-state');
            const heroHeading = document.getElementById('hero-heading');
            const heroSubtext = document.getElementById('hero-subtext');
            const breadcrumbCategory = document.getElementById('breadcrumb-category');
            
            // Category data for hero updates
            const categoryData = {
                'all': {
                    heading: 'Body of Work',
                    subtext: 'Poems. Articles. Daily Inspirations. Stories. Testimonies.',
                    breadcrumb: 'Body of Work'
                },
                'poems': {
                    heading: 'Poems',
                    subtext: 'Freestyle, heartfelt expressions of faith, hope, and everyday beauty',
                    breadcrumb: 'Poems'
                },
                'articles': {
                    heading: 'Articles',
                    subtext: 'Thoughtful reflections on life, faith, and personal growth',
                    breadcrumb: 'Articles'
                },
                'daily-inspirations': {
                    heading: 'Daily Inspirations',
                    subtext: 'Short, Holy Spirit-inspired messages to start your day with hope',
                    breadcrumb: 'Daily Inspirations'
                },
                'stories': {
                    heading: 'Stories',
                    subtext: 'Narratives of faith, courage, and transformation',
                    breadcrumb: 'Stories'
                },
                'testimonies': {
                    heading: 'Testimonies',
                    subtext: 'Real stories of God\'s grace, protection, and answered prayer',
                    breadcrumb: 'Testimonies'
                }
            };
            
            // Update hero function
            function updateHero(filter) {
                const data = categoryData[filter] || categoryData['all'];
                
                // Add fade out effect
                heroHeading.style.opacity = '0';
                heroSubtext.style.opacity = '0';
                breadcrumbCategory.style.opacity = '0';
                
                setTimeout(() => {
                    heroHeading.textContent = data.heading;
                    heroSubtext.textContent = data.subtext;
                    breadcrumbCategory.textContent = data.breadcrumb;
                    
                    // Fade in
                    heroHeading.style.transition = 'opacity 0.3s ease';
                    heroSubtext.style.transition = 'opacity 0.3s ease';
                    breadcrumbCategory.style.transition = 'opacity 0.3s ease';
                    
                    heroHeading.style.opacity = '1';
                    heroSubtext.style.opacity = '1';
                    breadcrumbCategory.style.opacity = '1';
                }, 150);
            }
            
            // Tab click handler
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const filter = button.getAttribute('data-filter');
                    
                    // Update active tab styling
                    tabButtons.forEach(btn => {
                        if (btn === button) {
                            btn.classList.add('active');
                            btn.style.color = '#D4A017';
                        } else {
                            btn.classList.remove('active');
                            btn.style.color = '#4A1942';
                        }
                    });
                    
                    // Update hero
                    updateHero(filter);
                    
                    // Filter cards
                    filterCards(filter);
                    
                    // Update URL hash
                    if (filter !== 'all') {
                        window.history.pushState(null, null, '#' + filter);
                    } else {
                        window.history.pushState(null, null, window.location.pathname);
                    }
                });
            });
            
            // Filter cards function
            function filterCards(filter) {
                let visibleCount = 0;
                
                writingCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    
                    if (filter === 'all' || category === filter) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Toggle empty state
                if (visibleCount === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }
            
            // Check for hash on page load
            const hash = window.location.hash.substring(1); // Remove #
            if (hash && hash !== '') {
                const matchingButton = document.querySelector(`[data-filter="${hash}"]`);
                if (matchingButton) {
                    matchingButton.click();
                    
                    // Scroll to top of content with offset
                    setTimeout(() => {
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }, 100);
                }
            }
        });
    </script>
</body>
</html>

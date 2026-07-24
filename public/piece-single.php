<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/writing-pieces.php';
require_once __DIR__ . '/../includes/functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$piece = getPieceBySlug($slug);

if (!$piece) {
    http_response_code(404);
    $pageTitle = 'Piece Not Found';
} else {
    $pageTitle = $piece['title'];
}

// Get related pieces
$relatedPieces = $piece ? getRelatedPieces($piece['slug'], $piece['category'], 3) : [];

// All categories for sidebar
$allCategories = [
    'poems' => 'Poems',
    'articles' => 'Articles',
    'daily-inspirations' => 'Daily Inspirations',
    'stories' => 'Stories',
    'testimonies' => 'Testimonies'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $piece ? htmlspecialchars($piece['excerpt']) : 'Writing piece not found'; ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Naomi Wendot</title>
    
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
        /* Reading progress bar */
        #read-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #4A1942, #D4A017);
            z-index: 9999;
            transition: width 0.1s linear;
            width: 0;
        }
        
        /* Article card */
        .article-card {
            background: #fff;
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            box-shadow: 0 4px 24px rgba(74, 25, 66, 0.08);
            border-top: 4px solid #D4A017;
        }
        
        @media (max-width: 640px) {
            .article-card {
                padding: 1.5rem 1.25rem;
            }
        }
        
        /* Prose styling */
        .prose-naomi h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #4A1942;
            margin: 2rem 0 0.75rem;
            font-family: 'Playfair Display', serif;
        }
        
        .prose-naomi h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #4A1942;
            margin: 1.5rem 0 0.6rem;
            font-family: 'Playfair Display', serif;
        }
        
        .prose-naomi p {
            margin-bottom: 1rem;
            color: #374151;
            line-height: 1.85;
            font-size: 1rem;
        }
        
        .prose-naomi ul,
        .prose-naomi ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
            color: #374151;
            font-size: 1rem;
        }
        
        .prose-naomi li {
            margin-bottom: 0.5rem;
            line-height: 1.75;
        }
        
        .prose-naomi blockquote {
            border-left: 4px solid #D4A017;
            padding: 1rem 1.5rem;
            background: rgba(212, 160, 23, 0.07);
            border-radius: 0 0.75rem 0.75rem 0;
            margin: 1.75rem 0;
            font-style: italic;
            color: #4A1942;
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
        }
        
        .prose-naomi blockquote footer {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #D4A017;
            font-weight: 600;
        }
        
        .prose-naomi a {
            color: #D4A017;
            font-weight: 600;
        }
        
        .prose-naomi a:hover {
            opacity: 0.8;
        }
        
        .prose-naomi strong {
            color: #4A1942;
            font-weight: 700;
        }
        
        .prose-naomi em {
            font-style: italic;
        }
        
        /* Featured image */
        .featured-img-wrap {
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(74, 25, 66, 0.18);
            position: relative;
        }
        
        .featured-img-wrap img {
            width: 100%;
            height: 420px;
            object-fit: cover;
            display: block;
        }
        
        @media (max-width: 640px) {
            .featured-img-wrap img {
                height: 280px;
            }
        }
        
        /* Category badge */
        .cat-badge {
            display: inline-block;
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(212, 160, 23, 0.15);
            color: #D4A017;
            border: 1px solid rgba(212, 160, 23, 0.3);
        }
        
        /* Share buttons */
        .share-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1.5px solid rgba(74, 25, 66, 0.15);
            color: #4A1942;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .share-pill:hover {
            background: #4A1942;
            color: #fff;
            border-color: #4A1942;
        }
        
        /* Related card */
        .related-card {
            background: #fff;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 3px 16px rgba(74, 25, 66, 0.08);
            border-bottom: 3px solid transparent;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 36px rgba(74, 25, 66, 0.14);
            border-bottom-color: #D4A017;
        }
        
        .related-card-img img {
            transition: transform 0.5s ease;
            width: 100%;
            height: 160px;
            object-fit: cover;
        }
        
        .related-card:hover .related-card-img img {
            transform: scale(1.06);
        }
        
        .related-card-placeholder {
            background: linear-gradient(135deg, #4A1942, #5A2952);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 160px;
        }
    </style>
</head>
<body class="font-inter">
    <!-- Reading progress bar -->
    <div id="read-progress"></div>
    
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <?php if (!$piece): ?>
        <!-- Not Found -->
        <main class="max-w-7xl mx-auto px-4 py-20">
            <div class="rounded-2xl p-10 text-center"
                 style="background:rgba(212,160,23,0.1);border:2px dashed rgba(212,160,23,0.35);">
                <p class="text-5xl mb-4">📝</p>
                <h1 class="text-3xl font-bold text-plum mb-3 font-playfair">Piece Not Found</h1>
                <p class="text-charcoal mb-6">This writing piece may have been moved or removed.</p>
                <a href="body-of-work.php" class="inline-block px-8 py-3 bg-plum text-cream font-semibold rounded-full hover:opacity-90 transition-all">
                    ← Back to Body of Work
                </a>
            </div>
        </main>
    <?php else: ?>
        <!-- Slim breadcrumb hero (BCH-style) -->
        <section class="bg-gradient-to-r from-plum to-charcoal py-10 px-4">
            <div class="max-w-7xl mx-auto">
                <nav class="flex items-center text-sm text-cream text-opacity-80">
                    <a href="index.php" class="hover:text-gold transition-colors">Home</a>
                    <span class="mx-3 text-gold">/</span>
                    <a href="body-of-work.php" class="hover:text-gold transition-colors">Body of Work</a>
                    <span class="mx-3 text-gold">/</span>
                    <span class="text-gold font-medium truncate max-w-xs"><?php echo htmlspecialchars(mb_strimwidth($piece['title'], 0, 45, '…')); ?></span>
                </nav>
            </div>
        </section>
        
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
                <!-- Main Content -->
                <article class="lg:col-span-3 space-y-6 reveal" id="article-body">
                    <!-- Featured Image -->
                    <?php if ($piece['image']): ?>
                        <div class="featured-img-wrap">
                            <img src="<?php echo basePath(); ?>assets/images/<?php echo e($piece['image']); ?>" 
                                 alt="<?php echo e($piece['title']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <!-- Article Card -->
                    <div class="article-card">
                        <!-- Meta Row -->
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <span class="cat-badge"><?php echo e($piece['category_label']); ?></span>
                            <span class="flex items-center gap-1.5 text-sm text-gray-400 font-inter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <?php echo e($piece['date']); ?>
                            </span>
                            <span class="text-sm text-gray-400 ml-auto font-inter" id="read-time-label"></span>
                        </div>
                        
                        <!-- Title -->
                        <h1 class="text-3xl md:text-4xl font-bold mb-4 leading-tight text-plum font-playfair">
                            <?php echo e($piece['title']); ?>
                        </h1>
                        
                        <div class="h-1 w-16 bg-gold rounded-full mb-6"></div>
                        
                        <!-- Content -->
                        <div class="prose-naomi" id="prose-content">
                            <?php echo $piece['content']; ?>
                        </div>
                    </div>
                    
                    <!-- Share Row -->
                    <div class="flex flex-wrap items-center gap-3 reveal">
                        <span class="text-sm font-semibold text-gray-500 mr-1 font-inter">Share:</span>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($piece['title']); ?>&url=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"
                           target="_blank" rel="noopener" class="share-pill">
                            𝕏 Twitter
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($piece['title'].' — https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"
                           target="_blank" rel="noopener" class="share-pill">
                            WhatsApp
                        </a>
                        <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>{this.textContent='✓ Copied!'});setTimeout(()=>{this.textContent='Copy Link'},2000)"
                                class="share-pill">
                            Copy Link
                        </button>
                    </div>
                    
                    <!-- Back Button -->
                    <div class="flex flex-wrap items-center gap-3 reveal">
                        <a href="body-of-work.php" class="inline-flex items-center gap-2 px-6 py-3 bg-plum text-cream font-semibold rounded-full hover:opacity-90 transition-all font-inter">
                            ← Back to Body of Work
                        </a>
                        <a href="body-of-work.php#<?php echo e($piece['category']); ?>" 
                           class="inline-flex items-center gap-2 px-6 py-3 bg-gold text-plum font-semibold rounded-full hover:opacity-90 transition-all font-inter">
                            More <?php echo e($piece['category_label']); ?>s
                        </a>
                    </div>
                </article>
                
                <!-- Sidebar -->
                <aside class="space-y-6">
                    <!-- Categories -->
                    <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-gold reveal">
                        <h3 class="text-gold uppercase text-xs tracking-widest font-bold mb-4 font-inter">Categories</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="body-of-work.php" 
                                   class="text-sm font-semibold hover:text-gold transition-colors font-inter flex items-center justify-between"
                                   style="color: #4A1942;">
                                    All Writing
                                </a>
                            </li>
                            <?php foreach ($allCategories as $catSlug => $catName): ?>
                                <li>
                                    <a href="body-of-work.php#<?php echo e($catSlug); ?>" 
                                       class="text-sm font-semibold hover:text-gold transition-colors font-inter flex items-center justify-between <?php echo $piece['category'] === $catSlug ? 'text-gold' : ''; ?>"
                                       style="color: <?php echo $piece['category'] === $catSlug ? '#D4A017' : '#4A1942'; ?>;">
                                        <?php echo e($catName); ?>
                                        <?php if ($piece['category'] === $catSlug): ?>
                                            <span class="text-gold">→</span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Related Pieces -->
                    <?php if (!empty($relatedPieces)): ?>
                        <div class="bg-white rounded-2xl p-6 shadow-md border-t-4 border-gold reveal">
                            <h3 class="text-gold uppercase text-xs tracking-widest font-bold mb-4 font-inter">Related</h3>
                            <ul class="space-y-4">
                                <?php foreach ($relatedPieces as $rp): ?>
                                    <li>
                                        <a href="piece-single.php?slug=<?php echo e($rp['slug']); ?>" class="group block">
                                            <span class="cat-badge text-[10px] mb-1 inline-block"><?php echo e($rp['category_label']); ?></span>
                                            <p class="text-sm font-bold leading-snug group-hover:text-gold transition-colors text-plum font-playfair">
                                                <?php echo e($rp['title']); ?>
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1 font-inter"><?php echo e($rp['date']); ?></p>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Newsletter CTA -->
                    <div class="rounded-2xl p-6 reveal" style="background: linear-gradient(135deg, #4A1942, #5A2952);">
                        <h3 class="text-sm font-bold text-cream mb-2 font-playfair">Stay Encouraged</h3>
                        <p class="text-xs mb-4 font-inter" style="color: rgba(255, 255, 255, 0.75);">
                            Get new poems, daily inspirations, and stories straight to your inbox.
                        </p>
                        <?php
                        $context = 'piece-single';
                        include __DIR__ . '/../includes/newsletter-signup.php';
                        ?>
                    </div>
                    
                    <!-- Contact CTA -->
                    <div class="bg-gold rounded-2xl p-6 reveal text-center">
                        <p class="text-plum uppercase text-xs tracking-widest font-bold mb-2 font-inter">Get In Touch</p>
                        <h3 class="text-sm font-bold text-plum mb-3 font-playfair">Want to Connect?</h3>
                        <a href="contact.php" class="inline-block w-full px-6 py-3 bg-plum text-cream font-semibold rounded-full hover:opacity-90 transition-all font-inter text-sm">
                            Send a Message
                        </a>
                        <a href="<?php echo e(whatsappLink('Hello Naomi, I loved your writing!')); ?>" 
                           target="_blank" 
                           class="block text-center mt-3 text-xs font-semibold font-inter"
                           style="color: rgba(74, 25, 66, 0.8);">
                            📱 WhatsApp
                        </a>
                    </div>
                </aside>
            </div>
            
            <!-- Related Pieces Grid (Full Width) -->
            <?php if (!empty($relatedPieces)): ?>
                <section class="mt-16 reveal">
                    <div class="text-center mb-10">
                        <p class="text-gold uppercase text-xs tracking-widest font-bold mb-2 font-inter">Keep Reading</p>
                        <h2 class="text-3xl font-bold text-plum font-playfair">
                            More <?php echo e($piece['category_label']); ?>s
                        </h2>
                        <div class="h-1 w-16 bg-gold rounded-full mx-auto mt-3"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($relatedPieces as $rp): ?>
                            <a href="piece-single.php?slug=<?php echo e($rp['slug']); ?>" class="related-card group">
                                <div class="related-card-img overflow-hidden">
                                    <?php if ($rp['image']): ?>
                                        <img src="<?php echo basePath(); ?>assets/images/<?php echo e($rp['image']); ?>" alt="<?php echo e($rp['title']); ?>">
                                    <?php else: ?>
                                        <div class="related-card-placeholder">
                                            <span class="text-3xl opacity-30">📝</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-5">
                                    <span class="cat-badge text-[10px] mb-2 inline-block"><?php echo e($rp['category_label']); ?></span>
                                    <h3 class="text-base font-bold mb-1 line-clamp-2 group-hover:text-gold transition-colors text-plum font-playfair">
                                        <?php echo e($rp['title']); ?>
                                    </h3>
                                    <p class="text-xs text-gray-400 font-inter"><?php echo e($rp['date']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    <?php endif; ?>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
    
    <script>
        // Reading progress bar
        (function() {
            const bar = document.getElementById('read-progress');
            const body = document.getElementById('article-body');
            if (!bar || !body) return;
            
            function update() {
                const rect = body.getBoundingClientRect();
                const total = rect.height - window.innerHeight;
                const progress = Math.min(Math.max(-rect.top / total * 100, 0), 100);
                bar.style.width = progress + '%';
            }
            
            window.addEventListener('scroll', update, { passive: true });
        })();
        
        // Read time estimate
        (function() {
            const el = document.getElementById('read-time-label');
            const prose = document.getElementById('prose-content');
            if (!el || !prose) return;
            
            const words = prose.innerText.trim().split(/\s+/).length;
            const mins = Math.max(1, Math.round(words / 200));
            el.textContent = mins + ' min read';
        })();
    </script>
</body>
</html>

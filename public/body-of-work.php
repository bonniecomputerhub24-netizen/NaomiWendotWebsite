<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/post-functions.php';
require_once __DIR__ . '/../includes/video-functions.php';
require_once __DIR__ . '/../includes/video-card.php';

// ── Filters ──
$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : '';
$contentType  = isset($_GET['type']) ? trim($_GET['type']) : ''; // New: content type filter
$search       = isset($_GET['q']) ? trim($_GET['q']) : '';
$page         = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage      = 12;

// Convert category slug to ID/name for display + query
$categories     = getCategories();
$categoryFilter = null;
$categoryName   = '';
foreach ($categories as $cat) {
    if ($cat['slug'] === $categorySlug) {
        $categoryFilter = (int)$cat['id'];
        $categoryName   = $cat['name'];
        break;
    }
}

$pageTitle       = ($categoryName ? $categoryName . ' — ' : '') . "Body of Work — Naomi Wendot";
$metaDescription = "Browse Naomi Wendot's poems, articles, daily inspirations, stories, and testimonies — writing rooted in faith and hope.";

// Fetch posts
$result      = getAllPosts($search, $categoryFilter, $page, $perPage, $contentType);
$posts       = $result['posts'];
$totalPages  = $result['pages'];
$currentPage = $result['current_page'];
$totalPosts  = $result['total'];

$recentPosts = getLatestPosts(5);
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
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo basePath(); ?>assets/css/custom.css">

    <style>
        html, body { overflow-x: hidden; max-width: 100vw; }
        section { max-width: 100%; overflow-x: hidden; }
        .grid, .flex { max-width: 100%; }

        /* Reveal animations */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.active { opacity: 1; transform: translateY(0); }

        .fade-in-up { opacity: 0; transform: translateY(20px); animation: fadeInUp 0.6s ease-out forwards; }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

        .hero-parallax { background-size: cover; background-position: center; background-attachment: fixed; }
        @media (max-width: 768px) { .hero-parallax { background-attachment: scroll; } }

        .gold-divider { height: 3px; background: linear-gradient(90deg, transparent, #D4A017, transparent); border: none; margin: 0; }
        .gold-bar { height: 3px; width: 3.5rem; border-radius: 9999px; background: #D4A017; margin: .6rem auto 0; }
        .section-label { font-size: .7rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; color: #D4A017; }

        /* Buttons */
        .btn-gold { display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.65rem 1.6rem;border-radius:9999px;background:#D4A017;color:#4A1942;font-weight:700;font-size:.8rem;transition:all .2s;box-shadow:0 3px 12px rgba(212,160,23,.3); }
        .btn-gold:hover { background:#e0b13a; transform: translateY(-1px); }
        .btn-plum { display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.65rem 1.6rem;border-radius:9999px;background:#4A1942;color:#fff;font-weight:700;font-size:.8rem;transition:all .2s;box-shadow:0 3px 12px rgba(74,25,66,.25); }
        .btn-plum:hover { background:#5A2952; transform: translateY(-1px); }

        /* Category pill filter bar */
        .cat-pill { display:inline-block;padding:.3rem .85rem;border-radius:9999px;font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;background:rgba(212,160,23,.12);color:#D4A017;border:1px solid rgba(212,160,23,.35);transition:all .2s; }
        .cat-pill:hover { background:rgba(212,160,23,.22); }
        .cat-pill.active { background:#D4A017;color:#4A1942;border-color:#D4A017; }

        /* Filter & search panel */
        .filter-panel { background:#fff;border-radius:1.25rem;padding:1.6rem 1.8rem;box-shadow:0 4px 22px rgba(74,25,66,.08);border-top:4px solid #D4A017; }

        /* Blog / writing card */
        .blog-card { background:#fff;border-radius:1.25rem;overflow:hidden;box-shadow:0 3px 16px rgba(74,25,66,.08);border-bottom:3px solid transparent;transition:transform .25s,box-shadow .25s,border-color .25s;display:flex;flex-direction:column; }
        .blog-card:hover { transform:translateY(-5px);box-shadow:0 12px 34px rgba(74,25,66,.14);border-bottom-color:#D4A017; }
        .blog-card-img { overflow:hidden; }
        .blog-card-img img { transition: transform .5s ease; }
        .blog-card:hover .blog-card-img img { transform: scale(1.06); }

        /* Featured post */
        .featured-post { background:#fff;border-radius:1.5rem;overflow:hidden;box-shadow:0 6px 28px rgba(74,25,66,.14);border-bottom:4px solid #D4A017; }
        .featured-post-img img { width:100%;height:340px;object-fit:cover;display:block;transition:transform .5s ease; }
        .featured-post:hover .featured-post-img img { transform: scale(1.04); }
        /* Portrait / handwritten images: letterbox instead of forcing a landscape crop */
        .featured-post-img.is-handwritten,
        .blog-card-img.is-handwritten { background:#FDEAEA; display:flex; align-items:center; justify-content:center; padding:.5rem; }
        .featured-post-img.is-handwritten img,
        .blog-card-img.is-handwritten img { width:auto; height:100%; max-width:100%; }

        /* Sidebar */
        .sidebar-card { background:#fff;border-radius:1rem;padding:1.4rem;box-shadow:0 3px 16px rgba(74,25,66,.08);border-top:3px solid #D4A017; }
        .newsletter-card { border-radius:1rem;padding:1.4rem;color:#fff;background:linear-gradient(135deg,#4A1942 0%,#5A2952 100%); }
        .nl-input { width:100%;padding:.6rem .85rem;border-radius:.5rem;font-size:.8rem;font-family:'Century Gothic',sans-serif;outline:none;background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2); }
        .nl-input::placeholder { color: rgba(255,255,255,.55); }
        .nl-input:focus { border-color:#D4A017; box-shadow: 0 0 0 2px rgba(212,160,23,.3); }
        .nl-btn { width:100%;padding:.6rem;border-radius:.5rem;background:#D4A017;color:#4A1942;font-weight:700;font-size:.8rem;border:none;cursor:pointer;transition:opacity .2s; }
        .nl-btn:hover { opacity: .87; }

        /* Pagination */
        .page-btn { display:inline-flex;align-items:center;justify-content:center;width:2.1rem;height:2.1rem;border-radius:.5rem;font-size:.75rem;font-weight:700;border:1.5px solid rgba(74,25,66,.15);color:#4A1942;transition:all .2s; }
        .page-btn:hover, .page-btn.active { background:#4A1942;color:#fff;border-color:#4A1942; }

        /* Bottom CTA */
        .cta-bottom { background:linear-gradient(135deg,#4A1942 0%,#5A2952 100%);border-radius:1.5rem;padding:2.5rem 2rem; }

        .line-clamp-2 { display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
        .line-clamp-3 { display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden; }
    </style>
</head>
<body class="font-century bg-cream">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- HERO -->
    <section id="hero" class="hero-parallax relative h-[380px] flex items-center justify-center" style="background-image: url('<?php echo basePath(); ?>assets/images/Archives2.png');">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(74,25,66,0.85), rgba(74,25,66,0.75));"></div>

        <div class="relative z-20 text-center px-4 fade-in-up">
            <div class="text-sm mb-6">
                <a href="index.php" class="text-gold hover:underline font-century">Home</a>
                <span class="text-cream text-opacity-60 mx-2">›</span>
                <span class="text-cream font-bold font-century">Body of Work</span>
                <?php if ($categoryName): ?>
                    <span class="text-cream text-opacity-60 mx-2">›</span>
                    <span class="text-gold font-bold font-century"><?php echo htmlspecialchars($categoryName); ?></span>
                <?php endif; ?>
            </div>

            <h1 class="font-montserrat font-bold text-cream text-5xl md:text-6xl mb-4">
                Body of Work
            </h1>

            <p class="text-gold italic text-lg font-montserrat">
                Poems. Articles. Daily Inspirations. Stories. Testimonies. Videos.
            </p>
        </div>
    </section>

    <!-- INTRO -->
    <section class="bg-cream pt-14 pb-6 px-4">
        <div class="max-w-7xl mx-auto text-center reveal active">
            <p class="section-label mb-2">From the Archives</p>
            <p class="text-charcoal text-sm max-w-xl mx-auto">
                Browse everything in one place — or explore by <a href="#" onclick="event.preventDefault(); document.querySelector('.filter-panel').scrollIntoView({behavior:'smooth'});" class="text-gold hover:underline font-semibold">category below</a>. Each piece is an offering written to encourage, testify, and point back to grace.
            </p>
            <div class="gold-bar"></div>
        </div>
    </section>

    <!-- FILTER & SEARCH PANEL -->
    <section class="bg-cream pb-10 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="filter-panel reveal active">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                    <h3 class="section-label flex items-center gap-2 mb-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 8h12M9 12h6m-4 4h2"></path>
                        </svg>
                        Browse &amp; Search
                    </h3>

                    <form method="get" class="flex items-center gap-2 w-full md:w-auto md:min-w-[320px]">
                        <?php if ($categorySlug): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($categorySlug); ?>"><?php endif; ?>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search poems, stories, articles…"
                               class="flex-1 rounded-full border border-gray-200 bg-cream px-4 py-2 text-sm focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20">
                        <button type="submit" class="btn-plum shrink-0" aria-label="Search">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                            </svg>
                            Search
                        </button>
                    </form>
                </div>

                <div class="h-px bg-gold/15 mb-5"></div>

                <!-- Category pill bar -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="body-of-work.php<?php echo $search ? '?q=' . urlencode($search) : ''; ?>"
                       class="cat-pill <?php echo $categorySlug === '' && $contentType === '' ? 'active' : ''; ?>">All Writing</a>
                    
                    <!-- Videos filter (content type, not category) -->
                    <a href="?type=video<?php echo $search ? '&q=' . urlencode($search) : ''; ?>"
                       class="cat-pill <?php echo $contentType === 'video' ? 'active' : ''; ?>"
                       style="border: 2px solid #D4A017;">
                        🎥 Videos
                    </a>
                    
                    <?php foreach ($categories as $cat): ?>
                        <!-- Skip 'Videos' category if it exists to avoid confusion with content type filter -->
                        <?php if (strtolower($cat['slug']) === 'videos') continue; ?>
                        <a href="?category=<?php echo urlencode($cat['slug']); ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>"
                           class="cat-pill <?php echo $categorySlug === $cat['slug'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($search || $categorySlug || $contentType): ?>
                        <a href="body-of-work.php" class="text-xs text-gray-400 hover:text-gold ml-2 whitespace-nowrap">Clear filters ✕</a>
                    <?php endif; ?>
                </div>

                <?php if ($categorySlug || $search || $contentType): ?>
                    <div class="text-xs text-gray-500 mt-4 pt-4 border-t border-gold/15">
                        <?php if ($contentType === 'video'): ?>
                            Showing <strong class="text-plum">Videos</strong>
                        <?php elseif ($categoryName): ?>
                            Showing <strong class="text-plum"><?php echo htmlspecialchars($categoryName); ?></strong>
                        <?php endif; ?>
                        <?php if ($search): ?> matching "<strong class="text-plum"><?php echo htmlspecialchars($search); ?></strong>"<?php endif; ?>
                        — <?php echo (int)$totalPosts; ?> piece<?php echo $totalPosts !== 1 ? 's' : ''; ?> found
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- MAIN GRID WITH SIDEBAR -->
    <section id="writing-grid" class="bg-cream pb-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
                <!-- MAIN CONTENT: 3 columns -->
                <div class="lg:col-span-3">

                    <?php if (!empty($posts)): ?>

                        <?php
                        $featuredPost = ($currentPage === 1 && !$search) ? array_shift($posts) : null;
                        ?>

                        <!-- Featured post -->
                        <?php if ($featuredPost):
                            $fPillClass = getCategoryBadgeClass($featuredPost['category_slug']);
                            $fIsVideo = !empty($featuredPost['video_file']);
                            $fIsHandwritten = !empty($featuredPost['handwritten_image']);
                        ?>
                        <article class="featured-post mb-8 reveal active">
                            <?php if ($fIsVideo): ?>
                                <!-- Video Featured Content -->
                                <div class="featured-post-img overflow-hidden relative bg-black group <?php echo getVideoCardAspectClass($featuredPost); ?>">
                                    <a href="piece-single.php?slug=<?php echo htmlspecialchars($featuredPost['slug']); ?>" class="video-card-link" data-video-src="<?php echo htmlspecialchars(basePath() . $featuredPost['video_file']); ?>">
                                        <?php 
                                        $fVideoPoster = getVideoPoster($featuredPost);
                                        if ($fVideoPoster): ?>
                                            <video 
                                                class="video-autoplay-card w-full h-full object-cover" 
                                                preload="metadata"
                                                muted
                                                loop
                                                playsinline
                                                poster="<?php echo htmlspecialchars($fVideoPoster); ?>"
                                                data-src="<?php echo htmlspecialchars(basePath() . $featuredPost['video_file']); ?>"
                                                data-post-id="<?php echo (int)$featuredPost['id']; ?>"
                                            >
                                                <!-- Video src will be lazy-loaded via IntersectionObserver -->
                                            </video>
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center">
                                                <span class="text-8xl text-white opacity-50">🎥</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- VIDEO badge -->
                                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide" 
                                              style="border: 1px solid #D4A017; background: rgba(212,160,23,0.12); color: #D4A017;">
                                            🎥 VIDEO
                                        </span>
                                        
                                        <!-- Play Button Overlay -->
                                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 group-hover:bg-opacity-40 transition-colors">
                                            <div class="play-button-pulse bg-white bg-opacity-90 rounded-full p-6 group-hover:bg-opacity-100 group-hover:scale-110 transition-all">
                                                <svg class="w-12 h-12 text-plum" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        
                                        <!-- Duration Badge -->
                                        <?php if (!empty($featuredPost['video_duration'])): ?>
                                            <span class="absolute bottom-4 right-4 text-sm px-3 py-1.5 rounded font-semibold" 
                                                  style="background: rgba(74,25,66,0.8); color: #D4A017;">
                                                <?php echo formatVideoDuration($featuredPost['video_duration']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <!-- View count badge -->
                                        <?php if (!empty($featuredPost['video_views']) && $featuredPost['video_views'] > 0): ?>
                                            <span class="absolute bottom-4 left-4 text-xs px-2 py-1 rounded font-semibold" 
                                                  style="background: rgba(0,0,0,0.75); color: rgba(255,255,255,0.9);">
                                                <?php echo formatVideoViews($featuredPost['video_views']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php elseif ($featuredPost['handwritten_image'] || $featuredPost['featured_image']): ?>
                                <!-- Image Featured Content -->
                                <div class="featured-post-img overflow-hidden <?php echo $fIsHandwritten ? 'is-handwritten' : ''; ?>">
                                    <a href="piece-single.php?slug=<?php echo htmlspecialchars($featuredPost['slug']); ?>">
                                        <img src="<?php echo basePath() . ($featuredPost['handwritten_image'] ?? $featuredPost['featured_image']); ?>"
                                             alt="<?php echo htmlspecialchars($featuredPost['title']); ?>"
                                             class="<?php echo $fIsHandwritten ? 'object-contain' : 'object-cover'; ?>">
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="h-2" style="background: linear-gradient(90deg,#4A1942,#D4A017);"></div>
                            <?php endif; ?>
                            
                            <div class="p-6 md:p-8">
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?php echo $fPillClass; ?>">
                                        <?php if ($fIsVideo): ?>🎥 <?php endif; ?>FEATURED · <?php echo htmlspecialchars($featuredPost['category_name']); ?>
                                    </span>
                                    <span class="text-gray-400 text-xs font-century"><?php echo formatPostDate($featuredPost); ?></span>
                                </div>

                                <h2 class="font-montserrat font-bold text-plum text-2xl md:text-3xl mb-3 leading-snug">
                                    <a href="piece-single.php?slug=<?php echo htmlspecialchars($featuredPost['slug']); ?>" class="hover:text-gold transition-colors">
                                        <?php echo htmlspecialchars($featuredPost['title']); ?>
                                    </a>
                                </h2>

                                <p class="text-charcoal text-sm md:text-base leading-relaxed mb-5 line-clamp-3">
                                    <?php echo formatExcerpt($featuredPost, 220); ?>
                                </p>
                                
                                <?php if ($fIsVideo): ?>
                                    <!-- Like & View Bar for Featured Video -->
                                    <div class="mb-5 flex items-center gap-4 text-sm">
                                        <button 
                                            class="video-like-btn inline-flex items-center gap-2 px-4 py-2 rounded-full border transition-all font-semibold"
                                            data-post-id="<?php echo (int)$featuredPost['id']; ?>"
                                            data-likes="<?php echo (int)($featuredPost['likes'] ?? 0); ?>"
                                            style="border-color: #D4A017; color: #4A1942; background: rgba(212,160,23,0.05);"
                                        >
                                            <span class="like-icon">♡</span>
                                            <span class="like-text">Did this bless you?</span>
                                            <span class="like-count font-bold" style="color: #D4A017;"><?php echo (int)($featuredPost['likes'] ?? 0); ?></span>
                                        </button>
                                        
                                        <?php if (!empty($featuredPost['video_views']) && $featuredPost['video_views'] > 0): ?>
                                            <span class="flex items-center gap-1.5 text-gray-600">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span><?php echo formatVideoViews($featuredPost['video_views']); ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <a href="piece-single.php?slug=<?php echo htmlspecialchars($featuredPost['slug']); ?>" class="btn-gold">
                                    <?php if ($fIsVideo): ?>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                        Watch Video Now
                                    <?php else: ?>
                                        Read Full Piece
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0-7.5 7.5M21 12H3"></path>
                                        </svg>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </article>
                        <?php endif; ?>

                        <!-- Remaining posts grid -->
                        <?php if (!empty($posts)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <?php foreach ($posts as $index => $post):
                                $pillClass = getCategoryBadgeClass($post['category_slug']);
                                $isVideo = !empty($post['video_file']) && ($post['content_type'] ?? '') === 'video';
                            ?>
                                <?php if ($isVideo): ?>
                                    <!-- Video Card -->
                                    <div style="transition-delay: <?php echo ($index % 3) * 0.07; ?>s;">
                                        <?php renderVideoCard($post, ['base_path' => '', 'card_style' => 'blog-card']); ?>
                                    </div>
                                <?php else: ?>
                                    <!-- Written Content Card -->
                                    <?php 
                                    $isHandwritten = !empty($post['handwritten_image']);
                                    ?>
                                <article class="blog-card category-<?php echo htmlspecialchars($post['category_slug']); ?> reveal active" style="transition-delay: <?php echo ($index % 3) * 0.07; ?>s;">
                                    <?php if ($post['handwritten_image'] || $post['featured_image']): ?>
                                        <!-- Image content -->
                                        <div class="blog-card-img h-48 <?php echo $isHandwritten ? 'is-handwritten' : ''; ?>">
                                            <a href="piece-single.php?slug=<?php echo htmlspecialchars($post['slug']); ?>">
                                                <img src="<?php echo basePath() . ($post['handwritten_image'] ?? $post['featured_image']); ?>"
                                                     alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                     class="w-full h-full <?php echo $isHandwritten ? 'object-contain' : 'object-cover'; ?>">
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <!-- Fallback for content without media -->
                                        <div class="h-48 bg-gradient-to-br from-plum to-gold flex items-center justify-center">
                                            <span class="text-4xl opacity-30">📝</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-6 flex flex-col flex-1">
                                        <span class="inline-block self-start px-3 py-1 rounded-full text-xs font-semibold mb-3 <?php echo $pillClass; ?>">
                                            <?php echo htmlspecialchars($post['category_name']); ?>
                                        </span>

                                        <h3 class="font-montserrat font-bold text-plum text-lg mb-2 line-clamp-2">
                                            <a href="piece-single.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="hover:text-gold transition-colors">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h3>

                                        <p class="excerpt text-charcoal text-sm font-century leading-relaxed mb-4 line-clamp-2 flex-1">
                                            <?php echo formatExcerpt($post, 120); ?>
                                        </p>

                                        <div class="pt-3 border-t border-gold/20 flex items-center justify-between">
                                            <span class="text-gray-400 text-xs font-century"><?php echo formatPostDate($post); ?></span>
                                            <a href="piece-single.php?slug=<?php echo htmlspecialchars($post['slug']); ?>"
                                               class="inline-flex items-center gap-1 text-xs font-bold text-gold hover:text-plum transition-colors font-century">
                                                Read
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0-7.5 7.5M21 12H3"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <?php
                            $baseQ = [];
                            if ($categorySlug) $baseQ[] = 'category=' . urlencode($categorySlug);
                            if ($contentType)  $baseQ[] = 'type=' . urlencode($contentType);
                            if ($search)       $baseQ[] = 'q=' . urlencode($search);
                            $baseUrl = 'body-of-work.php' . ($baseQ ? '?' . implode('&', $baseQ) . '&' : '?');
                            ?>
                            <div class="flex items-center justify-center gap-2 mt-10 reveal active">
                                <?php if ($currentPage > 1): ?>
                                    <a href="<?php echo $baseUrl; ?>page=<?php echo $currentPage - 1; ?>" class="page-btn">‹</a>
                                <?php endif; ?>
                                <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                                    <a href="<?php echo $baseUrl; ?>page=<?php echo $p; ?>" class="page-btn <?php echo $p === $currentPage ? 'active' : ''; ?>"><?php echo $p; ?></a>
                                <?php endfor; ?>
                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="<?php echo $baseUrl; ?>page=<?php echo $currentPage + 1; ?>" class="page-btn">›</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="rounded-2xl p-10 text-center reveal active" style="background: rgba(212,160,23,.08); border: 2px dashed rgba(212,160,23,.35);">
                            <svg class="w-14 h-14 text-gold/60 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            <h2 class="text-lg font-bold text-plum font-montserrat mb-2">Nothing here yet</h2>
                            <p class="text-gray-500 text-sm font-century mb-4">
                                <?php echo ($categorySlug || $search) ? 'Try a different filter or search term.' : 'No posts published yet — check back soon.'; ?>
                            </p>
                            <?php if ($categorySlug || $search): ?>
                                <a href="body-of-work.php" class="btn-plum">View All Writing</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- SIDEBAR: 1 column -->
                <aside class="space-y-6">
                    <!-- Categories Widget -->
                    <div class="sidebar-card reveal active sticky top-24">
                        <h3 class="section-label mb-5 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Categories
                        </h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="body-of-work.php" class="flex items-center justify-between text-sm font-semibold hover:text-gold transition-colors font-century <?php echo !$categorySlug ? 'text-gold' : 'text-plum'; ?>">
                                    All Writing
                                    <span class="text-xs opacity-50"><?php echo $totalPosts; ?></span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a href="?category=<?php echo urlencode($cat['slug']); ?>"
                                       class="flex items-center justify-between text-sm font-semibold hover:text-gold transition-colors font-century <?php echo $categorySlug == $cat['slug'] ? 'text-gold' : 'text-plum'; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                        <span class="text-xs opacity-50">→</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Recent Posts Widget -->
                    <?php if (!empty($recentPosts)): ?>
                    <div class="sidebar-card reveal active">
                        <h3 class="section-label mb-5 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Recent Pieces
                        </h3>
                        <ul class="space-y-4">
                            <?php foreach ($recentPosts as $rp): ?>
                                <li>
                                    <a href="piece-single.php?slug=<?php echo htmlspecialchars($rp['slug']); ?>" class="group block">
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-gold bg-opacity-10 text-gold font-semibold inline-block mb-1">
                                            <?php echo htmlspecialchars($rp['category_name']); ?>
                                        </span>
                                        <p class="text-sm font-bold leading-snug group-hover:text-gold transition-colors text-plum font-montserrat line-clamp-2">
                                            <?php echo htmlspecialchars($rp['title']); ?>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1 font-century"><?php echo formatPostDate($rp); ?></p>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Newsletter CTA -->
                    <div class="newsletter-card reveal active">
                        <h3 class="text-sm font-bold text-cream mb-1 font-montserrat">Stay Encouraged</h3>
                        <p class="text-xs mb-4 font-century" style="color: rgba(255, 255, 255, 0.75);">
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

    <!-- BOTTOM CTA -->
    <section class="max-w-7xl mx-auto px-4 pb-16">
        <div class="cta-bottom md:flex md:items-center md:justify-between gap-8 reveal active">
            <div>
                <h2 class="text-2xl font-black text-cream mb-1 font-montserrat">Want to know Naomi's heart more?</h2>
                <p class="text-sm max-w-xl font-century" style="color: rgba(255,255,255,.75);">
                    Read her story, ministry heart, and where the words come from.
                </p>
            </div>
            <div class="flex-shrink-0 mt-4 md:mt-0">
                <a href="about.php" class="btn-gold">About Naomi →</a>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>

    <script>
        function revealOnScroll() {
            document.querySelectorAll('.reveal').forEach(function (element) {
                const elementTop = element.getBoundingClientRect().top;
                const elementBottom = element.getBoundingClientRect().bottom;
                const windowHeight = window.innerHeight;
                if (elementTop < windowHeight * 0.9 && elementBottom > 0) {
                    element.classList.add('active');
                }
            });
        }
        window.addEventListener('load', function () {
            revealOnScroll();
            setTimeout(function () {
                document.querySelectorAll('.fade-in-up').forEach(function (el) { el.style.opacity = '1'; });
            }, 100);
        });
        window.addEventListener('scroll', revealOnScroll);
    </script>
</body>
</html>
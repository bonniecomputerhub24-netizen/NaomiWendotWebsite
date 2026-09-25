<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/post-functions.php';
require_once __DIR__ . '/../includes/video-functions.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';

// Fetch post from database
$post = getPostBySlug($slug);

$allCategories = getCategories();

if (!$post) {
    $pageTitle = "Piece Not Found — Naomi Wendot";
    $metaDescription = "This writing piece may have been moved or removed.";
    $relatedPosts = [];
} else {
    $pageTitle = htmlspecialchars($post['title']) . " — Naomi Wendot";
    $metaDescription = formatExcerpt($post, 160);

    $relatedPosts = getLatestPosts(4, $post['category_slug']);
    $relatedPosts = array_filter($relatedPosts, function ($p) use ($post) {
        return $p['id'] !== $post['id'];
    });
    $relatedPosts = array_slice($relatedPosts, 0, 3);

    $wordCount     = str_word_count(strip_tags($post['content']));
    $readTime      = max(1, ceil($wordCount / 200));
    $isHandwritten = !empty($post['handwritten_image']);
    $isVideo       = (($post['content_type'] ?? '') === 'video') && !empty($post['video_file']);
    
    // Debug: Uncomment to see post data
    // echo "<pre>DEBUG POST DATA:\n"; print_r($post); echo "</pre>"; exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <title><?php echo $pageTitle; ?></title>

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
        #reading-progress { position: fixed; top: 0; left: 0; width: 0%; height: 4px; background: linear-gradient(90deg, #D4A017, #4A1942); z-index: 9999; transition: width 0.1s ease-out; }

        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.active { opacity: 1; transform: translateY(0); }

        .section-label { font-size: .7rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; color: #D4A017; }
        .gold-bar { height: 3px; border-radius: 9999px; background: #D4A017; }
        .gold-divider { height: 3px; background: linear-gradient(90deg, transparent, #D4A017, transparent); border: none; margin: 0; }

        .btn-gold { display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.65rem 1.6rem;border-radius:9999px;background:#D4A017;color:#4A1942;font-weight:700;font-size:.8rem;transition:all .2s;box-shadow:0 3px 12px rgba(212,160,23,.3); }
        .btn-gold:hover { background:#e0b13a; transform: translateY(-1px); }
        .btn-plum { display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.65rem 1.6rem;border-radius:9999px;background:#4A1942;color:#fff;font-weight:700;font-size:.8rem;transition:all .2s;box-shadow:0 3px 12px rgba(74,25,66,.25); }
        .btn-plum:hover { background:#5A2952; transform: translateY(-1px); }

        .cat-badge { display:inline-block;padding:.3rem .8rem;border-radius:9999px;font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;background:rgba(212,160,23,.15);color:#D4A017;border:1px solid rgba(212,160,23,.3); }

        /* Video display */
        .video-wrap { border-radius: 1.5rem; overflow: hidden; box-shadow: 0 10px 40px rgba(74,25,66,.18); position: relative; background: #000; max-width: 100%; }
        .video-wrap iframe, .video-wrap video { width: 100%; height: auto; min-height: 420px; display: block; border-radius: 1.5rem; }
        @media (max-width: 768px) { .video-wrap iframe, .video-wrap video { min-height: 280px; } }
        .video-duration { background: rgba(0,0,0,0.8); color: white; padding: 0.25rem 0.6rem; border-radius: 0.35rem; font-size: 0.75rem; font-weight: 600; }
        
        /* Featured image */
        .featured-img-wrap { border-radius: 1.5rem; overflow: hidden; box-shadow: 0 10px 40px rgba(74,25,66,.18); position: relative; }
        .featured-img-wrap img { width: 100%; height: 420px; object-fit: cover; display: block; }
        .featured-img-gradient { position: absolute; inset: 0; background: linear-gradient(to top, rgba(28,28,28,.35), transparent 50%); }
        /* Portrait / handwritten images: letterbox instead of forcing a landscape crop */
        .featured-img-wrap.is-handwritten { background: #FDEAEA; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .featured-img-wrap.is-handwritten img { width: auto; height: auto; max-height: 640px; max-width: 100%; margin: 0 auto; }
        .thumb-handwritten { background: #FDEAEA; display: flex; align-items: center; justify-content: center; }
        .thumb-handwritten img { width: auto; height: 100%; max-width: 100%; object-fit: contain; }

        /* Article card */
        .article-card { background: #fff; border-radius: 1.5rem; padding: 2rem 2.5rem; box-shadow: 0 4px 24px rgba(74,25,66,.08); border-top: 4px solid #D4A017; }
        @media (max-width: 640px) { .article-card { padding: 1.5rem 1.25rem; } }

        .prose-content h2, .prose-content h3 { margin-top: 2rem; margin-bottom: 1rem; font-family: 'Montserrat', sans-serif; font-weight: 700; color: #4A1942; }
        .prose-content p { margin-bottom: 1.5rem; line-height: 1.8; }
        .prose-content blockquote { border-left: 4px solid #D4A017; padding: .85rem 1.25rem; background: rgba(212,160,23,.07); border-radius: 0 .75rem .75rem 0; margin: 1.75rem 0; font-style: italic; color: #4B5563; }
        .prose-content img { max-width: 100%; border-radius: .75rem; margin: 1.25rem 0; }
        .prose-content a { color: #D4A017; font-weight: 600; }

        /* Share pills */
        .share-pill { display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;border-radius:9999px;font-size:.7rem;font-weight:700;border:1.5px solid rgba(74,25,66,.15);color:#4A1942;transition:all .2s;cursor:pointer;background:#fff; }
        .share-pill:hover { background:#4A1942;color:#fff;border-color:#4A1942;transform:translateY(-2px);box-shadow:0 4px 12px rgba(74,25,66,.2); }
        .share-pill.copy-social { border-color:rgba(212,160,23,.5);color:#D4A017;background:rgba(212,160,23,.05); }
        .share-pill.copy-social:hover { background:#D4A017;color:#4A1942;border-color:#D4A017; }
        .share-section { background:linear-gradient(135deg,rgba(253,234,234,.8),rgba(255,253,245,.8));border-radius:1.25rem;padding:1.5rem;border:2px dashed rgba(212,160,23,.25); }

        /* Author card */
        .author-card { background: linear-gradient(135deg, #4A1942 0%, #5A2952 100%); border-radius: 1.25rem; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; }
        .author-avatar { width: 4.5rem; height: 4.5rem; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid rgba(212,160,23,.5); }

        /* Sidebar */
        .sidebar-card { background: #fff; border-radius: 1rem; padding: 1.4rem; box-shadow: 0 3px 16px rgba(74,25,66,.07); border-top: 3px solid #D4A017; }
        .newsletter-card { border-radius: 1rem; padding: 1.4rem; color: #fff; background: linear-gradient(135deg,#4A1942 0%,#5A2952 100%); }
        .nl-input { width: 100%; padding: .6rem .85rem; border-radius: .5rem; font-size: .8rem; outline: none; background: rgba(255,255,255,.12); color: #fff; border: 1px solid rgba(255,255,255,.2); }
        .nl-input::placeholder { color: rgba(255,255,255,.55); }
        .nl-btn { width: 100%; padding: .6rem; border-radius: .5rem; background: #D4A017; color: #4A1942; font-weight: 700; font-size: .8rem; border: none; cursor: pointer; transition: opacity .2s; }
        .nl-btn:hover { opacity: .87; }

        /* Related cards */
        .related-card { background: #fff; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 3px 16px rgba(74,25,66,.08); border-bottom: 3px solid transparent; transition: transform .25s, box-shadow .25s, border-color .25s; }
        .related-card:hover { transform: translateY(-5px); box-shadow: 0 12px 36px rgba(74,25,66,.14); border-bottom-color: #D4A017; }
        .related-card-img img { transition: transform .5s ease; }
        .related-card:hover .related-card-img img { transform: scale(1.06); }

        /* Parallax bottom CTA */
        .parallax-section { background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; }
        .parallax-overlay { background: linear-gradient(135deg, rgba(74,25,66,.85) 0%, rgba(28,28,28,.65) 100%); }
        .cta-box { background: rgba(255,255,255,.97); border-radius: 1.5rem; border: 1px solid rgba(212,160,23,.3); box-shadow: 0 8px 40px rgba(74,25,66,.18); padding: 2.5rem 2rem; text-align: center; }

        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="font-century bg-cream">
    <!-- Reading Progress Bar -->
    <div id="reading-progress"></div>

    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <?php if (!$post): ?>
        <!-- POST NOT FOUND -->
        <section class="min-h-screen bg-cream py-20 px-4">
            <div class="max-w-3xl mx-auto text-center">
                <p class="text-3xl mb-3">📝</p>
                <h1 class="text-3xl font-bold text-plum font-montserrat mb-4">Piece Not Found</h1>
                <p class="text-gray-600 text-lg mb-8 font-century">This writing piece may have been moved or removed.</p>
                <a href="body-of-work.php" class="btn-plum">← Back to Body of Work</a>
            </div>
        </section>

    <?php else: ?>

        <!-- HERO (slim) -->
        <section class="bg-gradient-to-br from-plum to-purple-900 py-6 md:py-8 px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-sm mb-3 text-center reveal active">
                    <a href="index.php" class="text-gold hover:underline font-century">Home</a>
                    <span class="text-cream text-opacity-60 mx-2">›</span>
                    <a href="body-of-work.php" class="text-gold hover:underline font-century">Body of Work</a>
                    <span class="text-cream text-opacity-60 mx-2">›</span>
                    <span class="text-cream font-bold font-century"><?php echo htmlspecialchars($post['category_name']); ?></span>
                </div>

                <div class="flex justify-center reveal active">
                    <span class="cat-badge" style="background:#D4A017;color:#4A1942;border-color:#D4A017;">
                        <?php echo htmlspecialchars($post['category_name']); ?>
                    </span>
                </div>
            </div>
        </section>

        <!-- FEATURED MEDIA (Image/Video) -->
        <?php if ($isVideo): ?>
            <!-- VIDEO CONTENT -->
            <section class="bg-cream py-8 px-4 reveal active">
                <div class="max-w-4xl mx-auto">
                    <div class="video-wrap">
                        <?php echo generateVideoEmbed($post['video_file'], getVideoPoster($post), $post['video_duration'], ['responsive' => true, 'autoplay' => false, 'controls' => true, 'preload' => 'auto', 'show_duration' => false]); ?>
                    </div>
                    
                    <!-- Like & View Bar for Video Player -->
                    <div class="mt-4 flex items-center justify-center gap-6 text-sm">
                        <button 
                            class="video-like-btn inline-flex items-center gap-2 px-4 py-2 rounded-full border transition-all font-semibold"
                            data-post-id="<?php echo (int)$post['id']; ?>"
                            data-likes="<?php echo (int)($post['likes'] ?? 0); ?>"
                            style="border-color: #D4A017; color: #4A1942; background: rgba(212,160,23,0.05);"
                        >
                            <span class="like-icon">♡</span>
                            <span class="like-text">Did this bless you?</span>
                            <span class="like-count font-bold" style="color: #D4A017;"><?php echo (int)($post['likes'] ?? 0); ?></span>
                        </button>
                        
                        <?php if (!empty($post['video_views']) && $post['video_views'] > 0): ?>
                            <span class="flex items-center gap-1.5 text-gray-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <span><?php echo formatVideoViews($post['video_views']); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php elseif ($post['handwritten_image'] || $post['featured_image']): ?>
            <!-- IMAGE CONTENT -->
            <section class="bg-cream py-8 px-4 reveal active">
                <div class="max-w-4xl mx-auto">
                    <div class="featured-img-wrap <?php echo $isHandwritten ? 'is-handwritten' : ''; ?>">
                        <img src="<?php echo basePath() . ($post['handwritten_image'] ?? $post['featured_image']); ?>"
                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                             class="<?php echo $isHandwritten ? 'object-contain' : 'object-cover'; ?>">
                        <?php if (!$isHandwritten): ?><div class="featured-img-gradient"></div><?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- MAIN CONTENT + SIDEBAR -->
        <section class="bg-cream py-8 md:py-12 px-4" id="article-content">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">

                    <!-- Article -->
                    <article class="lg:col-span-3 space-y-6 reveal active category-<?php echo htmlspecialchars($post['category_slug']); ?>" id="article-body">

                        <div class="article-card">
                            <h1 class="handwriting-title font-artistic font-bold text-plum text-2xl md:text-3xl lg:text-4xl leading-tight mb-4">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </h1>

                            <div class="flex flex-wrap items-center gap-3 mb-2 text-xs text-gray-400 font-century">
                                <span class="cat-badge"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                <span><?php echo formatPostDate($post); ?></span>
                                <span>•</span>
                                <span>By Naomi Wendot</span>
                                <span>•</span>
                                <span><?php echo $readTime; ?> min read</span>
                                <span class="ml-auto"><?php echo $post['views']; ?> views</span>
                            </div>
                            <div class="gold-bar w-14 my-4"></div>

                            <div class="prose-content text-charcoal 
                                <?php 
                                // Apply different handwriting styles based on category
                                if ($post['category_slug'] === 'poems') {
                                    echo 'poetry font-signature';
                                } elseif ($post['category_slug'] === 'stories') {
                                    echo 'font-signature';
                                } elseif ($post['category_slug'] === 'daily-inspirations') {
                                    echo 'category-daily-inspirations font-handwriting';
                                } else {
                                    echo 'font-handwriting';
                                }
                                ?>">
                                <?php echo $post['content']; ?>
                            </div>
                        </div>

                        <!-- Like Section -->
                        <div class="text-center py-2">
                            <button id="like-btn" onclick="likePost('<?php echo htmlspecialchars($post['slug']); ?>')"
                                    class="inline-flex items-center gap-3 px-8 py-4 bg-white border-2 border-gold rounded-full font-semibold hover:bg-gold hover:text-white transition-all shadow-md font-century group">
                                <svg id="heart-icon" class="w-6 h-6 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span id="like-text">Did this bless you?</span>
                                <span id="like-count" class="px-3 py-1 bg-gold text-white rounded-full text-sm group-hover:bg-white group-hover:text-gold transition-all">
                                    <?php echo isset($post['likes']) ? $post['likes'] : 0; ?>
                                </span>
                            </button>
                            <p id="like-feedback" class="text-gold font-semibold mt-3 hidden font-century">❤️ Thank you for the love!</p>
                        </div>

                        <!-- Enhanced Share Section -->
                        <div class="share-section reveal">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                <span class="text-sm font-bold text-plum font-montserrat">Share this piece with others</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button onclick="copyForSocial(this)" class="share-pill copy-social" title="Copy formatted text ready for social media">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    Copy for Social
                                </button>
                                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title'] . ' by Naomi Wendot'); ?>&url=<?php echo urlencode('https://naomiwendot.com/public/piece-single?slug=' . $post['slug']); ?>"
                                   target="_blank" rel="noopener" class="share-pill" title="Share on X (Twitter)">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                                    </svg>
                                    𝕏 Twitter
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://naomiwendot.com/public/piece-single?slug=' . $post['slug']); ?>"
                                   target="_blank" rel="noopener" class="share-pill" title="Share on Facebook">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                                    </svg>
                                    Facebook
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://naomiwendot.com/public/piece-single?slug=' . $post['slug']); ?>"
                                   target="_blank" rel="noopener" class="share-pill" title="Share on LinkedIn">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"></path>
                                    </svg>
                                    LinkedIn
                                </a>
                                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($post['title'] . ' by Naomi Wendot - https://naomiwendot.com/public/piece-single?slug=' . $post['slug']); ?>"
                                   target="_blank" rel="noopener" class="share-pill" title="Share via WhatsApp">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                                    </svg>
                                    WhatsApp
                                </a>
                                <button onclick="copyLink()" class="share-pill" title="Copy link to clipboard">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    Copy Link
                                </button>
                            </div>
                            <p id="copy-feedback" class="text-green-600 font-semibold text-xs hidden font-century mt-3 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span id="copy-feedback-text">Link copied to clipboard!</span>
                            </p>
                        </div>

                        <!-- Author card -->
                        <div class="author-card">
                            <img src="<?php echo basePath(); ?>assets/images/NaomiWendotProfileImage.png"
                                 alt="Naomi Wendot" class="author-avatar">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-cream text-opacity-60 mb-0.5 font-century">Written by</p>
                                <p class="font-bold text-lg text-gold font-montserrat">Naomi Wendot</p>
                                <p class="text-cream text-opacity-85 text-sm font-century mt-1">
                                    Writer, Speaker &amp; Ministry Leader passionate about sharing God's word through poetry, stories, and daily inspirations.
                                </p>
                                <a href="about.php" class="inline-block mt-3 text-xs font-bold text-gold hover:underline font-century">
                                    Learn More →
                                </a>
                            </div>
                        </div>

                        <!-- Back / CTA -->
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="body-of-work.php" class="btn-plum">← Back to Body of Work</a>
                            <a href="body-of-work.php?category=<?php echo urlencode($post['category_slug']); ?>" class="btn-gold">
                                More in <?php echo htmlspecialchars($post['category_name']); ?>
                            </a>
                        </div>

                    </article>

                    <!-- Sidebar -->
                    <aside class="space-y-5">

                        <!-- Categories -->
                        <?php if (!empty($allCategories)): ?>
                        <div class="sidebar-card reveal active">
                            <h3 class="section-label mb-4">Categories</h3>
                            <ul class="space-y-2">
                                <li><a href="body-of-work.php" class="text-xs font-semibold text-plum hover:text-gold transition-colors font-century">All Writing</a></li>
                                <?php foreach ($allCategories as $cat): ?>
                                <li>
                                    <a href="body-of-work.php?category=<?php echo urlencode($cat['slug']); ?>"
                                       class="flex items-center justify-between text-xs font-semibold hover:text-gold transition-colors font-century <?php echo $post['category_slug'] === $cat['slug'] ? 'text-gold' : 'text-plum'; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                        <?php if ($post['category_slug'] === $cat['slug']): ?><span>→</span><?php endif; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Related articles -->
                        <?php if (!empty($relatedPosts)): ?>
                        <div class="sidebar-card reveal active">
                            <h3 class="section-label mb-4">Related Pieces</h3>
                            <ul class="space-y-3">
                                <?php foreach ($relatedPosts as $rp): ?>
                                <li>
                                    <a href="piece-single.php?slug=<?php echo htmlspecialchars($rp['slug']); ?>" class="flex gap-3 group items-start">
                                        <?php if ($rp['handwritten_image'] || $rp['featured_image']):
                                            $rpIsHandwritten = !empty($rp['handwritten_image']);
                                        ?>
                                            <div class="w-14 h-14 rounded-lg flex-shrink-0 overflow-hidden <?php echo $rpIsHandwritten ? 'thumb-handwritten' : ''; ?>">
                                                <img src="<?php echo basePath() . ($rp['handwritten_image'] ?? $rp['featured_image']); ?>"
                                                     alt="<?php echo htmlspecialchars($rp['title']); ?>"
                                                     class="w-14 h-14 <?php echo $rpIsHandwritten ? 'object-contain' : 'object-cover'; ?> group-hover:opacity-80 transition-opacity">
                                            </div>
                                        <?php else: ?>
                                            <div class="w-14 h-14 rounded-lg flex-shrink-0 flex items-center justify-center text-lg bg-gradient-to-br from-plum to-gold">📝</div>
                                        <?php endif; ?>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-gold"><?php echo htmlspecialchars($rp['category_name']); ?></span>
                                            <p class="text-xs font-semibold leading-snug text-plum group-hover:text-gold transition-colors font-century line-clamp-2"><?php echo htmlspecialchars($rp['title']); ?></p>
                                            <p class="text-[11px] text-gray-400 mt-0.5 font-century"><?php echo formatPostDate($rp); ?></p>
                                        </div>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Newsletter -->
                        <div class="newsletter-card reveal active">
                            <h3 class="text-sm font-bold mb-1 font-montserrat">Stay Encouraged</h3>
                            <p class="text-xs mb-4 font-century" style="color: rgba(255,255,255,.75);">Get new poems, daily inspirations, and stories straight to your inbox.</p>
                            <?php $context = 'piece-single'; include __DIR__ . '/../includes/newsletter-signup.php'; ?>
                        </div>

                    </aside>
                </div>
            </div>
        </section>

        <!-- RELATED POSTS GRID -->
        <?php if (!empty($relatedPosts)): ?>
        <section class="bg-rose py-16 px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-10 reveal active">
                    <p class="section-label mb-2">Keep Reading</p>
                    <h2 class="text-2xl md:text-3xl font-bold text-plum font-montserrat">More from <?php echo htmlspecialchars($post['category_name']); ?></h2>
                    <div class="gold-bar w-14 mx-auto mt-3"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($relatedPosts as $index => $related): ?>
                        <article class="related-card reveal active" style="transition-delay: <?php echo $index * 0.1; ?>s;">
                            <?php if ($related['handwritten_image'] || $related['featured_image']):
                                $relIsHandwritten = !empty($related['handwritten_image']);
                            ?>
                                <a href="piece-single.php?slug=<?php echo htmlspecialchars($related['slug']); ?>"
                                   class="related-card-img block h-48 overflow-hidden <?php echo $relIsHandwritten ? 'thumb-handwritten' : ''; ?>">
                                    <img src="<?php echo basePath() . ($related['handwritten_image'] ?? $related['featured_image']); ?>"
                                         alt="<?php echo htmlspecialchars($related['title']); ?>"
                                         class="w-full h-48 <?php echo $relIsHandwritten ? 'object-contain' : 'object-cover'; ?>">
                                </a>
                            <?php else: ?>
                                <div class="h-48 bg-gradient-to-br from-plum to-gold flex items-center justify-center">
                                    <span class="text-4xl opacity-30">📝</span>
                                </div>
                            <?php endif; ?>
                            <div class="p-6">
                                <span class="cat-badge mb-2 inline-block"><?php echo htmlspecialchars($related['category_name']); ?></span>
                                <h3 class="font-montserrat font-bold text-plum text-lg mb-2 line-clamp-2">
                                    <a href="piece-single.php?slug=<?php echo htmlspecialchars($related['slug']); ?>" class="hover:text-gold transition-colors">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2 font-century"><?php echo formatExcerpt($related, 100); ?></p>
                                <a href="piece-single.php?slug=<?php echo htmlspecialchars($related['slug']); ?>" class="text-gold text-sm font-semibold hover:underline font-century">Read More →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- BOTTOM PARALLAX CTA -->
        <section class="px-4 py-16">
            <div class="max-w-6xl mx-auto relative parallax-section rounded-3xl overflow-hidden reveal active"
                 style="background-image: url('<?php echo basePath(); ?>assets/images/Archives2.png');">
                <div class="parallax-overlay absolute inset-0 rounded-3xl"></div>
                <div class="relative max-w-3xl mx-auto px-6 py-16">
                    <div class="cta-box">
                        <p class="section-label mb-2">Stay Encouraged</p>
                        <h2 class="text-2xl md:text-3xl font-black mb-3 font-montserrat text-plum">Get New Writing In Your Inbox</h2>
                        <div class="h-0.5 w-12 rounded mx-auto mb-5" style="background:#D4A017;"></div>
                        <p class="text-gray-500 text-sm mb-7 max-w-xl mx-auto leading-relaxed font-century">
                            Poems, daily inspirations, and stories — sent straight to you as they're written.
                        </p>
                        <?php $context = 'piece-single-bottom'; include __DIR__ . '/../includes/newsletter-signup.php'; ?>
                    </div>
                </div>
            </div>
        </section>

    <?php endif; ?>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>

    <script>
        // Reading Progress Bar
        window.addEventListener('scroll', function () {
            const article = document.getElementById('article-body');
            if (!article) return;

            const rect = article.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const articleTop = article.offsetTop;
            const articleHeight = article.offsetHeight;
            const scrolled = window.pageYOffset;

            const start = articleTop - windowHeight / 2;
            const end = articleTop + articleHeight - windowHeight / 2;
            const progress = Math.min(Math.max((scrolled - start) / (end - start), 0), 1);

            document.getElementById('reading-progress').style.width = (progress * 100) + '%';
        });

        // Reveal animations on scroll
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
        window.addEventListener('load', revealOnScroll);
        window.addEventListener('scroll', revealOnScroll);

        // Copy link
        function copyLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function () {
                const feedback = document.getElementById('copy-feedback');
                const feedbackText = document.getElementById('copy-feedback-text');
                feedbackText.textContent = '✓ Link copied to clipboard!';
                feedback.classList.remove('hidden');
                setTimeout(function () { feedback.classList.add('hidden'); }, 3000);
            }).catch(function () {
                alert('Failed to copy link. Please copy manually: ' + url);
            });
        }

        // Copy formatted text for social media - enhanced version
        function copyForSocial(btn) {
            const titleEl = document.querySelector('#article-body h1');
            const prose = document.querySelector('.prose-content');
            if (!prose) return;

            const titleText = titleEl ? titleEl.innerText.trim() : '';
            let bodyText = '';

            // Extract content preserving structure
            prose.querySelectorAll('h2, h3, h4, p, li, blockquote').forEach(el => {
                const text = el.innerText.trim();
                if (!text) return;
                
                if (el.tagName === 'LI') {
                    bodyText += '• ' + text + '\n';
                } else if (el.tagName === 'H2' || el.tagName === 'H3') {
                    bodyText += '\n' + text.toUpperCase() + '\n\n';
                } else if (el.tagName === 'BLOCKQUOTE') {
                    bodyText += '\n"' + text + '"\n\n';
                } else {
                    bodyText += text + '\n\n';
                }
            });

            // Build final formatted text
            const separator = '✨'.repeat(10);
            const fullText = `${separator}\n\n${titleText}\n\nby Naomi Wendot\n\n${separator}\n\n${bodyText.trim()}\n\n${separator}\n\nRead more at:\n${window.location.href}\n\n#NaomiWendot #Faith #Inspiration #Christian #Writer`;

            // Copy to clipboard
            navigator.clipboard.writeText(fullText).then(() => {
                const originalText = btn.textContent;
                const feedback = document.getElementById('copy-feedback');
                const feedbackText = document.getElementById('copy-feedback-text');
                
                btn.textContent = '✓ Copied!';
                btn.style.background = '#22c55e';
                btn.style.borderColor = '#22c55e';
                btn.style.color = '#fff';
                
                feedbackText.textContent = '✓ Formatted text copied! Ready to paste on Facebook, WhatsApp Channel, LinkedIn, or Instagram.';
                feedback.classList.remove('hidden');
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '';
                    btn.style.borderColor = '';
                    btn.style.color = '';
                    feedback.classList.add('hidden');
                }, 3500);
            }).catch(() => {
                alert('Could not copy automatically. Please select and copy the article text manually.');
            });
        }

        // Like post
        function likePost(slug) {
            const btn = document.getElementById('like-btn');
            const icon = document.getElementById('heart-icon');
            const text = document.getElementById('like-text');
            const count = document.getElementById('like-count');
            const feedback = document.getElementById('like-feedback');

            const likedKey = 'liked_post_' + slug;
            if (localStorage.getItem(likedKey)) {
                feedback.textContent = '❤️ You already liked this!';
                feedback.classList.remove('hidden');
                setTimeout(function () { feedback.classList.add('hidden'); }, 3000);
                return;
            }

            btn.disabled = true;

            fetch('<?php echo basePath(); ?>api/like-post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ slug: slug })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        icon.setAttribute('fill', 'currentColor');
                        text.textContent = 'Blessed!';
                        count.textContent = data.likes;
                        localStorage.setItem(likedKey, 'true');
                        feedback.classList.remove('hidden');
                        setTimeout(function () { feedback.classList.add('hidden'); }, 3000);
                    } else {
                        alert('Failed to like post. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to like post. Please try again.');
                })
                .finally(function () { btn.disabled = false; });
        }

        window.addEventListener('load', function () {
            const slug = '<?php echo (isset($post) && is_array($post) && isset($post['slug'])) ? htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8') : ''; ?>';
            if (!slug) return;
            const likedKey = 'liked_post_' + slug;
            if (localStorage.getItem(likedKey)) {
                document.getElementById('heart-icon').setAttribute('fill', 'currentColor');
                document.getElementById('like-text').textContent = 'Blessed!';
            }
        });
    </script>
</body>
</html>
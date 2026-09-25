<?php
/**
 * Testimonies Page - Display all testimony content
 * Dedicated page for Naomi's testimonies
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/post-functions.php';

$pageTitle = 'Testimonies — Naomi Wendot';
$metaDescription = 'Read Naomi Wendot\'s testimonies — real stories of God\'s grace, protection, and answered prayer.';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;

// Search within testimonies
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Get testimonies with pagination
if ($search) {
    // If searching, use getAllPosts with category filter
    $categories = getCategories();
    $testimoniesCategoryId = null;
    foreach ($categories as $cat) {
        if ($cat['slug'] === 'testimonies') {
            $testimoniesCategoryId = (int)$cat['id'];
            break;
        }
    }
    $result = getAllPosts($search, $testimoniesCategoryId, $page, $perPage);
} else {
    // Standard category fetch with pagination
    $result = getLatestPosts(null, 'testimonies', $page, $perPage);
}

$testimonies = $result['posts'];
$totalPosts = $result['total'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
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
                        playfair: ['Playfair Display', 'serif'],
                        century: ['Century Gothic', 'CenturyGothic', 'AppleGothic', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo basePath(); ?>assets/css/custom.css">

    <style>
        /* Blog card styling for written content */
        .blog-card {
            background: #fff;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 3px 16px rgba(74,25,66,.08);
            transition: transform .25s, box-shadow .25s;
            display: flex;
            flex-direction: column;
        }
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 34px rgba(74,25,66,.14);
        }
        .blog-card-img {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #FDEAEA 0%, #F8E5E5 100%);
            aspect-ratio: 16/9;
        }
        .blog-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .5s ease;
        }
        .blog-card:hover .blog-card-img img {
            transform: scale(1.06);
        }
        .section-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: #D4A017;
        }
        .gold-bar {
            height: 3px;
            width: 3.5rem;
            border-radius: 9999px;
            background: #D4A017;
            margin: .6rem auto 0;
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="font-century bg-cream">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- HERO with background image -->
    <section class="relative h-[380px] flex items-center justify-center overflow-hidden">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0">
            <img 
                src="https://images.unsplash.com/photo-1502139214982-d0ad755818d8?w=1600&q=80" 
                alt="Light and gratitude" 
                class="w-full h-full object-cover"
            >
            <!-- Rose-plum gradient overlay with reduced opacity -->
            <div class="absolute inset-0 bg-gradient-to-br from-rose-900 via-pink-900 to-plum opacity-45"></div>
        </div>
        
        <!-- Pattern overlay -->
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        
        <div class="relative z-20 text-center px-4 max-w-4xl">
            <!-- Breadcrumb -->
            <div class="text-sm mb-6">
                <a href="<?php echo basePath(); ?>public/index.php" class="text-gold hover:underline font-century">Home</a>
                <span class="text-cream text-opacity-60 mx-2">›</span>
                <a href="<?php echo basePath(); ?>public/body-of-work.php" class="text-gold hover:underline font-century">Body of Work</a>
                <span class="text-cream text-opacity-60 mx-2">›</span>
                <span class="text-cream font-bold font-century">Testimonies</span>
            </div>

            <!-- Title -->
            <h1 class="font-playfair font-bold text-cream text-5xl md:text-6xl mb-4">
                Testimonies
            </h1>

            <!-- Tagline -->
            <p class="text-gold italic text-lg md:text-xl font-playfair max-w-2xl mx-auto">
                Real stories of God's grace, protection, and answered prayer
            </p>
        </div>
    </section>

    <!-- INTRO -->
    <section class="bg-cream pt-14 pb-6 px-4">
        <div class="max-w-7xl mx-auto text-center">
            <p class="section-label mb-2">Stories of Faithfulness</p>
            <p class="text-charcoal text-sm max-w-2xl mx-auto">
                Personal accounts of how God has moved, healed, provided, and answered in tangible ways.
            </p>
            <div class="gold-bar"></div>
        </div>
    </section>

    <!-- SEARCH BAR -->
    <section class="bg-cream pb-8 px-4">
        <div class="max-w-7xl mx-auto">
            <form method="get" action="" class="max-w-xl mx-auto">
                <div class="relative">
                    <input 
                        type="text" 
                        name="q" 
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search testimonies..." 
                        class="w-full px-4 py-3 pr-12 rounded-full border border-gray-300 focus:border-gold focus:outline-none focus:ring-2 focus:ring-gold text-sm"
                    >
                    <button 
                        type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-plum hover:text-gold transition-colors"
                        aria-label="Search"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
                <?php if ($search): ?>
                    <div class="text-center mt-3">
                        <span class="text-sm text-gray-600">Searching for: <strong>"<?php echo htmlspecialchars($search); ?>"</strong></span>
                        <a href="testimonies.php" class="ml-2 text-sm text-gold hover:underline">Clear search</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <!-- TESTIMONIES GRID -->
    <section class="bg-cream py-10 px-4">
        <div class="max-w-7xl mx-auto">
            <?php if (!empty($testimonies)): ?>
                <!-- Results count -->
                <div class="mb-6 text-center">
                    <p class="text-sm text-gray-600">
                        Showing <?php echo count($testimonies); ?> of <?php echo $totalPosts; ?> testimon<?php echo $totalPosts !== 1 ? 'ies' : 'y'; ?>
                        <?php if ($currentPage > 1): ?>
                            (Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>)
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    <?php foreach ($testimonies as $testimony): 
                        $isHandwritten = !empty($testimony['handwritten_image']);
                        $postUrl = basePath() . 'public/piece-single.php?slug=' . urlencode($testimony['slug']);
                    ?>
                        <article class="blog-card category-testimonies">
                            <!-- Image/Thumbnail -->
                            <?php if ($isHandwritten || $testimony['featured_image']): ?>
                                <div class="blog-card-img">
                                    <a href="<?php echo $postUrl; ?>">
                                        <img 
                                            src="<?php echo basePath() . ($testimony['handwritten_image'] ?? $testimony['featured_image']); ?>" 
                                            alt="<?php echo htmlspecialchars($testimony['title']); ?>"
                                            loading="lazy"
                                        >
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Content -->
                            <div class="p-6 flex flex-col flex-1">
                                <h3 class="font-playfair font-bold text-plum text-xl mb-2 line-clamp-2">
                                    <a href="<?php echo $postUrl; ?>" class="hover:text-gold transition-colors">
                                        <?php echo htmlspecialchars($testimony['title']); ?>
                                    </a>
                                </h3>

                                <?php if (!empty($testimony['excerpt'])): ?>
                                    <p class="text-charcoal text-sm font-century leading-relaxed mb-4 line-clamp-3 flex-1">
                                        <?php echo formatExcerpt($testimony, 150); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="pt-3 border-t border-gold/20 flex items-center justify-between">
                                    <span class="text-gray-400 text-xs font-century">
                                        <?php echo formatPostDate($testimony); ?>
                                    </span>
                                    <a href="<?php echo $postUrl; ?>"
                                       class="inline-flex items-center gap-1 text-xs font-bold text-gold hover:text-plum transition-colors font-century">
                                        Read
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0-7.5 7.5M21 12H3"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- PAGINATION -->
                <?php if ($totalPages > 1): ?>
                    <div class="flex justify-center items-center gap-2">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>" 
                               class="px-4 py-2 rounded-lg border border-gray-300 text-plum hover:bg-gold hover:text-white hover:border-gold transition-colors">
                                ← Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="px-4 py-2 rounded-lg bg-plum text-white font-semibold">
                                    <?php echo $i; ?>
                                </span>
                            <?php elseif ($i == 1 || $i == $totalPages || abs($i - $currentPage) <= 2): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>" 
                                   class="px-4 py-2 rounded-lg border border-gray-300 text-plum hover:bg-gold hover:text-white hover:border-gold transition-colors">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif (abs($i - $currentPage) == 3): ?>
                                <span class="px-2 text-gray-400">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>" 
                               class="px-4 py-2 rounded-lg border border-gray-300 text-plum hover:bg-gold hover:text-white hover:border-gold transition-colors">
                                Next →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty state -->
                <div class="text-center py-20">
                    <div class="text-6xl mb-4 opacity-30">✨</div>
                    <?php if ($search): ?>
                        <p class="text-gray-500 text-lg mb-2">No testimonies found for "<?php echo htmlspecialchars($search); ?>"</p>
                        <a href="testimonies.php" class="text-gold hover:underline">View all testimonies</a>
                    <?php else: ?>
                        <p class="text-gray-500 text-lg mb-2">No testimonies available yet</p>
                        <p class="text-gray-400 text-sm">Check back soon for inspiring stories of God's faithfulness!</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-cream py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="bg-gradient-to-br from-plum to-purple-900 rounded-2xl p-8 md:p-12 text-white">
                <h2 class="font-playfair font-bold text-3xl mb-4">Stay Connected</h2>
                <p class="text-gold text-lg mb-6">
                    Subscribe to get notified when new testimonies are published
                </p>
                
                <?php include __DIR__ . '/../includes/newsletter-signup.php'; ?>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Main JS -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
</body>
</html>

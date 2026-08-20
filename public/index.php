<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/post-functions.php';
require_once __DIR__ . '/../includes/video-functions.php';

$pageTitle = "Naomi Wendot — Life Blessed is a Life Shared";
$metaDescription = "Kenyan Christian writer and poet sharing poems, articles, daily inspirations, and testimonies rooted in faith.";

// Fetch data from database
$featuredPost = getFeaturedPost();
$latestPosts = getLatestPosts(3); // Get 3 latest posts
$testimonials = getApprovedTestimonials(3); // Get 3 testimonials
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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo basePath(); ?>assets/css/custom.css">
</head>
<body class="font-inter">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- SECTION 1: HERO -->
    <section id="hero" class="hero-parallax relative min-h-[85vh] md:min-h-screen flex items-center" style="background-image: url('https://images.unsplash.com/photo-1455390582262-044cdead277a?w=1600&q=80');">
        <!-- Overlay -->
        <div class="hero-overlay absolute inset-0" style="background: linear-gradient(135deg, rgba(74,25,66,0.85), rgba(74,25,66,0.65));"></div>
        
        <!-- Content Grid -->
        <div class="relative z-20 w-full max-w-7xl mx-auto px-4 py-12 md:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start lg:items-center">
                <!-- LEFT COLUMN: Content (Takes 7 columns on desktop) -->
                <div class="fade-in-up order-2 lg:order-1 lg:col-span-7">
                    <!-- Badge -->
                    <div class="inline-flex items-center px-3 py-1.5 md:px-4 md:py-2 rounded-full border-2 border-gold bg-transparent text-cream text-xs md:text-sm font-medium mb-4 md:mb-6">
                        ✍️ Poems · Articles · Inspirations
                    </div>
                    
                    <!-- Heading -->
                    <h1 class="font-playfair font-bold">
                        <span class="block text-4xl sm:text-5xl lg:text-6xl xl:text-7xl italic text-cream leading-tight">Words that Heal,</span>
                        <span class="block text-4xl sm:text-5xl lg:text-6xl xl:text-7xl italic text-gold mt-2 leading-tight">Hope that Lasts.</span>
                    </h1>
                    
                    <!-- Subtext -->
                    <p class="text-cream text-base md:text-lg font-inter leading-relaxed max-w-lg mt-6">
                        Writing rooted in faith, hope, and the beauty of everyday life.
                    </p>
                    
                    <!-- Primary Button -->
                    <div class="mt-8">
                        <a 
                            href="#body-of-work-preview" 
                            class="inline-block px-8 py-4 bg-gold text-plum font-semibold rounded-full hover:scale-105 transition-transform duration-300 text-center w-full sm:w-auto shadow-lg"
                        >
                            Read My Writing →
                        </a>
                    </div>
                    
                    <!-- Quote (desktop only, below button) -->
                    <p class="hidden lg:block text-cream text-opacity-90 font-playfair italic text-base mt-8 max-w-md">
                        "Life blessed is a life shared." — Naomi Wendot, 2005
                    </p>
                </div>
                
                <!-- RIGHT COLUMN: Featured Card (Takes 5 columns on desktop) -->
                <div class="fade-in-up order-1 lg:order-2 lg:col-span-5" style="transition-delay: 200ms;">
                    <?php if ($featuredPost): 
                        $isFeaturedVideo = !empty($featuredPost['video_file']);
                    ?>
                    <!-- Mobile: simple flat card, Desktop: floating/tilted -->
                    <div class="bg-white rounded-2xl md:rounded-3xl shadow-lg md:shadow-2xl lg:tilt-card lg:float-card max-w-md mx-auto lg:ml-auto lg:mr-0 overflow-hidden">
                        <!-- Header -->
                        <div class="bg-plum px-4 py-3 md:px-6 md:py-4">
                            <h3 class="font-playfair font-bold text-cream text-center text-sm md:text-base">
                                <?php echo $isFeaturedVideo ? "🎥 THIS WEEK'S VIDEO" : "THIS WEEK'S INSPIRATION"; ?>
                            </h3>
                        </div>
                        
                        <!-- Video Thumbnail (if video) -->
                        <?php if ($isFeaturedVideo): ?>
                            <div class="relative bg-black group">
                                <a href="piece-single.php?slug=<?php echo htmlspecialchars($featuredPost['slug']); ?>">
                                    <?php 
                                    $featuredVideoThumbnail = getVideoPoster($featuredPost);
                                    if ($featuredVideoThumbnail): ?>
                                        <!-- Show video poster -->
                                        <video 
                                            class="w-full h-40 md:h-48 object-cover" 
                                            preload="metadata"
                                            muted
                                            playsinline
                                            style="pointer-events: none;"
                                            poster="<?php echo htmlspecialchars($featuredVideoThumbnail); ?>"
                                        >
                                            <source src="<?php echo htmlspecialchars(basePath() . $featuredPost['video_file']); ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <div class="w-full h-40 md:h-48 bg-gradient-to-br from-plum via-purple-800 to-plum flex items-center justify-center">
                                            <span class="text-5xl md:text-6xl text-white opacity-40">🎥</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Play Button Overlay -->
                                    <div class="absolute inset-0 flex items-center justify-center group-hover:bg-black group-hover:bg-opacity-20 transition-all">
                                        <div class="bg-white bg-opacity-95 rounded-full p-3 md:p-4 group-hover:bg-opacity-100 group-hover:scale-110 transition-all shadow-lg">
                                            <svg class="w-6 h-6 md:w-8 md:h-8 text-plum" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Duration Badge (desktop only) -->
                                    <?php if (!empty($featuredPost['video_duration'])): ?>
                                        <span class="hidden md:inline-block absolute bottom-2 right-2 bg-black bg-opacity-80 text-white text-xs font-semibold px-2 py-1 rounded">
                                            <?php echo formatVideoDuration($featuredPost['video_duration']); ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Body -->
                        <div class="px-4 py-4 md:px-6 md:py-6 relative">
                            <?php if (!$isFeaturedVideo): ?>
                                <!-- Quote Icon (only for written content) -->
                                <svg class="w-6 h-6 md:w-8 md:h-8 text-gold opacity-20 mb-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"></path>
                                </svg>
                            <?php endif; ?>
                            
                            <!-- Title for videos -->
                            <?php if ($isFeaturedVideo): ?>
                                <h4 class="font-playfair font-bold text-plum text-lg md:text-xl mb-2 md:mb-3">
                                    <?php echo htmlspecialchars($featuredPost['title']); ?>
                                </h4>
                            <?php endif; ?>
                            
                            <!-- Excerpt -->
                            <p class="font-playfair italic text-plum text-base md:text-lg leading-relaxed">
                                <?php echo formatExcerpt($featuredPost, 100); ?>
                            </p>
                            
                            <!-- Like & View Bar (desktop only for hero card) -->
                            <?php if ($isFeaturedVideo): ?>
                                <div class="mt-3 mb-3 hidden md:flex items-center gap-4 text-xs">
                                    <button 
                                        class="video-like-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border transition-all font-semibold"
                                        data-post-id="<?php echo (int)$featuredPost['id']; ?>"
                                        data-likes="<?php echo (int)($featuredPost['likes'] ?? 0); ?>"
                                        style="border-color: #D4A017; color: #4A1942; background: rgba(212,160,23,0.05);"
                                    >
                                        <span class="like-icon">♡</span>
                                        <span class="like-text">Did this bless you?</span>
                                        <span class="like-count font-bold" style="color: #D4A017;"><?php echo (int)($featuredPost['likes'] ?? 0); ?></span>
                                    </button>
                                    
                                    <?php if (!empty($featuredPost['video_views']) && $featuredPost['video_views'] > 0): ?>
                                        <span class="flex items-center gap-1 text-gray-600">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span><?php echo formatVideoViews($featuredPost['video_views']); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Divider -->
                            <div class="w-12 md:w-16 h-0.5 bg-gold my-3 md:my-4 ml-auto"></div>
                            
                            <!-- Attribution -->
                            <p class="text-gold text-xs md:text-sm text-right font-inter">
                                — Naomi, <?php echo date('jS M', strtotime($featuredPost['published_at'])); ?>
                            </p>
                        </div>
                        
                        <!-- Footer Button -->
                        <a 
                            href="piece-single.php?slug=<?php echo htmlspecialchars($featuredPost['slug']); ?>" 
                            class="block w-full bg-gold text-plum font-semibold text-center py-3 md:py-4 rounded-b-2xl md:rounded-b-3xl hover:bg-opacity-90 transition-all text-sm md:text-base"
                        >
                            <?php echo $isFeaturedVideo ? 'Watch Now →' : 'Read More →'; ?>
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Fallback if no posts -->
                    <div class="bg-white rounded-2xl md:rounded-3xl shadow-lg md:shadow-2xl lg:tilt-card lg:float-card max-w-md mx-auto lg:ml-auto lg:mr-0 overflow-hidden">
                        <div class="bg-plum px-4 py-3 md:px-6 md:py-4">
                            <h3 class="font-playfair font-bold text-cream text-center">WELCOME</h3>
                        </div>
                        <div class="px-4 py-4 md:px-6 md:py-6">
                            <p class="font-playfair italic text-plum text-base md:text-lg leading-relaxed">
                                Welcome to this space of encouragement. New content coming soon!
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Secondary Actions (below fold on mobile) -->
            <div class="mt-8 lg:hidden">
                <!-- Subscribe Button (mobile only) -->
                <a 
                    href="#newsletter" 
                    class="block w-full px-8 py-3 border-2 border-cream text-cream font-semibold rounded-full hover:bg-cream hover:text-plum transition-all duration-300 text-center mb-4"
                >
                    Subscribe to Updates
                </a>
                
                <!-- Quote (mobile only) -->
                <p class="text-cream text-opacity-80 font-playfair italic text-sm text-center">
                    "Life blessed is a life shared." — Naomi Wendot, 2005
                </p>
            </div>
        </div>
        
        <!-- Scroll Cue (desktop only) -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 hidden md:block">
            <svg class="w-6 h-6 text-cream animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- SECTION 2: BODY OF WORK PREVIEW -->
    <section id="body-of-work-preview" class="bg-plum py-12 md:py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest mb-3 font-inter">WHAT I DO</p>
                <h2 class="font-playfair font-bold text-cream text-3xl md:text-4xl">Write. Teach. Share God's Word.</h2>
                <p class="text-cream text-opacity-80 text-sm md:text-base font-inter mt-3 max-w-2xl mx-auto">
                    Writer, EDUCARE Trainer of Trainers, and advocate for Bible access — using words, education, and ministry to guide hearts toward Christ.
                </p>
                <div class="gold-underline mt-4"></div>
            </div>
            
            <!-- Clean 3x2 Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Poems -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-md hover-lift fade-in-up" style="transition-delay: 50ms;">
                    <div class="h-48 overflow-hidden">
                        <img 
                            src="https://images.unsplash.com/photo-1455390582262-044cdead277a?w=600&q=80" 
                            alt="Poems - Writing with pen"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6">
                        <h3 class="font-playfair font-bold text-plum text-2xl mb-3">Poems</h3>
                        <p class="text-charcoal text-sm font-inter leading-relaxed mb-5">
                            Freestyle, heartfelt expressions of faith, hope, and everyday beauty
                        </p>
                        <a href="../body-of-work/poems.php" class="inline-flex items-center text-gold text-sm font-semibold hover:underline">
                            Browse Poems →
                        </a>
                    </div>
                </div>
                
                <!-- Articles -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-md hover-lift fade-in-up" style="transition-delay: 100ms;">
                    <div class="h-48 overflow-hidden">
                        <img 
                            src="https://images.unsplash.com/photo-1506869640319-fe1a24fd76dc?w=600&q=80" 
                            alt="Articles - Open book with coffee"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6">
                        <h3 class="font-playfair font-bold text-plum text-2xl mb-3">Articles</h3>
                        <p class="text-charcoal text-sm font-inter leading-relaxed mb-5">
                            Thoughtful reflections on life, faith, and personal growth
                        </p>
                        <a href="../body-of-work/articles.php" class="inline-flex items-center text-gold text-sm font-semibold hover:underline">
                            Browse Articles →
                        </a>
                    </div>
                </div>
                
                <!-- Daily Inspirations -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-md hover-lift fade-in-up" style="transition-delay: 150ms;">
                    <div class="h-48 overflow-hidden">
                        <img 
                            src="https://images.unsplash.com/photo-1470252649378-9c29740c9fa8?w=600&q=80" 
                            alt="Daily Inspirations - Sunrise over mountains"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6">
                        <h3 class="font-playfair font-bold text-plum text-2xl mb-3">Daily Inspirations</h3>
                        <p class="text-charcoal text-sm font-inter leading-relaxed mb-5">
                            Holy Spirit-inspired messages to start your day with hope
                        </p>
                        <a href="../body-of-work/daily-inspirations.php" class="inline-flex items-center text-gold text-sm font-semibold hover:underline">
                            Browse Inspirations →
                        </a>
                    </div>
                </div>
                
                <!-- Stories -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-md hover-lift fade-in-up" style="transition-delay: 200ms;">
                    <div class="h-48 overflow-hidden">
                        <img 
                            src="https://images.unsplash.com/photo-1457369804613-52c61a468e7d?w=600&q=80" 
                            alt="Stories - Open book pages"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6">
                        <h3 class="font-playfair font-bold text-plum text-2xl mb-3">Stories</h3>
                        <p class="text-charcoal text-sm font-inter leading-relaxed mb-5">
                            Narratives of faith, courage, and transformation
                        </p>
                        <a href="../body-of-work/stories.php" class="inline-flex items-center text-gold text-sm font-semibold hover:underline">
                            Browse Stories →
                        </a>
                    </div>
                </div>
                
                <!-- Testimonies -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-md hover-lift fade-in-up" style="transition-delay: 250ms;">
                    <div class="h-48 overflow-hidden">
                        <img 
                            src="https://images.unsplash.com/photo-1502139214982-d0ad755818d8?w=600&q=80" 
                            alt="Testimonies - Praying hands with light"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6">
                        <h3 class="font-playfair font-bold text-plum text-2xl mb-3">Testimonies</h3>
                        <p class="text-charcoal text-sm font-inter leading-relaxed mb-5">
                            Real stories of God's grace, protection, and answered prayer
                        </p>
                        <a href="../body-of-work/testimonies.php" class="inline-flex items-center text-gold text-sm font-semibold hover:underline">
                            Browse Testimonies →
                        </a>
                    </div>
                </div>
                
                <!-- Videos -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-md hover-lift fade-in-up" style="transition-delay: 300ms;">
                    <div class="h-48 overflow-hidden">
                        <img 
                            src="https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=600&q=80" 
                            alt="Videos - Video recording setup"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-6">
                        <h3 class="font-playfair font-bold text-plum text-2xl mb-3">Videos</h3>
                        <p class="text-charcoal text-sm font-inter leading-relaxed mb-5">
                            Short inspirational videos sharing faith and encouragement
                        </p>
                        <a href="../body-of-work/videos.php" class="inline-flex items-center text-gold text-sm font-semibold hover:underline">
                            Browse Videos →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: FEATURED WRITING CAROUSEL -->
    <section id="featured-writing" class="bg-cream py-12 md:py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-8 md:mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest mb-2 md:mb-3 font-inter">LATEST PIECES</p>
                <h2 class="font-playfair font-bold text-plum text-2xl md:text-4xl">Latest Content</h2>
                <div class="gold-underline mt-2 md:mt-3"></div>
            </div>
            
            <!-- Carousel -->
            <div id="latest-carousel" class="overflow-x-auto snap-x snap-mandatory scrollbar-hide pb-4">
                <div class="flex gap-4 md:gap-6 w-max px-2" id="carousel-track">
                    <?php if (!empty($latestPosts)): ?>
                        <?php foreach ($latestPosts as $index => $post): 
                            $isVideo = !empty($post['video_file']);
                            $isHandwritten = !empty($post['handwritten_image']);
                        ?>
                            <div class="w-[85vw] sm:w-[45vw] md:min-w-[350px] snap-center bg-white rounded-2xl shadow-md overflow-hidden hover-lift fade-in-up" style="transition-delay: <?php echo ($index * 100); ?>ms;">
                                <?php if ($isVideo): ?>
                                    <!-- Video Thumbnail with Play Overlay -->
                                    <div class="h-40 md:h-48 overflow-hidden relative bg-black group">
                                        <a href="piece-single.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="block relative">
                                            <?php 
                                            $videoThumbnail = getVideoPoster($post);
                                            if ($videoThumbnail): ?>
                                                <!-- Show video poster -->
                                                <video 
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                                    preload="metadata"
                                                    muted
                                                    playsinline
                                                    poster="<?php echo htmlspecialchars($videoThumbnail); ?>"
                                                >
                                                    <source src="<?php echo htmlspecialchars(basePath() . $post['video_file']); ?>" type="video/mp4">
                                                </video>
                                            <?php else: ?>
                                                <div class="w-full h-full bg-gradient-to-br from-plum via-purple-800 to-plum flex items-center justify-center">
                                                    <span class="text-5xl md:text-6xl text-white opacity-40">🎥</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Play Button Overlay -->
                                            <div class="absolute inset-0 flex items-center justify-center group-hover:bg-black group-hover:bg-opacity-20 transition-all">
                                                <div class="bg-white bg-opacity-95 rounded-full p-2.5 md:p-3 group-hover:bg-opacity-100 group-hover:scale-110 transition-all shadow-lg">
                                                    <svg class="w-5 h-5 md:w-6 md:h-6 text-plum" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php elseif ($post['featured_image'] || $post['handwritten_image']): ?>
                                    <!-- Image Content -->
                                    <div class="h-40 md:h-48 overflow-hidden <?php echo $isHandwritten ? 'bg-rose flex items-center justify-center' : ''; ?>">
                                        <img src="<?php echo basePath() . ($post['handwritten_image'] ?? $post['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                             class="<?php echo $isHandwritten ? 'h-full w-auto object-contain' : 'w-full h-full object-cover'; ?> hover:scale-105 transition-transform duration-500">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="p-4 md:p-6">
                                    <span class="inline-block px-2.5 py-1 md:px-3 md:py-1 bg-gold bg-opacity-10 text-gold text-xs font-semibold rounded-full mb-2 md:mb-3">
                                        <?php echo htmlspecialchars($post['category_name']); ?>
                                    </span>
                                    
                                    <!-- Title (for all content types) -->
                                    <h3 class="font-montserrat font-bold text-plum text-base md:text-lg mb-2 line-clamp-2">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h3>
                                    
                                    <p class="font-playfair italic text-plum text-sm md:text-base line-clamp-3 leading-relaxed mb-3">
                                        <?php echo formatExcerpt($post, 100); ?>
                                    </p>
                                    
                                    <p class="text-gold text-xs font-inter mb-3">
                                        <?php echo formatPostDate($post); ?>
                                    </p>
                                    <a href="piece-single.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" 
                                       class="inline-block text-plum text-sm font-semibold hover:underline">
                                        <?php echo $isVideo ? 'Watch →' : 'Read →'; ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="min-w-[300px] md:min-w-[350px] snap-center bg-white rounded-2xl shadow-md p-6 text-center">
                            <p class="text-gray-500">New content coming soon!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Scroll Position Indicator (Mobile Only) -->
            <div id="carousel-indicators" class="flex justify-center gap-2 mt-4 md:hidden">
                <!-- Dots will be injected by JavaScript -->
            </div>
        </div>
    </section>

    <!-- SECTION 4: ABOUT SNAPSHOT -->
    <section id="about-snapshot" class="bg-rose py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- LEFT: Portrait -->
                <div class="fade-in-up order-1">
                    <img 
                        src="<?php echo basePath(); ?>assets/images/NaomiWendotProfileImage.png" 
                        alt="Naomi Wendot portrait" 
                        class="w-full max-w-md mx-auto aspect-[3/4] object-cover rounded-2xl border-2 border-gold shadow-lg tilt-card"
                        loading="lazy"
                    >
                </div>
                
                <!-- RIGHT: Text -->
                <div class="fade-in-up order-2" style="transition-delay: 200ms;">
                    <p class="text-gold uppercase text-xs md:text-sm tracking-widest mb-3 font-inter">ABOUT</p>
                    <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl mb-3">
                        A Writer Who Found Her Voice in Faith
                    </h2>
                    <div class="gold-underline mb-6" style="margin-left: 0;"></div>
                    
                    <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed mb-4">
                        Naomi Wendot is a Kenyan-born poet, writer, educator, and woman of deep Christian faith. Her words have journeyed from personal journals in 2005 to hearts across Kenya, the United States, and beyond.
                    </p>
                    
                    <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed mb-6">
                        What began as freestyle poems jotted in quiet moments has grown into a body of poetry, articles, daily inspirations, stories, and testimonies. Beyond writing, she is a trained EDUCARE Trainer of Trainers, bringing Christian-centred education and hope to communities.
                    </p>
                    
                    <a href="about.php" class="inline-block text-gold font-semibold text-base md:text-lg hover:underline">
                        Read Her Full Story →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: READER TESTIMONIALS -->
    <section id="testimonials" class="bg-plum py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <h2 class="font-playfair font-bold text-cream text-4xl">What Readers Say</h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <!-- Testimonials Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (!empty($testimonials)): ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <?php
                        $quote = $testimonial['testimony'];
                        $name = $testimonial['name'];
                        $location = $testimonial['location'] ?? '';
                        $bg = "white";
                        include __DIR__ . '/../includes/testimonial-card.php';
                        ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback testimonials -->
                    <?php
                    $quote = "Her poems on faith and hope have become a daily refuge.";
                    $name = "Onduso Bonface";
                    $location = "Kenya";
                    $bg = "white";
                    include __DIR__ . '/../includes/testimonial-card.php';
                    
                    $quote = "The authenticity in her writing feels like a conversation with a dear friend.";
                    $name = "Margaret Wendot";
                    $location = "Kenya";
                    include __DIR__ . '/../includes/testimonial-card.php';
                    
                    $quote = "Her testimonies have transformed how I see God's presence in my life.";
                    $name = "Elizabeth W.";
                    $location = "Mombasa";
                    include __DIR__ . '/../includes/testimonial-card.php';
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- SECTION 6: NEWSLETTER BAND -->
    <section id="newsletter" class="bg-gold py-16 px-4">
        <div class="max-w-4xl mx-auto text-center fade-in-up">
            <h2 class="font-playfair font-bold text-plum text-3xl mb-3">Stay Encouraged</h2>
            <p class="text-plum text-base font-inter mb-8">
                Get new poems, daily inspirations, and stories straight to your inbox.
            </p>
            
            <?php
            $context = 'homepage';
            include __DIR__ . '/../includes/newsletter-signup.php';
            ?>
            
            <p class="text-plum text-sm italic mt-4 font-inter">
                No spam. Just words that matter.
            </p>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
    <script src="<?php echo basePath(); ?>assets/js/newsletter.js"></script>
</body>
</html>

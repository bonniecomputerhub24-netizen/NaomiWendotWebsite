<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Naomi Wendot — Life Blessed is a Life Shared";
$metaDescription = "Kenyan Christian writer and poet sharing poems, articles, daily inspirations, and testimonies rooted in faith.";
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
</head>
<body class="font-inter">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- SECTION 1: HERO -->
    <section id="hero" class="hero-parallax relative min-h-screen md:min-h-screen flex items-center" style="background-image: url('https://images.unsplash.com/photo-1455390582262-044cdead277a?w=1600&q=80');">
        <!-- Overlay -->
        <div class="hero-overlay absolute inset-0" style="background: linear-gradient(135deg, rgba(74,25,66,0.75), rgba(74,25,66,0.55));"></div>
        
        <!-- Content Grid -->
        <div class="relative z-20 w-full max-w-7xl mx-auto px-4 py-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- LEFT COLUMN: Content -->
                <div class="fade-in-up order-2 md:order-1">
                    <!-- Badge -->
                    <div class="inline-flex items-center px-4 py-2 rounded-full border-2 border-gold bg-transparent text-cream text-sm font-medium mb-6">
                        ✍️ Poems · Articles · Daily Inspirations · Stories
                    </div>
                    
                    <!-- Heading -->
                    <h1 class="font-playfair font-bold">
                        <span class="block text-5xl md:text-7xl italic text-cream">Words that Heal,</span>
                        <span class="block text-5xl md:text-7xl italic text-gold mt-2">Hope that Lasts.</span>
                    </h1>
                    
                    <!-- Subtext -->
                    <p class="text-cream text-base md:text-lg font-inter leading-relaxed max-w-md mt-6">
                        Writing rooted in faith, hope, and the beauty of everyday life. Welcome to this space of encouragement.
                    </p>
                    
                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-8">
                        <a 
                            href="#body-of-work-preview" 
                            class="inline-block px-8 py-4 bg-gold text-plum font-semibold rounded-full hover:scale-105 transition-transform duration-300 text-center"
                        >
                            Read My Writing →
                        </a>
                        <a 
                            href="#newsletter" 
                            class="inline-block px-8 py-4 border-2 border-cream text-cream font-semibold rounded-full hover:bg-cream hover:text-plum transition-all duration-300 text-center"
                        >
                            Subscribe
                        </a>
                    </div>
                    
                    <!-- Floating Quote -->
                    <p class="text-cream text-opacity-80 font-playfair italic text-base mt-8">
                        "Life blessed is a life shared." — Naomi Wendot, 2005
                    </p>
                </div>
                
                <!-- RIGHT COLUMN: Floating Card -->
                <div class="fade-in-up order-1 md:order-2 hidden md:block" style="transition-delay: 200ms;">
                    <div class="bg-white rounded-3xl shadow-2xl tilt-card float-card max-w-md ml-auto overflow-hidden">
                        <!-- Header -->
                        <div class="bg-plum px-6 py-4">
                            <h3 class="font-playfair font-bold text-cream text-center">THIS WEEK'S INSPIRATION</h3>
                        </div>
                        
                        <!-- Body -->
                        <div class="px-6 py-6 relative">
                            <!-- Quote Icon -->
                            <svg class="w-8 h-8 text-gold opacity-20 mb-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"></path>
                            </svg>
                            
                            <!-- Excerpt -->
                            <p class="font-playfair italic text-plum text-lg leading-relaxed">
                                It's yet another day, another chance to live, another opportunity to share, to give. A day to praise and worship the Lord above...
                            </p>
                            
                            <!-- Divider -->
                            <div class="w-16 h-0.5 bg-gold my-4 ml-auto"></div>
                            
                            <!-- Attribution -->
                            <p class="text-gold text-sm text-right font-inter">
                                — Naomi Wendot, 7th Nov 2005
                            </p>
                        </div>
                        
                        <!-- Footer Button -->
                        <a 
                            href="piece-single.php?slug=yet-another-day-2005" 
                            class="block w-full bg-gold text-plum font-semibold text-center py-4 rounded-b-3xl hover:bg-opacity-90 transition-all"
                        >
                            Read More →
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Cue -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 hidden md:block">
            <svg class="w-6 h-6 text-cream animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- SECTION 2: BODY OF WORK PREVIEW -->
    <section id="body-of-work-preview" class="bg-plum py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest mb-3 font-inter">EXPLORE THE WRITING</p>
                <h2 class="font-playfair font-bold text-cream text-3xl md:text-4xl">From the Archives</h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <!-- Asymmetric Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Feature Card: Poems (Spans 2 Columns) -->
                <div class="md:col-span-2 relative rounded-2xl overflow-hidden hover-lift fade-in-up" style="transition-delay: 100ms;">
                    <img 
                        src="https://images.unsplash.com/photo-1517842645767-c639042777db?w=800&q=80" 
                        alt="Poetry and writing" 
                        class="w-full h-80 object-cover"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-plum via-plum/60 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-8">
                        <div class="inline-block px-3 py-1 bg-gold bg-opacity-20 border border-gold text-gold text-xs font-semibold rounded-full mb-3">
                            FEATURED
                        </div>
                        <h3 class="font-playfair font-bold text-cream text-3xl mb-2">Poems</h3>
                        <p class="text-cream text-opacity-90 text-base font-inter mb-4">
                            Freestyle, heartfelt expressions of faith, hope, and everyday beauty
                        </p>
                        <a href="body-of-work.php#poems" class="inline-block text-gold font-semibold hover:underline">
                            Browse Poems →
                        </a>
                    </div>
                </div>
                
                <!-- Articles -->
                <div class="bg-white rounded-2xl p-6 hover-lift fade-in-up" style="transition-delay: 200ms;">
                    <svg class="w-10 h-10 text-gold mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="font-playfair font-bold text-plum text-xl mb-2">Articles</h3>
                    <p class="text-charcoal text-sm font-inter leading-relaxed mb-4">
                        Thoughtful reflections on life, faith, and personal growth
                    </p>
                    <a href="body-of-work.php#articles" class="text-gold text-sm font-semibold hover:underline">
                        Browse →
                    </a>
                </div>
                
                <!-- Daily Inspirations -->
                <div class="bg-white rounded-2xl p-6 hover-lift fade-in-up" style="transition-delay: 300ms;">
                    <svg class="w-10 h-10 text-gold mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <h3 class="font-playfair font-bold text-plum text-xl mb-2">Daily Inspirations</h3>
                    <p class="text-charcoal text-sm font-inter leading-relaxed mb-4">
                        Short, Holy Spirit-inspired messages to start your day with hope
                    </p>
                    <a href="body-of-work.php#daily-inspirations" class="text-gold text-sm font-semibold hover:underline">
                        Browse →
                    </a>
                </div>
                
                <!-- Stories -->
                <div class="bg-white rounded-2xl p-6 hover-lift fade-in-up" style="transition-delay: 400ms;">
                    <svg class="w-10 h-10 text-gold mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="font-playfair font-bold text-plum text-xl mb-2">Stories</h3>
                    <p class="text-charcoal text-sm font-inter leading-relaxed mb-4">
                        Narratives of faith, courage, and transformation
                    </p>
                    <a href="body-of-work.php#stories" class="text-gold text-sm font-semibold hover:underline">
                        Browse →
                    </a>
                </div>
                
                <!-- Testimonies -->
                <div class="bg-white rounded-2xl p-6 hover-lift fade-in-up" style="transition-delay: 500ms;">
                    <svg class="w-10 h-10 text-gold mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    <h3 class="font-playfair font-bold text-plum text-xl mb-2">Testimonies</h3>
                    <p class="text-charcoal text-sm font-inter leading-relaxed mb-4">
                        Real stories of God's grace, protection, and answered prayer
                    </p>
                    <a href="body-of-work.php#testimonies" class="text-gold text-sm font-semibold hover:underline">
                        Browse →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: FEATURED WRITING CAROUSEL -->
    <section id="featured-writing" class="bg-cream py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest mb-3 font-inter">LATEST PIECES</p>
                <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl">Latest Writing</h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <!-- Carousel -->
            <div class="overflow-x-auto snap-x snap-mandatory scrollbar-hide pb-4">
                <div class="flex gap-6 w-max px-4">
                    <!-- Card 1: Poem with Archive Image -->
                    <div class="min-w-[300px] md:min-w-[350px] snap-center bg-white rounded-2xl shadow-md overflow-hidden hover-lift fade-in-up" style="transition-delay: 100ms;">
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo basePath(); ?>assets/images/Archives1.png" 
                                 alt="It's Yet Another Day" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-6">
                            <span class="inline-block px-3 py-1 bg-gold bg-opacity-10 text-gold text-xs font-semibold rounded-full mb-3">Poem</span>
                            <p class="font-playfair italic text-plum text-base line-clamp-3 leading-relaxed mb-3">
                                It's yet another day, another chance to live, another opportunity to share, to give. A day to praise and worship the Lord above...
                            </p>
                            <p class="text-gold text-xs font-inter mb-3">7th November 2005</p>
                            <a href="piece-single.php?slug=yet-another-day-2005" class="inline-block text-plum text-sm font-semibold hover:underline">
                                Read Full Piece →
                            </a>
                        </div>
                    </div>
                    
                    <!-- Card 2: Daily Inspiration -->
                    <div class="min-w-[300px] md:min-w-[350px] snap-center bg-white rounded-2xl shadow-md p-6 hover-lift fade-in-up" style="transition-delay: 200ms;">
                        <span class="inline-block px-3 py-1 bg-gold bg-opacity-10 text-gold text-xs font-semibold rounded-full mb-3">Daily Inspiration</span>
                        <p class="font-playfair italic text-plum text-base line-clamp-3 leading-relaxed mb-3">
                            God's hand is there to help you cross the bridge you can't, to jump the heights you can't, to swim through the big stormy ocean and emerge victoriously as a winner — all for His glory.
                        </p>
                        <p class="text-gold text-xs font-inter mb-3">Recent</p>
                        <a href="piece-single.php?slug=overcomer-in-christ" class="inline-block text-plum text-sm font-semibold hover:underline">
                            Read Full Piece →
                        </a>
                    </div>
                    
                    <!-- Card 3: Testimony with Archive Image -->
                    <div class="min-w-[300px] md:min-w-[350px] snap-center bg-white rounded-2xl shadow-md overflow-hidden hover-lift fade-in-up" style="transition-delay: 300ms;">
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo basePath(); ?>assets/images/Archives3.png" 
                                 alt="Guarding What I Consume" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-6">
                            <span class="inline-block px-3 py-1 bg-gold bg-opacity-10 text-gold text-xs font-semibold rounded-full mb-3">Testimony</span>
                            <p class="font-playfair italic text-plum text-base line-clamp-3 leading-relaxed mb-3">
                                How can a young person stay on the path of purity? By living according to your word. — Psalm 119:9
                            </p>
                            <p class="text-gold text-xs font-inter mb-3">Personal Reflection</p>
                            <a href="piece-single.php?slug=guarding-what-i-consume" class="inline-block text-plum text-sm font-semibold hover:underline">
                                Read Full Piece →
                            </a>
                        </div>
                    </div>
                    
                    <!-- Card 4: Poem with Archive Image -->
                    <div class="min-w-[300px] md:min-w-[350px] snap-center bg-white rounded-2xl shadow-md overflow-hidden hover-lift fade-in-up" style="transition-delay: 400ms;">
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo basePath(); ?>assets/images/Archives2.png" 
                                 alt="I Shall Flourish Like the Palm Tree" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-6">
                            <span class="inline-block px-3 py-1 bg-gold bg-opacity-10 text-gold text-xs font-semibold rounded-full mb-3">Poem</span>
                            <p class="font-playfair italic text-plum text-base line-clamp-3 leading-relaxed mb-3">
                                I shall flourish like the palm tree. — Psalm 92:12
                            </p>
                            <p class="text-gold text-xs font-inter mb-3">Personal Declaration</p>
                            <a href="piece-single.php?slug=flourish-like-palm-tree" class="inline-block text-plum text-sm font-semibold hover:underline">
                                Read Full Piece →
                            </a>
                        </div>
                    </div>
                </div>
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
                <?php
                // Testimonial 1
                $quote = "Her poems on faith and hope have become a daily refuge.";
                $name = "Onduso Bonface";
                $location = "Kenya";
                $bg = "white";
                include __DIR__ . '/../includes/testimonial-card.php';
                ?>
                
                <?php
                // Testimonial 2
                $quote = "The authenticity in her writing feels like a conversation with a dear friend.";
                $name = "Margaret Wendot";
                $location = "Kenya";
                $bg = "white";
                include __DIR__ . '/../includes/testimonial-card.php';
                ?>
                
                <?php
                // Testimonial 3
                $quote = "Her testimonies have transformed how I see God's presence in my life.";
                $name = "Elizabeth W.";
                $location = "Mombasa";
                $bg = "white";
                include __DIR__ . '/../includes/testimonial-card.php';
                ?>
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

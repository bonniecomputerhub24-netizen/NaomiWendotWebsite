<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Nature & Bible Verses — Naomi Wendot";
$metaDescription = "An in-progress illustrated picture book pairing Scripture on creation with reflections and personal declarations, for children, teenagers, adults, and the aged.";
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

    <!-- HERO -->
    <section id="hero" class="hero-parallax relative min-h-screen md:h-[90vh] flex items-center" style="background-image: url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1600&q=80');">
        <!-- Overlay (Lighter than other pages) -->
        <div class="hero-overlay absolute inset-0" style="background: linear-gradient(135deg, rgba(74,25,66,0.65), rgba(74,25,66,0.45));"></div>
        
        <!-- Content Grid -->
        <div class="relative z-20 w-full max-w-7xl mx-auto px-4 py-12 md:py-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                <!-- LEFT COLUMN: Content -->
                <div class="fade-in-up order-2 md:order-1">
                    <!-- Badge -->
                    <div class="inline-flex items-center px-3 py-1.5 md:px-4 md:py-2 rounded-full border-2 border-gold bg-transparent text-cream text-xs md:text-sm font-medium mb-4 md:mb-6">
                        📖 In Progress — A Picture Book
                    </div>
                    
                    <!-- Heading -->
                    <h1 class="font-playfair font-bold italic">
                        <span class="block text-4xl sm:text-5xl md:text-7xl text-cream leading-tight">Nature &</span>
                        <span class="block text-4xl sm:text-5xl md:text-7xl text-gold mt-1 md:mt-2 leading-tight">Bible Verses</span>
                    </h1>
                    
                    <!-- Subtext -->
                    <p class="text-cream text-sm sm:text-base md:text-lg font-inter leading-relaxed max-w-md mt-4 md:mt-6">
                        An illustrated picture book pairing Scripture on creation — starting with trees — with reflections and personal declarations. Written for children, teenagers, adults, and the aged alike.
                    </p>
                    
                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 md:gap-4 mt-6 md:mt-8">
                        <a 
                            href="#waitlist" 
                            class="inline-block px-6 py-3 md:px-8 md:py-4 bg-gold text-plum font-semibold text-sm md:text-base rounded-full hover:scale-105 transition-transform duration-300 text-center"
                        >
                            Join the Waitlist →
                        </a>
                        <a 
                            href="#verses" 
                            class="inline-block px-6 py-3 md:px-8 md:py-4 border-2 border-cream text-cream font-semibold text-sm md:text-base rounded-full hover:bg-cream hover:text-plum transition-all duration-300 text-center"
                        >
                            Explore the Verses
                        </a>
                    </div>
                </div>
                
                <!-- RIGHT COLUMN: Floating Verse Card -->
                <div class="fade-in-up order-1 md:order-2 hidden md:block" style="transition-delay: 200ms;">
                    <div class="bg-white rounded-3xl shadow-2xl tilt-card float-card max-w-md ml-auto overflow-hidden">
                        <!-- Header -->
                        <div class="bg-plum px-6 py-4">
                            <h3 class="font-playfair font-bold text-cream text-center">PSALM 92:12</h3>
                        </div>
                        
                        <!-- Body -->
                        <div class="px-6 py-6 relative">
                            <!-- Quote Icon -->
                            <svg class="w-8 h-8 text-gold opacity-20 mb-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"></path>
                            </svg>
                            
                            <!-- Verse -->
                            <p class="font-playfair italic text-plum text-lg leading-relaxed">
                                The righteous shall flourish like the palm tree: he shall grow like a cedar in Lebanon.
                            </p>
                        </div>
                        
                        <!-- Footer Declaration -->
                        <div class="bg-gold text-plum font-bold text-center py-4 rounded-b-3xl">
                            I shall flourish like the palm tree.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 1: VERSE SHOWCASE -->
    <section id="verses" class="bg-cream py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-sm tracking-widest font-inter mb-3">THE VERSES</p>
                <h2 class="font-playfair font-bold text-plum text-4xl">Verses on Creation</h2>
                <div class="gold-underline mt-3"></div>
                <p class="text-charcoal text-base font-inter mt-4 max-w-2xl mx-auto">
                    Starting with trees — each verse tagged for the audience it speaks to.
                </p>
            </div>
            
            <!-- Verse Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Genesis 1:11-12 -->
                <div class="fade-in-up" style="transition-delay: 100ms;">
                    <?php
                    $reference = "Genesis 1:11-12";
                    $text = "Then God said, 'Let the land produce vegetation: seed-bearing plants and trees on the land that bear fruit with seed in it, according to their various kinds.' And it was so.";
                    $audienceTags = ['Children', 'Adults'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                </div>
                
                <!-- Psalm 104:24 -->
                <div class="fade-in-up" style="transition-delay: 200ms;">
                    <?php
                    $reference = "Psalm 104:24";
                    $text = "How many are your works, LORD! In wisdom you made them all; the earth is full of your creatures.";
                    $audienceTags = ['Adults', 'Aged'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                </div>
                
                <!-- Psalm 150:6 -->
                <div class="fade-in-up" style="transition-delay: 300ms;">
                    <?php
                    $reference = "Psalm 150:6";
                    $text = "Let everything that has breath praise the LORD. Praise the LORD.";
                    $audienceTags = ['Children', 'Teens', 'Adults', 'Aged'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                </div>
                
                <!-- Luke 12:27 -->
                <div class="fade-in-up" style="transition-delay: 400ms;">
                    <?php
                    $reference = "Luke 12:27";
                    $text = "Consider how the wild flowers grow. They do not labor or spin. Yet I tell you, not even Solomon in all his splendor was dressed like one of these.";
                    $audienceTags = ['Teens', 'Adults'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                </div>
                
                <!-- Psalm 127:3 -->
                <div class="fade-in-up" style="transition-delay: 500ms;">
                    <?php
                    $reference = "Psalm 127:3";
                    $text = "Children are a heritage from the LORD, offspring a reward from him.";
                    $audienceTags = ['Adults', 'Aged'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                </div>
                
                <!-- Psalm 19:1 -->
                <div class="fade-in-up" style="transition-delay: 600ms;">
                    <?php
                    $reference = "Psalm 19:1";
                    $text = "The heavens declare the glory of God; the skies proclaim the work of his hands.";
                    $audienceTags = ['Children', 'Teens'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                </div>
                
                <!-- Psalm 92:12 (Flagship - Spans 2 Columns) -->
                <div class="md:col-span-2 fade-in-up" style="transition-delay: 700ms;">
                    <?php
                    $reference = "Psalm 92:12";
                    $text = "The righteous shall flourish like the palm tree: he shall grow like a cedar in Lebanon.";
                    $audienceTags = ['Teens', 'Adults', 'Aged'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 2: DECLARATIONS -->
    <section id="declarations" class="bg-gold py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-plum uppercase text-sm tracking-widest font-inter mb-3">MAKE IT PERSONAL</p>
                <h2 class="font-playfair font-bold text-plum text-4xl">Declarations</h2>
                <div class="w-16 h-1 bg-plum rounded-full mx-auto mt-3"></div>
            </div>
            
            <!-- Declaration Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Declaration 1 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center fade-in-up" style="transition-delay: 100ms;">
                    <div class="text-4xl text-gold opacity-30 mb-4 font-serif leading-none">"</div>
                    <p class="font-playfair italic text-plum text-xl leading-relaxed">
                        I shall flourish like the palm tree.
                    </p>
                    <p class="text-gray-500 text-xs font-inter mt-4">
                        Psalm 92:12
                    </p>
                </div>
                
                <!-- Declaration 2 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center fade-in-up" style="transition-delay: 200ms;">
                    <div class="text-4xl text-gold opacity-30 mb-4 font-serif leading-none">"</div>
                    <p class="font-playfair italic text-plum text-xl leading-relaxed">
                        I am fearfully and wonderfully made, like every living thing He formed.
                    </p>
                    <p class="text-gray-500 text-xs font-inter mt-4">
                        Inspired by Genesis 1:11-12
                    </p>
                </div>
                
                <!-- Declaration 3 -->
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center fade-in-up" style="transition-delay: 300ms;">
                    <div class="text-4xl text-gold opacity-30 mb-4 font-serif leading-none">"</div>
                    <p class="font-playfair italic text-plum text-xl leading-relaxed">
                        My life declares the glory of God, just as the skies do.
                    </p>
                    <p class="text-gray-500 text-xs font-inter mt-4">
                        Inspired by Psalm 19:1
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: WAITLIST -->
    <section id="waitlist" class="bg-rose py-20 px-4">
        <div class="max-w-2xl mx-auto text-center fade-in-up">
            <p class="text-gold uppercase text-sm tracking-widest font-inter mb-3">BE THE FIRST TO KNOW</p>
            <h2 class="font-playfair font-bold text-plum text-4xl mb-3">Join the Waitlist</h2>
            <div class="gold-underline mb-6"></div>
            
            <p class="text-charcoal text-base font-inter leading-relaxed max-w-xl mx-auto mb-8">
                Get notified the moment Nature & Bible Verses launches — plus early access pricing.
            </p>
            
            <?php
            $context = 'nature-book';
            include __DIR__ . '/../includes/newsletter-signup.php';
            ?>
        </div>
    </section>

    <!-- SECTION 4: COMING TO THE BOOK STORE -->
    <section id="bookstore" class="bg-plum py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-sm tracking-widest font-inter mb-3">COMING SOON</p>
                <h2 class="font-playfair font-bold text-cream text-4xl">The Book Store</h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <!-- Preview Card -->
            <div class="bg-white rounded-2xl shadow-xl max-w-lg mx-auto p-8 fade-in-up" style="transition-delay: 200ms;">
                <!-- Book Placeholder -->
                <div class="max-w-xs mx-auto mb-6">
                    <div class="aspect-[3/4] rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #FDEAEA 0%, #D4A017 100%);">
                        <svg class="w-24 h-24 text-gold opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Format Toggle -->
                <div class="flex gap-3 justify-center mb-4">
                    <button 
                        class="format-toggle active px-6 py-2 rounded-full font-semibold text-sm transition-all"
                        data-format="print"
                        style="background-color: #4A1942; color: #FFFDF5;"
                    >
                        Print
                    </button>
                    <button 
                        class="format-toggle px-6 py-2 rounded-full font-semibold text-sm transition-all"
                        data-format="ebook"
                        style="background-color: #FDEAEA; color: #4A1942;"
                    >
                        eBook
                    </button>
                </div>
                
                <!-- Pricing Notice -->
                <p class="text-gray-500 text-sm text-center mb-6 font-inter">
                    Pricing coming soon
                </p>
                
                <!-- Notify Button -->
                <a 
                    href="#waitlist" 
                    class="block w-full px-6 py-3 bg-gold bg-opacity-50 text-plum text-center font-semibold rounded-full cursor-not-allowed"
                >
                    Notify Me When Available
                </a>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
    <script src="<?php echo basePath(); ?>assets/js/newsletter.js"></script>
    
    <!-- Format Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const formatToggles = document.querySelectorAll('.format-toggle');
            
            formatToggles.forEach(toggle => {
                toggle.addEventListener('click', () => {
                    // Remove active from all
                    formatToggles.forEach(btn => {
                        btn.classList.remove('active');
                        btn.style.backgroundColor = '#FDEAEA';
                        btn.style.color = '#4A1942';
                    });
                    
                    // Add active to clicked
                    toggle.classList.add('active');
                    toggle.style.backgroundColor = '#4A1942';
                    toggle.style.color = '#FFFDF5';
                });
            });
        });
    </script>
</body>
</html>

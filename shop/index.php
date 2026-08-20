<?php
/**
 * Shop Page - Books and Products by Naomi Wendot
 * Currently featuring upcoming book releases
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Shop — Naomi Wendot';
$metaDescription = 'Browse and purchase books by Naomi Wendot — poetry collections, devotionals, and inspirational reads rooted in faith.';

// Fetch books from database
$books = [];
try {
    $db = getDb();
    // Only show published and coming_soon books on frontend (hide drafts and writing)
    $stmt = $db->query("
        SELECT * FROM books 
        WHERE status IN ('coming_soon', 'published')
        ORDER BY 
            FIELD(status, 'published', 'coming_soon'),
            created_at DESC
    ");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching books: " . $e->getMessage());
}
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo basePath(); ?>assets/css/custom.css">
</head>
<body class="font-century bg-cream">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- HERO -->
    <section class="relative h-[300px] flex items-center justify-center overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0">
            <img 
                src="https://images.unsplash.com/photo-1512820790803-83ca734da794?w=1600&q=80" 
                alt="Books"
                class="w-full h-full object-cover"
            >
            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-plum via-purple-900 to-indigo-900 opacity-75"></div>
        </div>

        <!-- Content -->
        <div class="relative z-20 text-center px-4 fade-in-up">
            <h1 class="font-montserrat font-bold text-cream text-4xl md:text-5xl mb-3">
                Books by Naomi
            </h1>
            <p class="text-cream text-base md:text-lg max-w-2xl mx-auto font-century">
                Inspiring words rooted in faith, hope, and God's unchanging love
            </p>
        </div>
    </section>

    <!-- BOOKS SECTION -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <h2 class="font-montserrat font-bold text-plum text-3xl mb-3">Available Books</h2>
                <p class="text-charcoal max-w-2xl mx-auto font-century">
                    Explore our collection of faith-inspired books and devotionals
                </p>
                <div class="gold-underline mt-4"></div>
            </div>

            <?php if (!empty($books)): ?>
                <!-- Books Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($books as $index => $book): ?>
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 fade-in-up" style="transition-delay: <?php echo ($index * 100); ?>ms;">
                            <!-- Book Cover -->
                            <div class="relative h-80 bg-gray-200 overflow-hidden group">
                                <?php if ($book['cover_image']): ?>
                                    <img 
                                        src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                        alt="<?php echo htmlspecialchars($book['title']); ?>"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    >
                                <?php else: ?>
                                    <div class="w-full h-full bg-gradient-to-br from-plum to-purple-800 flex items-center justify-center">
                                        <span class="text-6xl text-white opacity-40">📖</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Status Badge -->
                                <div class="absolute top-4 right-4">
                                    <?php if ($book['status'] === 'published'): ?>
                                        <span class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full shadow-lg font-montserrat">
                                            Available Now
                                        </span>
                                    <?php elseif ($book['status'] === 'coming_soon'): ?>
                                        <span class="px-3 py-1 bg-gold text-plum text-xs font-semibold rounded-full shadow-lg font-montserrat">
                                            Coming Soon
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Pre-order Badge -->
                                <?php if ($book['pre_order']): ?>
                                    <div class="absolute top-4 left-4">
                                        <span class="px-3 py-1 bg-plum text-cream text-xs font-semibold rounded-full shadow-lg font-montserrat">
                                            Pre-Order
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Book Details -->
                            <div class="p-6">
                                <!-- Category -->
                                <?php if ($book['category']): ?>
                                    <span class="inline-block px-3 py-1 bg-rose text-gold text-xs font-semibold rounded-full mb-3 font-montserrat">
                                        <?php echo htmlspecialchars($book['category']); ?>
                                    </span>
                                <?php endif; ?>

                                <!-- Title -->
                                <h3 class="font-montserrat font-bold text-plum text-xl mb-2 leading-tight">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </h3>

                                <!-- Subtitle -->
                                <?php if ($book['subtitle']): ?>
                                    <p class="text-gray-600 text-sm italic mb-3 font-century line-clamp-2">
                                        <?php echo htmlspecialchars($book['subtitle']); ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Description -->
                                <p class="text-charcoal text-sm leading-relaxed mb-4 line-clamp-3 font-century">
                                    <?php echo htmlspecialchars($book['description']); ?>
                                </p>

                                <!-- Price - Both Currencies -->
                                <div class="mb-3 border-t border-gray-100 pt-4">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-plum font-bold text-2xl font-montserrat">
                                            $<?php echo number_format($book['price_usd'] ?? $book['price'], 2); ?>
                                        </span>
                                        <span class="text-gray-500 text-base font-century">
                                            / KSh <?php echo number_format($book['price_kes'] ?? ($book['price'] * 130), 0); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Book Meta Info -->
                                <?php if ($book['pages']): ?>
                                    <p class="text-gray-500 text-xs mb-4 font-century flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        <?php echo (int)$book['pages']; ?> pages
                                    </p>
                                <?php endif; ?>

                                <!-- Action Button -->
                                <?php if ($book['status'] === 'published'): ?>
                                    <a 
                                        href="book.php?slug=<?php echo htmlspecialchars($book['slug']); ?>" 
                                        class="block w-full bg-gold text-plum font-bold text-center py-3 rounded-full hover:bg-opacity-90 transition-all shadow-md font-montserrat"
                                    >
                                        View Details →
                                    </a>
                                <?php elseif ($book['preview_available']): ?>
                                    <a 
                                        href="book.php?slug=<?php echo htmlspecialchars($book['slug']); ?>" 
                                        class="block w-full bg-plum text-cream font-bold text-center py-3 rounded-full hover:bg-opacity-90 transition-all shadow-md font-montserrat"
                                    >
                                        Read Preview →
                                    </a>
                                <?php else: ?>
                                    <button 
                                        class="w-full bg-gray-200 text-gray-500 font-bold text-center py-3 rounded-full cursor-not-allowed font-montserrat"
                                        disabled
                                    >
                                        Coming Soon
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-20">
                    <div class="text-6xl mb-4 opacity-30">📚</div>
                    <p class="text-gray-500 text-lg mb-2 font-montserrat">No books available yet</p>
                    <p class="text-gray-400 text-sm font-century">Check back soon for new releases!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- NEWSLETTER CTA with different background -->
    <section class="bg-gradient-to-br from-plum to-purple-900 py-16 px-4">
        <div class="max-w-4xl mx-auto text-center fade-in-up">
            <h2 class="font-montserrat font-bold text-cream text-3xl mb-3">Get Notified When Books Launch</h2>
            <p class="text-cream text-opacity-90 mb-8 font-century">
                Be the first to know when new books are available for purchase.
            </p>
            
            <?php
            $context = 'shop';
            include __DIR__ . '/../includes/newsletter-signup.php';
            ?>
            
            <p class="text-cream text-sm italic mt-4 font-century">
                No spam. Just book launch updates and occasional writing news.
            </p>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
    <script src="<?php echo basePath(); ?>assets/js/newsletter.js"></script>
</body>
</html>

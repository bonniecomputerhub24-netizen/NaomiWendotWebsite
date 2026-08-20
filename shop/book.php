<?php
/**
 * Book Detail Page with Simple Cart
 * Individual book view with purchase options
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Get book slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: index.php");
    exit;
}

// Fetch book details
$book = null;
$chapters = [];
try {
    $db = getDb();
    
    // Get book
    $stmt = $db->prepare("SELECT * FROM books WHERE slug = ? AND status IN ('published', 'coming_soon')");
    $stmt->execute([$slug]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$book) {
        header("Location: index.php");
        exit;
    }
    
    // Get preview chapters if available
    if ($book['preview_available']) {
        $stmt = $db->prepare("
            SELECT * FROM book_chapters 
            WHERE book_id = ? AND is_preview = 1 AND status = 'published'
            ORDER BY chapter_number ASC
        ");
        $stmt->execute([$book['id']]);
        $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error fetching book: " . $e->getMessage());
    header("Location: index.php");
    exit;
}

$pageTitle = htmlspecialchars($book['title']) . ' — Shop — Naomi Wendot';
$metaDescription = htmlspecialchars($book['description'] ?? 'A book by Naomi Wendot');

// Get user's preferred currency
$preferredCurrency = isset($_COOKIE['preferred_currency']) ? $_COOKIE['preferred_currency'] : 'USD';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $metaDescription; ?>">
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo basePath(); ?>assets/css/custom.css">
</head>
<body class="font-century bg-cream">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- BOOK DETAIL SECTION -->
    <section class="py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- Book Cover -->
                <div class="sticky top-24">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <?php if ($book['cover_image']): ?>
                            <img 
                                src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                alt="<?php echo htmlspecialchars($book['title']); ?>"
                                class="w-full h-auto object-cover"
                            >
                        <?php else: ?>
                            <div class="w-full h-96 bg-gradient-to-br from-plum to-purple-800 flex items-center justify-center">
                                <span class="text-9xl text-white opacity-40">📖</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Book Info -->
                <div>
                    <!-- Category Badge -->
                    <?php if ($book['category']): ?>
                        <span class="inline-block px-4 py-1 bg-gold bg-opacity-10 text-gold text-sm font-semibold rounded-full mb-4 font-montserrat">
                            <?php echo htmlspecialchars($book['category']); ?>
                        </span>
                    <?php endif; ?>

                    <!-- Title -->
                    <h1 class="font-montserrat font-bold text-plum text-4xl md:text-5xl mb-3">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </h1>

                    <!-- Subtitle -->
                    <?php if ($book['subtitle']): ?>
                        <p class="text-gray-600 text-xl italic mb-6 font-century">
                            <?php echo htmlspecialchars($book['subtitle']); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Author -->
                    <p class="text-gray-700 mb-6 font-century">
                        by <span class="font-semibold">Naomi Wendot</span>
                    </p>

                    <!-- Description -->
                    <div class="prose prose-lg mb-8 font-century">
                        <p class="text-charcoal leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                        </p>
                    </div>

                    <!-- Book Details -->
                    <div class="bg-rose rounded-lg p-6 mb-8">
                        <div class="grid grid-cols-2 gap-4 text-sm font-century">
                            <?php if ($book['pages']): ?>
                                <div>
                                    <span class="text-gray-600">Pages:</span>
                                    <span class="font-semibold text-plum ml-2"><?php echo (int)$book['pages']; ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($book['isbn']): ?>
                                <div>
                                    <span class="text-gray-600">ISBN:</span>
                                    <span class="font-semibold text-plum ml-2"><?php echo htmlspecialchars($book['isbn']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div>
                                <span class="text-gray-600">Status:</span>
                                <span class="font-semibold text-plum ml-2">
                                    <?php echo $book['status'] === 'published' ? 'Available Now' : 'Coming Soon'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Price & Purchase Section -->
                    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                        <!-- Price Display - Both Currencies -->
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <p class="text-gray-600 text-sm mb-2 font-century">Price</p>
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <span class="text-plum font-bold text-3xl font-montserrat">
                                    $<?php echo number_format($book['price_usd'] ?? $book['price'], 2); ?>
                                </span>
                                <span class="text-gray-500 text-xl font-century">
                                    / KSh <?php echo number_format($book['price_kes'] ?? ($book['price'] * 130), 0); ?>
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mt-2 font-century">
                                <?php echo $book['status'] === 'published' ? 'Available for purchase' : 'Pre-order price'; ?>
                            </p>
                        </div>

                        <!-- Purchase Button -->
                        <?php if ($book['status'] === 'published' || $book['pre_order']): ?>
                            <button 
                                onclick="addToCart(<?php echo $book['id']; ?>)"
                                class="w-full bg-gold text-plum font-bold py-4 rounded-full hover:bg-opacity-90 transition-all shadow-md font-montserrat text-lg mb-4"
                            >
                                <?php echo $book['status'] === 'published' ? '🛒 Add to Cart' : '📦 Pre-Order Now'; ?>
                            </button>
                        <?php else: ?>
                            <button 
                                disabled
                                class="w-full bg-gray-300 text-gray-600 font-bold py-4 rounded-full cursor-not-allowed font-montserrat text-lg mb-4"
                            >
                                Coming Soon
                            </button>
                        <?php endif; ?>

                        <p class="text-center text-gray-600 text-sm font-century">
                            Secure checkout • Instant digital delivery
                        </p>
                    </div>

                    <!-- Preview Chapters -->
                    <?php if (!empty($chapters)): ?>
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="font-montserrat font-bold text-plum text-xl mb-4">
                                📖 Free Preview Chapters
                            </h3>
                            <ul class="space-y-3">
                                <?php foreach ($chapters as $chapter): ?>
                                    <li>
                                        <a 
                                            href="#chapter-<?php echo $chapter['id']; ?>" 
                                            class="flex items-center justify-between p-3 hover:bg-rose rounded-lg transition-all font-century"
                                        >
                                            <span class="font-semibold text-plum">
                                                Chapter <?php echo $chapter['chapter_number']; ?>: <?php echo htmlspecialchars($chapter['title']); ?>
                                            </span>
                                            <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Preview Chapter Content -->
            <?php if (!empty($chapters)): ?>
                <div class="mt-16">
                    <div class="max-w-4xl mx-auto">
                        <?php foreach ($chapters as $chapter): ?>
                            <div id="chapter-<?php echo $chapter['id']; ?>" class="bg-white rounded-xl shadow-md p-8 md:p-12 mb-8">
                                <div class="mb-6">
                                    <span class="text-gold text-sm font-semibold uppercase tracking-wider font-montserrat">Chapter <?php echo $chapter['chapter_number']; ?></span>
                                    <h2 class="font-montserrat font-bold text-plum text-3xl mt-2">
                                        <?php echo htmlspecialchars($chapter['title']); ?>
                                    </h2>
                                </div>
                                <div class="prose prose-lg max-w-none font-century">
                                    <?php 
                                    // Simple Markdown to HTML conversion
                                    $content = htmlspecialchars($chapter['content']);
                                    $content = preg_replace('/^# (.+)$/m', '<h1 class="text-3xl font-bold text-plum mb-4 font-montserrat">$1</h1>', $content);
                                    $content = preg_replace('/^## (.+)$/m', '<h2 class="text-2xl font-bold text-plum mb-3 mt-6 font-montserrat">$1</h2>', $content);
                                    $content = preg_replace('/^\*\*(.+?)\*\*$/m', '<p class="font-semibold mb-4">$1</p>', $content);
                                    $content = preg_replace('/^\*(.+?)\*$/m', '<p class="italic text-gray-700 mb-4">$1</p>', $content);
                                    $content = nl2br($content);
                                    echo $content;
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- NEWSLETTER CTA with different background -->
    <section class="bg-gradient-to-br from-plum to-purple-900 py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
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

    <!-- Sliding Cart Panel -->
    <div id="cart-panel" class="fixed top-0 right-0 h-full w-full md:w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
        <!-- Header -->
        <div class="bg-plum text-cream p-6 flex items-center justify-between">
            <h3 class="font-montserrat font-bold text-xl">Shopping Cart</h3>
            <button onclick="closeCartPanel()" class="text-cream hover:text-gold transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Cart Items -->
        <div id="cart-panel-items" class="flex-1 overflow-y-auto p-6 space-y-4"></div>

        <!-- Footer with Total and Checkout -->
        <div class="border-t border-gray-200 p-6 bg-gray-50">
            <div class="mb-4 space-y-2">
                <div class="flex items-center justify-between text-sm font-century">
                    <span class="text-gray-600">Subtotal (USD):</span>
                    <span id="cart-panel-total-usd" class="font-semibold text-plum">$0.00</span>
                </div>
                <div class="flex items-center justify-between text-sm font-century">
                    <span class="text-gray-600">Subtotal (KES):</span>
                    <span id="cart-panel-total-kes" class="font-semibold text-plum">KSh 0</span>
                </div>
            </div>
            <a 
                href="cart.php"
                class="block w-full bg-gold text-plum font-bold text-center py-3 rounded-full hover:bg-opacity-90 transition-all mb-3 font-montserrat"
            >
                View Cart & Checkout →
            </a>
            <button 
                onclick="closeCartPanel()"
                class="block w-full text-center text-gray-600 hover:text-plum transition-colors font-century text-sm"
            >
                Continue Shopping
            </button>
        </div>
    </div>

    <!-- Overlay for Cart Panel -->
    <div id="cart-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40" onclick="closeCartPanel()"></div>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
    <script src="<?php echo basePath(); ?>assets/js/newsletter.js"></script>
    
    <script>
    // Simple Cart Functionality with Sliding Panel
    function addToCart(bookId) {
        // Get current cart from localStorage
        let cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        
        // Check if book already in cart
        const existingItem = cart.find(item => item.id === bookId);
        if (existingItem) {
            existingItem.quantity += 1;
            localStorage.setItem('naomi_cart', JSON.stringify(cart));
            showNotification('Quantity updated! Now ' + existingItem.quantity + ' in cart.', 'success');
            updateCartBadge();
            openCartPanel();
            return;
        }
        
        // Add book to cart
        cart.push({
            id: bookId,
            title: '<?php echo addslashes($book['title']); ?>',
            price_usd: <?php echo $book['price_usd'] ?? $book['price']; ?>,
            price_kes: <?php echo $book['price_kes'] ?? ($book['price'] * 130); ?>,
            cover: '<?php echo addslashes($book['cover_image']); ?>',
            quantity: 1
        });
        
        localStorage.setItem('naomi_cart', JSON.stringify(cart));
        showNotification('Book added to cart!', 'success');
        updateCartBadge();
        openCartPanel();
    }
    
    // Open Cart Panel
    function openCartPanel() {
        document.getElementById('cart-panel').classList.remove('translate-x-full');
        document.getElementById('cart-overlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        loadCartPanel();
    }
    
    // Close Cart Panel
    function closeCartPanel() {
        document.getElementById('cart-panel').classList.add('translate-x-full');
        document.getElementById('cart-overlay').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // Load Cart Panel Content
    function loadCartPanel() {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        const itemsContainer = document.getElementById('cart-panel-items');
        
        if (cart.length === 0) {
            itemsContainer.innerHTML = `
                <div class="text-center py-12">
                    <div class="text-5xl mb-3">🛒</div>
                    <p class="text-gray-500 font-century">Your cart is empty</p>
                </div>
            `;
            document.getElementById('cart-panel-total-usd').textContent = '$0.00';
            document.getElementById('cart-panel-total-kes').textContent = 'KSh 0';
            return;
        }
        
        itemsContainer.innerHTML = cart.map((item, index) => `
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex gap-3">
                    <img 
                        src="${item.cover}" 
                        alt="${item.title}"
                        class="w-16 h-20 object-cover rounded flex-shrink-0"
                    >
                    <div class="flex-1 min-w-0">
                        <h4 class="font-montserrat font-semibold text-plum text-sm mb-1 line-clamp-2">${item.title}</h4>
                        <p class="text-xs text-gray-600 mb-2 font-century">
                            $${item.price_usd.toFixed(2)} / KSh ${Math.round(item.price_kes).toLocaleString()}
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button 
                                    onclick="updatePanelQuantity(${index}, -1)"
                                    class="w-6 h-6 rounded-full border border-gold text-gold hover:bg-gold hover:text-plum transition-all flex items-center justify-center text-sm"
                                >
                                    −
                                </button>
                                <span class="text-sm font-semibold text-plum font-montserrat w-6 text-center">${item.quantity}</span>
                                <button 
                                    onclick="updatePanelQuantity(${index}, 1)"
                                    class="w-6 h-6 rounded-full border border-gold text-gold hover:bg-gold hover:text-plum transition-all flex items-center justify-center text-sm"
                                >
                                    +
                                </button>
                            </div>
                            <button 
                                onclick="removePanelItem(${index})"
                                class="text-red-600 hover:text-red-800 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        updatePanelTotals();
    }
    
    // Update quantity in panel
    function updatePanelQuantity(index, change) {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        if (cart[index]) {
            cart[index].quantity = Math.max(1, (cart[index].quantity || 1) + change);
            localStorage.setItem('naomi_cart', JSON.stringify(cart));
            loadCartPanel();
            updateCartBadge();
        }
    }
    
    // Remove item from panel
    function removePanelItem(index) {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        cart.splice(index, 1);
        localStorage.setItem('naomi_cart', JSON.stringify(cart));
        loadCartPanel();
        updateCartBadge();
    }
    
    // Update panel totals
    function updatePanelTotals() {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        let totalUSD = 0;
        let totalKES = 0;
        
        cart.forEach(item => {
            const qty = item.quantity || 1;
            totalUSD += item.price_usd * qty;
            totalKES += item.price_kes * qty;
        });
        
        document.getElementById('cart-panel-total-usd').textContent = '$' + totalUSD.toFixed(2);
        document.getElementById('cart-panel-total-kes').textContent = 'KSh ' + Math.round(totalKES).toLocaleString();
    }
    
    // Show notification
    function showNotification(message, type = 'success') {
        // Remove existing notifications
        const existing = document.querySelector('.cart-notification');
        if (existing) existing.remove();
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = 'cart-notification fixed top-24 right-4 z-50 bg-white shadow-2xl rounded-lg p-4 flex items-center gap-3 animate-slide-in';
        notification.style.minWidth = '300px';
        
        const icon = type === 'success' 
            ? '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            : '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        
        notification.innerHTML = `
            ${icon}
            <div class="flex-1">
                <p class="font-semibold text-plum font-montserrat">${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Update cart badge
    function updateCartBadge() {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
        
        const badgeDesktop = document.getElementById('cart-badge-desktop');
        const badgeMobile = document.getElementById('cart-badge-mobile');
        
        if (totalItems > 0) {
            if (badgeDesktop) {
                badgeDesktop.textContent = totalItems;
                badgeDesktop.classList.remove('hidden');
            }
            if (badgeMobile) {
                badgeMobile.textContent = totalItems;
                badgeMobile.classList.remove('hidden');
            }
        }
    }
    
    // Add CSS for animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slide-in {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>

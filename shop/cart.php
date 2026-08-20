<?php
/**
 * Shopping Cart Page
 * Simple cart management with localStorage
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Shopping Cart — Naomi Wendot';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <!-- CART SECTION -->
    <section class="py-20 px-4 min-h-screen">
        <div class="max-w-5xl mx-auto">
            <h1 class="font-montserrat font-bold text-plum text-4xl mb-8">Shopping Cart</h1>

            <!-- Empty Cart State -->
            <div id="empty-cart" class="hidden bg-white rounded-xl shadow-md p-12 text-center">
                <div class="text-6xl mb-4">🛒</div>
                <h2 class="font-montserrat font-bold text-2xl text-plum mb-3">Your cart is empty</h2>
                <p class="text-gray-600 mb-6 font-century">Add some books to get started!</p>
                <a 
                    href="index.php" 
                    class="inline-block px-8 py-3 bg-gold text-plum font-semibold rounded-full hover:bg-opacity-90 transition-all font-montserrat"
                >
                    Browse Books
                </a>
            </div>

            <!-- Cart Items -->
            <div id="cart-items" class="space-y-4 mb-8"></div>

            <!-- Cart Summary -->
            <div id="cart-summary" class="hidden bg-white rounded-xl shadow-lg p-8">
                <h2 class="font-montserrat font-bold text-xl text-plum mb-6">Order Summary</h2>
                
                <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
                    <div class="flex items-center justify-between font-century">
                        <span class="text-gray-600">Subtotal (USD):</span>
                        <span id="cart-total-usd" class="font-semibold text-plum">$0.00</span>
                    </div>
                    <div class="flex items-center justify-between font-century">
                        <span class="text-gray-600">Subtotal (KES):</span>
                        <span id="cart-total-kes" class="font-semibold text-plum">KSh 0</span>
                    </div>
                </div>

                <button 
                    onclick="proceedToCheckout()"
                    class="w-full bg-gold text-plum font-bold py-4 rounded-full hover:bg-opacity-90 transition-all shadow-md font-montserrat text-lg mb-4"
                >
                    Proceed to Checkout →
                </button>

                <a 
                    href="index.php" 
                    class="block w-full text-center text-gray-600 hover:text-plum transition-colors font-century"
                >
                    ← Continue Shopping
                </a>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
    
    <script>
    // Load and display cart
    function loadCart() {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCart = document.getElementById('empty-cart');
        const cartSummary = document.getElementById('cart-summary');

        if (cart.length === 0) {
            emptyCart.classList.remove('hidden');
            cartSummary.classList.add('hidden');
            cartItemsContainer.innerHTML = '';
            return;
        }

        emptyCart.classList.add('hidden');
        cartSummary.classList.remove('hidden');

        // Render cart items
        cartItemsContainer.innerHTML = cart.map((item, index) => `
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex gap-6">
                    <img 
                        src="${item.cover}" 
                        alt="${item.title}"
                        class="w-24 h-32 object-cover rounded-lg flex-shrink-0"
                    >
                    <div class="flex-1">
                        <h3 class="font-montserrat font-bold text-plum text-xl mb-2">${item.title}</h3>
                        <p class="text-gray-600 mb-3 font-century text-sm">by Naomi Wendot</p>
                        
                        <!-- Price - Both Currencies -->
                        <div class="mb-4">
                            <div class="flex items-baseline gap-2">
                                <span class="font-montserrat font-bold text-lg text-plum">
                                    $${item.price_usd.toFixed(2)}
                                </span>
                                <span class="text-gray-500 text-sm font-century">
                                    / KSh ${Math.round(item.price_kes).toLocaleString()}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Quantity Controls -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-gray-600 text-sm font-century">Quantity:</span>
                                <div class="flex items-center gap-2">
                                    <button 
                                        onclick="updateQuantity(${index}, -1)"
                                        class="w-8 h-8 rounded-full border-2 border-gold text-gold hover:bg-gold hover:text-plum transition-all flex items-center justify-center font-bold"
                                    >
                                        −
                                    </button>
                                    <span class="w-12 text-center font-semibold text-plum font-montserrat">${item.quantity}</span>
                                    <button 
                                        onclick="updateQuantity(${index}, 1)"
                                        class="w-8 h-8 rounded-full border-2 border-gold text-gold hover:bg-gold hover:text-plum transition-all flex items-center justify-center font-bold"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                            <button 
                                onclick="removeFromCart(${index})"
                                class="text-red-600 hover:text-red-800 transition-colors font-century text-sm flex items-center gap-1"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        updateTotals();
        updateCartBadge();
    }

    // Update item quantity
    function updateQuantity(index, change) {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        
        if (cart[index]) {
            cart[index].quantity = Math.max(1, (cart[index].quantity || 1) + change);
            localStorage.setItem('naomi_cart', JSON.stringify(cart));
            loadCart();
        }
    }

    // Update cart totals
    function updateTotals() {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        
        let totalUSD = 0;
        let totalKES = 0;
        
        cart.forEach(item => {
            const qty = item.quantity || 1;
            totalUSD += item.price_usd * qty;
            totalKES += item.price_kes * qty;
        });
        
        document.getElementById('cart-total-usd').textContent = '$' + totalUSD.toFixed(2);
        document.getElementById('cart-total-kes').textContent = 'KSh ' + Math.round(totalKES).toLocaleString();
    }

    // Remove item from cart
    function removeFromCart(index) {
        if (confirm('Remove this book from cart?')) {
            const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
            cart.splice(index, 1);
            localStorage.setItem('naomi_cart', JSON.stringify(cart));
            loadCart();
        }
    }

    // Update cart badge
    function updateCartBadge() {
        const cart = JSON.parse(localStorage.getItem('naomi_cart') || '[]');
        const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
        
        const badgeDesktop = document.getElementById('cart-badge-desktop');
        const badgeMobile = document.getElementById('cart-badge-mobile');
        
        if (badgeDesktop) {
            badgeDesktop.textContent = totalItems;
            if (totalItems > 0) {
                badgeDesktop.classList.remove('hidden');
            } else {
                badgeDesktop.classList.add('hidden');
            }
        }
        
        if (badgeMobile) {
            badgeMobile.textContent = totalItems;
            if (totalItems > 0) {
                badgeMobile.classList.remove('hidden');
            } else {
                badgeMobile.classList.add('hidden');
            }
        }
    }

    // Proceed to checkout
    function proceedToCheckout() {
        alert('Checkout functionality coming soon! For now, please contact us directly to complete your purchase.');
        // TODO: Implement actual checkout (Stripe, PayPal, M-Pesa, etc.)
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadCart();
    });
    </script>
</body>
</html>

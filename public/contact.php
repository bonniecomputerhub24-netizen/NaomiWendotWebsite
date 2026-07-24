<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Contact — Naomi Wendot";
$metaDescription = "Get in touch with Naomi Wendot — send a message, read what her writing has meant to others, or find answers to common questions.";
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

    <!-- HERO -->
    <section id="hero" class="hero-parallax relative h-[50vh] md:h-[60vh] flex items-center justify-center" style="background-image: url('<?php echo basePath(); ?>assets/images/Archives1.png');">
        <!-- Overlay -->
        <div class="hero-overlay absolute inset-0" style="background: linear-gradient(135deg, rgba(74,25,66,0.85), rgba(74,25,66,0.75));"></div>
        
        <!-- Content -->
        <div class="relative z-20 text-center px-4 fade-in-up">
            <!-- Breadcrumb -->
            <div class="text-sm mb-6">
                <a href="index.php" class="text-gold hover:underline font-inter">Home</a>
                <span class="text-cream text-opacity-60 mx-2">></span>
                <span class="text-cream font-bold font-inter">Contact</span>
            </div>
            
            <!-- Heading -->
            <h1 class="font-playfair font-bold text-cream text-5xl md:text-6xl mb-4">
                Get In Touch
            </h1>
            
            <!-- Subtext -->
            <p class="text-gold italic text-lg md:text-xl font-playfair">
                Naomi would love to hear from you.
            </p>
        </div>
    </section>

    <!-- SECTION 1: WHAT READERS SAY -->
    <section id="testimonials" class="bg-cream py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest font-inter mb-3">READER TESTIMONIES</p>
                <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl">What Readers Say</h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <!-- Testimonials Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                // Testimonial 1
                $quote = "Her poems on faith and hope have become a daily refuge.";
                $name = "Onduso Bonface";
                $location = "Kenya";
                $bg = "rose";
                include __DIR__ . '/../includes/testimonial-card.php';
                ?>
                
                <?php
                // Testimonial 2
                $quote = "The authenticity in her writing feels like a conversation with a dear friend.";
                $name = "Margaret Wendot";
                $location = "Kenya";
                $bg = "rose";
                include __DIR__ . '/../includes/testimonial-card.php';
                ?>
                
                <?php
                // Testimonial 3
                $quote = "Her testimonies have transformed how I see God's presence in my life.";
                $name = "Elizabeth W.";
                $location = "Mombasa";
                $bg = "rose";
                include __DIR__ . '/../includes/testimonial-card.php';
                ?>
            </div>
        </div>
    </section>

    <!-- SECTION 2: CONTACT FORM + DIRECT CONTACT CARD -->
    <section id="send-a-message" class="bg-rose py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                <!-- LEFT: Contact Form (60% - 3 columns) -->
                <div class="md:col-span-3 fade-in-up">
                    <div class="bg-white rounded-2xl shadow-lg p-8 md:p-10">
                        <p class="text-gold uppercase text-sm tracking-widest font-inter mb-3">SEND A MESSAGE</p>
                        <h2 class="font-playfair font-bold text-plum text-3xl mb-3">Say Hello</h2>
                        <div class="gold-underline mb-6" style="margin-left: 0;"></div>
                        
                        <form id="contact-form" class="space-y-6">
                            <!-- Name Field -->
                            <div>
                                <label for="name" class="block text-plum text-sm font-semibold font-inter mb-1">
                                    Your Name <span class="text-gold">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                                    placeholder="Enter your name"
                                >
                            </div>
                            
                            <!-- Email Field -->
                            <div>
                                <label for="email" class="block text-plum text-sm font-semibold font-inter mb-1">
                                    Your Email <span class="text-gold">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                                    placeholder="your.email@example.com"
                                >
                            </div>
                            
                            <!-- Message Field -->
                            <div>
                                <label for="message" class="block text-plum text-sm font-semibold font-inter mb-1">
                                    Message <span class="text-gold">*</span>
                                </label>
                                <textarea 
                                    id="message" 
                                    name="message" 
                                    rows="5" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent resize-none"
                                    placeholder="Your message to Naomi..."
                                ></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <button 
                                type="submit" 
                                class="w-full bg-plum text-cream py-4 rounded-xl font-bold font-inter hover:bg-opacity-90 transition-all duration-300"
                            >
                                Send Message →
                            </button>
                            
                            <!-- Note -->
                            <p class="text-plum italic text-sm text-center font-inter mt-4">
                                Naomi personally reads every message and will respond as soon as she is able.
                            </p>
                            
                            <!-- Success Message (Hidden) -->
                            <div id="form-success" class="hidden bg-gold bg-opacity-10 border border-gold rounded-xl p-4 text-center">
                                <p class="text-plum font-semibold font-inter">Thank you! Your message has been sent.</p>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- RIGHT: Direct Contact Card (40% - 2 columns) -->
                <div class="md:col-span-2 fade-in-up" style="transition-delay: 200ms;">
                    <div class="bg-plum rounded-2xl p-8 text-cream md:sticky md:top-24">
                        <h3 class="font-playfair font-bold text-xl md:text-2xl mb-6">
                            Contact Naomi Directly
                        </h3>
                        <div class="w-16 h-0.5 bg-gold mb-6"></div>
                        
                        <!-- Email -->
                        <div class="mb-6">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-gold bg-opacity-20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-gold text-xs uppercase tracking-wide font-inter">Email</span>
                            </div>
                            <a href="mailto:<?php echo e(SITE_EMAIL); ?>" class="text-cream text-lg hover:text-gold transition-colors font-inter">
                                <?php echo e(SITE_EMAIL); ?>
                            </a>
                        </div>
                        
                        <!-- WhatsApp -->
                        <div class="mb-8">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-gold bg-opacity-20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gold" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"></path>
                                    </svg>
                                </div>
                                <span class="text-gold text-xs uppercase tracking-wide font-inter">WhatsApp</span>
                            </div>
                            <a href="<?php echo e(whatsappLink('Hello Naomi, I found your website and wanted to connect.')); ?>" target="_blank" rel="noopener noreferrer" class="text-cream text-lg hover:text-gold transition-colors font-inter">
                                <?php echo e(WHATSAPP_DISPLAY); ?>
                            </a>
                        </div>
                        
                        <div class="w-16 h-0.5 bg-gold mb-6"></div>
                        
                        <!-- WhatsApp CTA Button -->
                        <a 
                            href="<?php echo e(whatsappLink('Hello Naomi, I found your website and wanted to connect.')); ?>" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="block w-full bg-gold text-plum text-center py-4 rounded-xl font-bold font-inter hover:scale-105 transition-transform duration-300"
                        >
                            💬 WhatsApp Naomi →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: FAQ -->
    <section id="faq" class="bg-cream py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest font-inter mb-3">COMMON QUESTIONS</p>
                <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl">Frequently Asked Questions</h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <!-- FAQ Accordion -->
            <div class="max-w-4xl mx-auto space-y-4">
                <!-- FAQ 1 -->
                <div class="faq-item bg-white rounded-xl shadow-sm fade-in-up" style="transition-delay: 100ms;">
                    <button class="faq-question w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-playfair font-semibold text-plum text-base pr-4">
                            Does Naomi do speaking engagements?
                        </span>
                        <svg class="faq-chevron w-5 h-5 text-gold transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="faq-answer hidden px-6 pb-5">
                        <p class="text-charcoal text-sm font-inter leading-relaxed">
                            Yes — Naomi is glad to consider speaking invitations, particularly for church and education settings. Reach out through the message form above with details about your event.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ 2 -->
                <div class="faq-item bg-white rounded-xl shadow-sm fade-in-up" style="transition-delay: 200ms;">
                    <button class="faq-question w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-playfair font-semibold text-plum text-base pr-4">
                            Can I share her writing on my social media?
                        </span>
                        <svg class="faq-chevron w-5 h-5 text-gold transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="faq-answer hidden px-6 pb-5">
                        <p class="text-charcoal text-sm font-inter leading-relaxed">
                            Yes, with attribution to Naomi Wendot. She writes to bless others, and sharing her words freely — with credit — is always welcome.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ 3 -->
                <div class="faq-item bg-white rounded-xl shadow-sm fade-in-up" style="transition-delay: 300ms;">
                    <button class="faq-question w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-playfair font-semibold text-plum text-base pr-4">
                            Do you write custom poems for special occasions?
                        </span>
                        <svg class="faq-chevron w-5 h-5 text-gold transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="faq-answer hidden px-6 pb-5">
                        <p class="text-charcoal text-sm font-inter leading-relaxed">
                            Naomi occasionally writes personalized pieces. Send a message describing the occasion and she'll let you know if she's able to take it on.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ 4 -->
                <div class="faq-item bg-white rounded-xl shadow-sm fade-in-up" style="transition-delay: 400ms;">
                    <button class="faq-question w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="font-playfair font-semibold text-plum text-base pr-4">
                            How can I support her work?
                        </span>
                        <svg class="faq-chevron w-5 h-5 text-gold transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="faq-answer hidden px-6 pb-5">
                        <p class="text-charcoal text-sm font-inter leading-relaxed">
                            The most meaningful support is reading, sharing, and praying for the ministry — and joining the waitlist for the upcoming book, Nature & Bible Verses.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
    
    <!-- Contact Form & FAQ Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // FAQ Accordion
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', () => {
                    const faqItem = question.closest('.faq-item');
                    const answer = faqItem.querySelector('.faq-answer');
                    const chevron = question.querySelector('.faq-chevron');
                    const isOpen = !answer.classList.contains('hidden');
                    
                    // Close all other FAQs
                    document.querySelectorAll('.faq-item').forEach(item => {
                        if (item !== faqItem) {
                            item.querySelector('.faq-answer').classList.add('hidden');
                            item.querySelector('.faq-chevron').classList.remove('rotate-180');
                            item.classList.remove('border-l-4', 'border-gold');
                        }
                    });
                    
                    // Toggle current FAQ
                    if (isOpen) {
                        answer.classList.add('hidden');
                        chevron.classList.remove('rotate-180');
                        faqItem.classList.remove('border-l-4', 'border-gold');
                    } else {
                        answer.classList.remove('hidden');
                        chevron.classList.add('rotate-180');
                        faqItem.classList.add('border-l-4', 'border-gold');
                    }
                });
            });
            
            // Contact Form Validation
            const contactForm = document.getElementById('contact-form');
            const formSuccess = document.getElementById('form-success');
            
            contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const message = document.getElementById('message').value.trim();
                
                // Basic validation
                if (!name || !email || !message) {
                    alert('Please fill in all required fields.');
                    return;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Please enter a valid email address.');
                    return;
                }
                
                // Show success message (no backend yet)
                contactForm.style.display = 'none';
                formSuccess.classList.remove('hidden');
                
                // TODO: Replace with actual backend API call
                console.log('Form submitted:', { name, email, message });
            });
        });
    </script>
</body>
</html>

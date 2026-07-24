<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "About Naomi Wendot — Writer, Poet, Educator";
$metaDescription = "The story, vision, writing philosophy, and teenage testimony of Kenyan Christian writer Naomi Wendot.";
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
    <section id="hero" class="hero-parallax relative h-[420px] flex items-center justify-center" style="background-image: url('<?php echo basePath(); ?>assets/images/Archives3.png');">
        <!-- Overlay -->
        <div class="hero-overlay absolute inset-0" style="background: linear-gradient(135deg, rgba(74,25,66,0.85), rgba(74,25,66,0.75));"></div>
        
        <!-- Content -->
        <div class="relative z-20 text-center px-4 fade-in-up">
            <!-- Breadcrumb -->
            <div class="text-sm mb-6">
                <a href="index.php" class="text-gold hover:underline font-inter">Home</a>
                <span class="text-cream text-opacity-60 mx-2">></span>
                <span class="text-cream font-bold font-inter">About</span>
            </div>
            
            <!-- Heading -->
            <h1 class="font-playfair font-bold text-cream text-4xl md:text-5xl lg:text-6xl mb-4">
                About Her
            </h1>
            
            <!-- Subtext -->
            <p class="text-gold italic text-lg md:text-xl font-playfair">
                Her story. Her faith. Her words.
            </p>
        </div>
    </section>

    <!-- SECTION 1: WHO SHE IS -->
    <section id="who-she-is" class="bg-cream py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- Mobile: Portrait First -->
                <div class="md:order-2 fade-in-up" style="transition-delay: 200ms;">
                    <div class="max-w-sm mx-auto">
                        <img 
                            src="<?php echo basePath(); ?>assets/images/NaomiWendotProfileImage.png" 
                            alt="Naomi Wendot — EDUCARE Trainer of Trainers" 
                            class="w-full aspect-[3/4] object-cover rounded-2xl border-2 border-gold shadow-xl tilt-card float-card"
                            loading="lazy"
                        >
                        <p class="text-plum text-sm italic text-center mt-3 font-inter">
                            Naomi Wendot — EDUCARE Trainer of Trainers
                        </p>
                    </div>
                </div>
                
                <!-- Text Content -->
                <div class="md:order-1 fade-in-up">
                    <p class="text-gold uppercase text-xs md:text-sm tracking-widest font-inter mb-3">WHO I AM</p>
                    <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl mb-3">
                        A Voice Rooted in Faith
                    </h2>
                    <div class="gold-underline mb-6" style="margin-left: 0;"></div>
                    
                    <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed mb-4">
                        Naomi Wendot is a Kenyan-born poet, writer, educator, and woman of deep Christian faith. Her words have journeyed from personal journals in 2005 to hearts across Kenya, the United States, and beyond.
                    </p>
                    
                    <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed mb-4">
                        What began as freestyle poems jotted in quiet moments has grown into a body of poetry, articles, daily inspirations, stories, and testimonies — all carrying one thread: that God's grace and peace sustain us through every season.
                    </p>
                    
                    <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed">
                        Beyond writing, Naomi has built a career in Christian-centred education as a trained EDUCARE Trainer of Trainers, and during her growing years was passionate about championing Bible access for unreached communities through Run for the Bibleless.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 2: VISION & MISSION -->
    <section id="vision-mission" class="bg-plum py-20 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Panel 1: Vision -->
                <div class="bg-cream rounded-2xl p-8 md:p-10 border-t-4 border-gold fade-in-up" style="transition-delay: 100ms;">
                    <p class="text-gold uppercase text-xs md:text-sm tracking-widest font-inter mb-3">HER VISION</p>
                    <p class="font-playfair italic text-plum text-xl md:text-2xl leading-relaxed">
                        To see God's word reach every heart — through written words that heal, testimonies that strengthen faith, and Scripture translated into the dialects of the unreached — so that no one is left without access to hope.
                    </p>
                </div>
                
                <!-- Panel 2: Mission -->
                <div class="bg-cream rounded-2xl p-8 md:p-10 border-t-4 border-gold fade-in-up" style="transition-delay: 200ms;">
                    <p class="text-gold uppercase text-xs md:text-sm tracking-widest font-inter mb-3">HER MISSION</p>
                    <p class="font-playfair italic text-plum text-xl md:text-2xl leading-relaxed">
                        To write, teach, and share — faithfully and freely — words rooted in Scripture that guide readers of every age toward purity, hope, and a deeper walk with God, while actively supporting the work of putting God's word into the hands and hearts of the Bibleless.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: WRITING PHILOSOPHY -->
    <section id="writing-philosophy" class="bg-cream py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest font-inter mb-3">HER CORE VALUES</p>
                <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl">
                    Her Writing Philosophy
                </h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Card 1: Write What is True (White) -->
                <div class="bg-white rounded-2xl shadow-sm p-8 border-t-4 border-gold hover:shadow-md transition-shadow fade-in-up" style="transition-delay: 100ms;">
                    <svg class="w-9 h-9 text-gold mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    <h3 class="font-playfair font-bold text-plum text-xl mb-2">
                        Write What is True
                    </h3>
                    <p class="text-charcoal text-sm font-inter leading-relaxed">
                        Every piece comes from a real place. Honesty is the foundation of writing that lasts.
                    </p>
                </div>
                
                <!-- Card 2: Anchor in Faith (Rose) -->
                <div class="bg-rose rounded-2xl shadow-sm p-6 md:p-8 border-t-4 border-gold hover:shadow-md transition-shadow fade-in-up" style="transition-delay: 200ms;">
                    <svg class="w-8 h-8 md:w-9 md:h-9 text-gold mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l-4-4m4 4l4-4m-8-8h8"></path>
                    </svg>
                    <h3 class="font-playfair font-bold text-plum text-lg md:text-xl mb-2">
                        Anchor in Faith
                    </h3>
                    <p class="text-charcoal text-sm font-inter leading-relaxed">
                        Faith in God shapes every word. Writing is a form of worship and witness.
                    </p>
                </div>
                
                <!-- Card 3: Share to Heal (White) -->
                <div class="bg-white rounded-2xl shadow-sm p-6 md:p-8 border-t-4 border-gold hover:shadow-md transition-shadow fade-in-up" style="transition-delay: 300ms;">
                    <svg class="w-8 h-8 md:w-9 md:h-9 text-gold mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <h3 class="font-playfair font-bold text-plum text-lg md:text-xl mb-2">
                        Share to Heal
                    </h3>
                    <p class="text-charcoal text-sm font-inter leading-relaxed">
                        Words shared are words that multiply. Every poem, story, and testimony is a gift offered freely.
                    </p>
                </div>
                
                <!-- Card 4: Guard What You Take In (Rose) -->
                <div class="bg-rose rounded-2xl shadow-sm p-6 md:p-8 border-t-4 border-gold hover:shadow-md transition-shadow fade-in-up" style="transition-delay: 400ms;">
                    <svg class="w-8 h-8 md:w-9 md:h-9 text-gold mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <h3 class="font-playfair font-bold text-plum text-lg md:text-xl mb-2">
                        Guard What You Take In
                    </h3>
                    <p class="text-charcoal text-sm font-inter leading-relaxed">
                        From her own teenage walk, Naomi holds firmly to a principle: what you read and watch shapes who you become. She teaches readers — especially the young — to guard their hearts by guarding their input.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 4: HER JOURNEY (Teenage Testimony) -->
    <section id="her-journey" class="bg-rose py-20 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest font-inter mb-3">HER STORY</p>
                <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl mb-4">
                    A Teenager Guided by the Word
                </h2>
                <div class="gold-underline mb-6"></div>
                
                <p class="text-charcoal text-base md:text-lg font-inter leading-relaxed max-w-2xl mx-auto">
                    As a young woman, Naomi leaned on Scripture to navigate the pressures of her generation. Four verses in particular became anchors in her walk of purity.
                </p>
            </div>
            
            <!-- Verse Cards with Reflections -->
            <div class="space-y-8">
                <!-- Verse 1: Titus 2:11-12 -->
                <div class="fade-in-up" style="transition-delay: 0ms;">
                    <?php
                    $reference = "Titus 2:11-12 (NIV)";
                    $text = "For the grace of God has appeared that offers salvation to all people. It teaches us to say 'No' to ungodliness and worldly passions, and to live self-controlled, upright and godly lives in this present age.";
                    $audienceTags = ['Teens', 'Adults'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                    <div class="mt-4 pl-6 border-l-2 border-gold">
                        <p class="text-charcoal text-base font-inter italic leading-relaxed">
                            "Lord, I pray that You would give me the grace to say No to sin. Give me grace not to look at things that I should not see or listen to things that are not right for me. Give me grace to obey You and my parents. Give me grace to live uprightly and godly in Jesus' name, Amen."
                        </p>
                    </div>
                </div>
                
                <!-- Verse 2: Psalm 119:9 -->
                <div class="fade-in-up" style="transition-delay: 150ms;">
                    <?php
                    $reference = "Psalm 119:9";
                    $text = "How can a young person stay on the path of purity? By living according to your word.";
                    $audienceTags = ['Teens', 'Adults'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                    <div class="mt-4 pl-4 md:pl-6 border-l-2 border-gold">
                        <p class="text-charcoal text-sm md:text-base font-inter italic leading-relaxed">
                            This verse challenged Naomi to guard what she consumed — the magazines, books, and content she allowed into her life. At 19, she made a vow to walk in purity, kept in a personal journal: living according to God's Word was not optional, but essential.
                        </p>
                    </div>
                </div>
                
                <!-- Verse 3: Psalm 119:105 -->
                <div class="fade-in-up" style="transition-delay: 300ms;">
                    <?php
                    $reference = "Psalm 119:105";
                    $text = "Your word is a lamp for my feet, a light on my path.";
                    $audienceTags = ['All'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                    <div class="mt-4 pl-6 border-l-2 border-gold">
                        <p class="text-charcoal text-base font-inter italic leading-relaxed">
                            A constant reminder that God's Word illuminates the way forward — in relationships, decision-making, and daily choices.
                        </p>
                    </div>
                </div>
                
                <!-- Verse 4: Song of Songs 8:4 -->
                <div class="fade-in-up" style="transition-delay: 450ms;">
                    <?php
                    $reference = "Song of Songs 8:4";
                    $text = "Do not arouse or awaken love until it so desires.";
                    $audienceTags = ['Teens', 'Adults'];
                    include __DIR__ . '/../includes/verse-card.php';
                    ?>
                    <div class="mt-4 pl-4 md:pl-6 border-l-2 border-gold">
                        <p class="text-charcoal text-sm md:text-base font-inter italic leading-relaxed">
                            Naomi set clear boundary lines guided by this verse — drawing wisdom from mentors, obeying her elders, coming home early from social gatherings. She chose relationships marked by restraint and respect for God's timing.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: EDUCARE & RUN FOR THE BIBLELESS -->
    <section id="ministry-work" class="bg-cream py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <p class="text-gold uppercase text-xs md:text-sm tracking-widest font-inter mb-3">MINISTRY & EDUCATION</p>
                <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl">
                    Beyond Writing
                </h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <div class="space-y-16">
                <!-- EDUCARE Training -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div class="fade-in-up order-2 md:order-1">
                        <h3 class="font-playfair font-bold text-plum text-2xl md:text-3xl mb-4">
                            EDUCARE Training
                        </h3>
                        <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed mb-4">
                            Glory to God for helping Naomi grow in her teaching career. By His grace, an opportunity opened to receive EDUCARE training — a Christian-centred biblical approach to education.
                        </p>
                        <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed">
                            The training equipped her further in teaching, and she later emerged as a Trainer of Trainers, bringing faith-based education and hope to communities across Kenya.
                        </p>
                    </div>
                    <div class="fade-in-up order-1 md:order-2">
                        <img 
                            src="<?php echo basePath(); ?>assets/images/EducareTraining.png" 
                            alt="EDUCARE Training with Naomi Wendot" 
                            class="w-full rounded-2xl border-2 border-gold shadow-lg"
                            loading="lazy"
                        >
                    </div>
                </div>
                
                <!-- Run for the Bibleless -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div class="fade-in-up">
                        <img 
                            src="<?php echo basePath(); ?>assets/images/RunForTheBibleless.png" 
                            alt="Run for the Bibleless participation" 
                            class="w-full rounded-2xl border-2 border-gold shadow-lg"
                            loading="lazy"
                        >
                    </div>
                    <div class="fade-in-up" style="transition-delay: 200ms;">
                        <h3 class="font-playfair font-bold text-plum text-2xl md:text-3xl mb-4">
                            Run for the Bibleless
                        </h3>
                        <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed mb-4">
                            Championing for more Bibles to reach the unreached in their own dialect. Naomi participated in Run for the Bibleless — a movement to put God's Word into the hands and hearts of communities who have never had Scripture in their own language.
                        </p>
                        <p class="text-charcoal text-sm md:text-base font-inter leading-relaxed">
                            This passion for Bible access continues to shape her ministry vision: that no one should be left without the hope found in God's Word.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 6: CLOSING PULL-QUOTE -->
    <section id="closing-quote" class="bg-gold py-20 px-4">
        <div class="max-w-3xl mx-auto text-center fade-in-up">
            <!-- Decorative Quote Mark -->
            <div class="text-6xl text-plum opacity-30 font-serif leading-none mb-4">"</div>
            
            <!-- Quote Text -->
            <p class="font-playfair italic text-plum text-2xl md:text-3xl leading-relaxed">
                Don't underestimate your prayer as a teenager. Keep praying. Keep trusting in God.
            </p>
            
            <!-- Attribution -->
            <p class="text-plum font-bold text-sm mt-6 font-inter">
                — Naomi Wendot
            </p>
        </div>
    </section>

    <!-- SECTION 7: WHO SHE WRITES FOR -->
    <section id="who-she-writes-for" class="bg-cream py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12 fade-in-up">
                <h2 class="font-playfair font-bold text-plum text-3xl md:text-4xl">
                    Who She Writes For
                </h2>
                <div class="gold-underline mt-3"></div>
            </div>
            
            <!-- Pills -->
            <div class="flex flex-wrap justify-center gap-4 max-w-4xl mx-auto">
                <div class="bg-white border border-gold rounded-full px-6 py-3 shadow-sm fade-in-up" style="transition-delay: 100ms;">
                    <span class="text-plum text-sm font-inter font-medium">
                        Young people navigating purity, identity, and faith
                    </span>
                </div>
                
                <div class="bg-white border border-gold rounded-full px-6 py-3 shadow-sm fade-in-up" style="transition-delay: 200ms;">
                    <span class="text-plum text-sm font-inter font-medium">
                        Adults seeking daily encouragement rooted in Scripture
                    </span>
                </div>
                
                <div class="bg-white border border-gold rounded-full px-6 py-3 shadow-sm fade-in-up" style="transition-delay: 300ms;">
                    <span class="text-plum text-sm font-inter font-medium">
                        Families looking for faith-based content for children
                    </span>
                </div>
                
                <div class="bg-white border border-gold rounded-full px-6 py-3 shadow-sm fade-in-up" style="transition-delay: 400ms;">
                    <span class="text-plum text-sm font-inter font-medium">
                        Communities and ministries working toward Bible access for the unreached
                    </span>
                </div>
                
                <div class="bg-white border border-gold rounded-full px-6 py-3 shadow-sm fade-in-up" style="transition-delay: 500ms;">
                    <span class="text-plum text-sm font-inter font-medium">
                        Anyone in need of words that remind them: you are not alone, God is near
                    </span>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="<?php echo basePath(); ?>assets/js/main.js"></script>
</body>
</html>

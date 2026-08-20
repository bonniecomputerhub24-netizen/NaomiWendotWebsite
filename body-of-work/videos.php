<?php
/**
 * Videos Page - Display all video content
 * Dedicated page for Naomi's short inspirational videos
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/post-functions.php';
require_once __DIR__ . '/../includes/video-functions.php';
require_once __DIR__ . '/../includes/video-card.php';

$pageTitle = 'Videos — Naomi Wendot';
$metaDescription = 'Watch Naomi Wendot\'s inspirational videos — short messages of faith, hope, and encouragement.';

// Get video content
$videos = getVideoContent(50); // Get up to 50 videos
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

    <style>
        .video-card { background: #fff; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 3px 16px rgba(74,25,66,.08); transition: transform .25s, box-shadow .25s; }
        .video-card:hover { transform: translateY(-5px); box-shadow: 0 12px 34px rgba(74,25,66,.14); }
        .video-thumbnail { position: relative; overflow: hidden; background: #000; width: 100%; }
        .video-thumbnail img, .video-thumbnail video { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .video-card:hover .video-thumbnail img, .video-card:hover .video-thumbnail video { transform: scale(1.06); }
        .play-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); transition: background .3s; }
        .video-card:hover .play-overlay { background: rgba(0,0,0,0.4); }
        .play-button { background: rgba(255,255,255,0.9); border-radius: 50%; padding: 0.75rem; transition: background .3s, transform .3s; }
        .video-card:hover .play-button { background: rgba(255,255,255,1); transform: scale(1.1); }
        .duration-badge { position: absolute; bottom: 0.5rem; right: 0.5rem; background: rgba(74,25,66,0.8); color: #D4A017; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; }
        .section-label { font-size: .7rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; color: #D4A017; }
        .gold-bar { height: 3px; width: 3.5rem; border-radius: 9999px; background: #D4A017; margin: .6rem auto 0; }
    </style>
</head>
<body class="font-century bg-cream">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <!-- HERO -->
    <section class="relative h-[380px] flex items-center justify-center bg-gradient-to-br from-plum via-purple-900 to-plum">
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        
        <div class="relative z-20 text-center px-4">
            <div class="text-sm mb-6">
                <a href="../public/index.php" class="text-gold hover:underline font-century">Home</a>
                <span class="text-cream text-opacity-60 mx-2">›</span>
                <a href="../public/body-of-work.php" class="text-gold hover:underline font-century">Body of Work</a>
                <span class="text-cream text-opacity-60 mx-2">›</span>
                <span class="text-cream font-bold font-century">Videos</span>
            </div>

            <div class="text-6xl mb-4">🎥</div>
            <h1 class="font-montserrat font-bold text-cream text-5xl md:text-6xl mb-4">
                Video Messages
            </h1>

            <p class="text-gold italic text-lg font-montserrat max-w-2xl mx-auto">
                Short inspirational videos sharing messages of faith, hope, and encouragement
            </p>
        </div>
    </section>

    <!-- INTRO -->
    <section class="bg-cream pt-14 pb-6 px-4">
        <div class="max-w-7xl mx-auto text-center">
            <p class="section-label mb-2">Visual Testimonies</p>
            <p class="text-charcoal text-sm max-w-2xl mx-auto">
                Watch and be encouraged. Each video is a moment to pause, reflect, and be reminded of God's faithfulness.
            </p>
            <div class="gold-bar"></div>
        </div>
    </section>

    <!-- VIDEO GRID -->
    <section class="bg-cream py-10 px-4">
        <div class="max-w-7xl mx-auto">
            <?php if (!empty($videos)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($videos as $index => $video): ?>
                        <?php renderVideoCard($video, ['show_views' => true]); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20">
                    <div class="text-6xl mb-4 opacity-30">🎥</div>
                    <p class="text-gray-500 text-lg mb-2">No videos available yet</p>
                    <p class="text-gray-400 text-sm">Check back soon for inspiring video content!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-cream py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="bg-gradient-to-br from-plum to-purple-900 rounded-2xl p-8 md:p-12 text-white">
                <h2 class="font-montserrat font-bold text-3xl mb-4">Stay Connected</h2>
                <p class="text-gold text-lg mb-6">
                    Subscribe to get notified when new videos are published
                </p>
                
                <?php include __DIR__ . '/../includes/newsletter-signup.php'; ?>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <style>
        .line-clamp-2 { 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            overflow: hidden; 
        }
    </style>
</body>
</html>
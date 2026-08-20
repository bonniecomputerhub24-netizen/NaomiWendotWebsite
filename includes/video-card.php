<?php
/**
 * Video Content Card Component
 * Displays video posts with proper embed and metadata
 */

require_once __DIR__ . '/video-functions.php';

/**
 * Render a video content card
 * Supports two layout styles:
 * - 'video-card' (default): dedicated video card styling for videos.php
 * - 'blog-card': matches blog post card styling for mixed content grids
 * 
 * @param array $post Post data with video fields
 * @param array $options Display options
 */
function renderVideoCard($post, $options = []) {
    $showExcerpt = $options['show_excerpt'] ?? true;
    $showDuration = $options['show_duration'] ?? true;
    $showViews = $options['show_views'] ?? false;
    $basePath = $options['base_path'] ?? '../public/';
    $cardStyle = $options['card_style'] ?? 'video-card'; // 'video-card' or 'blog-card'
    
    if (empty($post['video_file'])) {
        return;
    }
    
    $videoPoster = getVideoPoster($post);
    $aspectClass = getVideoCardAspectClass($post);
    $postUrl = $basePath . 'piece-single.php?slug=' . urlencode($post['slug']);
    
    // Use blog-card styling for mixed content grids (matches body-of-work.php inline markup)
    if ($cardStyle === 'blog-card'):
?>
    <article class="blog-card">
        <!-- Video thumbnail -->
        <div class="blog-card-img <?php echo $aspectClass; ?> relative bg-black">
            <a href="<?php echo $postUrl; ?>" class="block relative group video-card-link" data-video-src="<?php echo htmlspecialchars(basePath() . $post['video_file']); ?>">
                <?php if ($videoPoster): ?>
                    <video 
                        class="video-autoplay-card w-full h-full object-cover" 
                        preload="metadata"
                        muted
                        loop
                        playsinline
                        poster="<?php echo htmlspecialchars($videoPoster); ?>"
                        data-src="<?php echo htmlspecialchars(basePath() . $post['video_file']); ?>"
                        data-post-id="<?php echo (int)$post['id']; ?>"
                    >
                        <!-- Video src will be lazy-loaded via IntersectionObserver -->
                    </video>
                <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center">
                        <span class="text-6xl text-white opacity-50">🎥</span>
                    </div>
                <?php endif; ?>
                
                <!-- VIDEO badge -->
                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide" 
                      style="border: 1px solid #D4A017; background: rgba(212,160,23,0.12); color: #D4A017;">
                    🎥 VIDEO
                </span>
                
                <!-- Play button overlay -->
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 group-hover:bg-opacity-40 transition-colors">
                    <div class="play-button-pulse bg-white bg-opacity-90 rounded-full p-3 group-hover:bg-opacity-100 transition-colors">
                        <svg class="w-6 h-6 text-plum" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Duration badge -->
                <?php if ($showDuration && !empty($post['video_duration'])): ?>
                    <span class="absolute bottom-2 right-2 text-xs px-2 py-1 rounded font-semibold" 
                          style="background: rgba(74,25,66,0.8); color: #D4A017;">
                        <?php echo formatVideoDuration($post['video_duration']); ?>
                    </span>
                <?php endif; ?>
                
                <!-- View count badge -->
                <?php if (!empty($post['video_views']) && $post['video_views'] > 0): ?>
                    <span class="absolute bottom-2 left-2 text-[10px] px-1.5 py-0.5 rounded font-semibold" 
                          style="background: rgba(0,0,0,0.75); color: rgba(255,255,255,0.9);">
                        <?php echo formatVideoViews($post['video_views']); ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <div class="p-6 flex flex-col flex-1">
            <span class="inline-block self-start px-3 py-1 rounded-full text-xs font-semibold mb-3 <?php echo getCategoryBadgeClass($post['category_slug'] ?? ''); ?>">
                <?php echo htmlspecialchars($post['category_name'] ?? 'Video'); ?>
            </span>

            <h3 class="font-montserrat font-bold text-plum text-lg mb-2 line-clamp-2">
                <a href="<?php echo $postUrl; ?>" class="hover:text-gold transition-colors">
                    <?php echo htmlspecialchars($post['title']); ?>
                </a>
            </h3>

            <?php if ($showExcerpt): ?>
                <p class="text-charcoal text-sm font-century leading-relaxed mb-4 line-clamp-2 flex-1">
                    <?php echo formatExcerpt($post, 120); ?>
                </p>
            <?php endif; ?>
            
            <!-- Like & View Bar -->
            <div class="mb-4 flex items-center gap-4 text-xs">
                <button 
                    class="video-like-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border transition-all font-semibold"
                    data-post-id="<?php echo (int)$post['id']; ?>"
                    data-likes="<?php echo (int)($post['likes'] ?? 0); ?>"
                    style="border-color: #D4A017; color: #4A1942; background: rgba(212,160,23,0.05);"
                >
                    <span class="like-icon">♡</span>
                    <span class="like-text">Did this bless you?</span>
                    <span class="like-count font-bold" style="color: #D4A017;"><?php echo (int)($post['likes'] ?? 0); ?></span>
                </button>
                
                <?php if (!empty($post['video_views']) && $post['video_views'] > 0): ?>
                    <span class="flex items-center gap-1 text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                        <span><?php echo formatVideoViews($post['video_views']); ?></span>
                    </span>
                <?php endif; ?>
            </div>

            <div class="pt-3 border-t border-gold/20 flex items-center justify-between">
                <span class="text-gray-400 text-xs font-century"><?php echo formatPostDate($post); ?></span>
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
<?php
    else:
        // Default video-card styling for dedicated video pages
?>
    <article class="video-card">
        <a href="<?php echo $postUrl; ?>" class="block video-card-link" data-video-src="<?php echo htmlspecialchars(basePath() . $post['video_file']); ?>">
            <div class="video-thumbnail <?php echo $aspectClass; ?>">
                <?php if ($videoPoster): ?>
                    <video 
                        class="video-autoplay-card w-full h-full object-cover" 
                        preload="metadata"
                        muted
                        loop
                        playsinline
                        poster="<?php echo htmlspecialchars($videoPoster); ?>"
                        data-src="<?php echo htmlspecialchars(basePath() . $post['video_file']); ?>"
                        data-post-id="<?php echo (int)$post['id']; ?>"
                    >
                        <!-- Video src will be lazy-loaded via IntersectionObserver -->
                    </video>
                <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br from-plum via-purple-800 to-plum flex items-center justify-center">
                        <span class="text-6xl text-white opacity-40">🎥</span>
                    </div>
                <?php endif; ?>
                
                <!-- VIDEO badge -->
                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide" 
                      style="border: 1px solid #D4A017; background: rgba(212,160,23,0.12); color: #D4A017;">
                    🎥 VIDEO
                </span>
                
                <!-- Play overlay -->
                <div class="play-overlay">
                    <div class="play-button play-button-pulse">
                        <svg class="w-6 h-6 text-plum" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Duration badge -->
                <?php if ($showDuration && !empty($post['video_duration'])): ?>
                    <span class="duration-badge">
                        <?php echo formatVideoDuration($post['video_duration']); ?>
                    </span>
                <?php endif; ?>
                
                <!-- View count badge -->
                <?php if (!empty($post['video_views']) && $post['video_views'] > 0): ?>
                    <span class="absolute bottom-2 left-2 text-[10px] px-1.5 py-0.5 rounded font-semibold" 
                          style="background: rgba(0,0,0,0.75); color: rgba(255,255,255,0.9);">
                        <?php echo formatVideoViews($post['video_views']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </a>
        
        <div class="p-5">
            <h3 class="font-montserrat font-bold text-plum text-lg mb-2 line-clamp-2">
                <a href="<?php echo $postUrl; ?>" 
                   class="hover:text-gold transition-colors">
                    <?php echo htmlspecialchars($post['title']); ?>
                </a>
            </h3>
            
            <?php if ($showExcerpt && !empty($post['excerpt'])): ?>
                <p class="text-charcoal text-sm leading-relaxed mb-3 line-clamp-2">
                    <?php echo htmlspecialchars($post['excerpt']); ?>
                </p>
            <?php endif; ?>
            
            <!-- Like & View Bar -->
            <div class="mb-3 flex items-center gap-4 text-xs">
                <button 
                    class="video-like-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border transition-all font-semibold"
                    data-post-id="<?php echo (int)$post['id']; ?>"
                    data-likes="<?php echo (int)($post['likes'] ?? 0); ?>"
                    style="border-color: #D4A017; color: #4A1942; background: rgba(212,160,23,0.05);"
                >
                    <span class="like-icon">♡</span>
                    <span class="like-text">Did this bless you?</span>
                    <span class="like-count font-bold" style="color: #D4A017;"><?php echo (int)($post['likes'] ?? 0); ?></span>
                </button>
                
                <?php if (!empty($post['video_views']) && $post['video_views'] > 0): ?>
                    <span class="flex items-center gap-1 text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                        <span><?php echo formatVideoViews($post['video_views']); ?></span>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="pt-3 border-t border-gold/20 flex items-center justify-between">
                <span class="text-gray-400 text-xs">
                    <?php echo date('M j, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?>
                </span>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <?php if ($showViews && !empty($post['views']) && $post['views'] > 0): ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            <?php echo $post['views']; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
<?php
    endif;
}
?>

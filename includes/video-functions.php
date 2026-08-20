<?php
/**
 * Video Helper Functions
 * Handles video URL parsing and embed generation
 */

/**
 * Extract video ID and platform from various video URLs
 * 
 * @param string $url Video URL
 * @return array ['platform' => 'youtube|vimeo|direct', 'id' => 'video_id', 'embed_url' => 'embed_url']
 */
function parseVideoUrl($url) {
    if (empty($url)) {
        return null;
    }
    
    $url = trim($url);
    
    // YouTube patterns
    $youtubePatterns = [
        '/youtube\.com\/watch\?v=([^&\n?#]+)/',
        '/youtube\.com\/embed\/([^&\n?#]+)/',
        '/youtu\.be\/([^&\n?#]+)/',
        '/youtube\.com\/v\/([^&\n?#]+)/',
        '/m\.youtube\.com\/watch\?v=([^&\n?#]+)/',
    ];
    
    foreach ($youtubePatterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return [
                'platform' => 'youtube',
                'id' => $matches[1],
                'embed_url' => "https://www.youtube.com/embed/{$matches[1]}?rel=0&modestbranding=1",
                'thumbnail' => "https://img.youtube.com/vi/{$matches[1]}/maxresdefault.jpg"
            ];
        }
    }
    
    // Vimeo patterns
    $vimeoPatterns = [
        '/vimeo\.com\/([0-9]+)/',
        '/player\.vimeo\.com\/video\/([0-9]+)/',
    ];
    
    foreach ($vimeoPatterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return [
                'platform' => 'vimeo',
                'id' => $matches[1],
                'embed_url' => "https://player.vimeo.com/video/{$matches[1]}?title=0&byline=0&portrait=0",
                'thumbnail' => null // Vimeo thumbnail requires API call
            ];
        }
    }
    
    // Direct video file (mp4, webm, etc.)
    if (preg_match('/\.(mp4|webm|ogg|avi|mov)(\?.*)?$/i', $url)) {
        return [
            'platform' => 'direct',
            'id' => basename($url),
            'embed_url' => $url,
            'thumbnail' => null
        ];
    }
    
    return null;
}

/**
 * Generate video embed HTML for uploaded files
 * 
 * @param string $videoFile Path to uploaded video file
 * @param string $customThumbnail Optional custom thumbnail path
 * @param int $duration Optional video duration in seconds
 * @param array $options Additional options (width, height, autoplay, etc.)
 * @return string HTML for video embed
 */
function generateVideoEmbed($videoFile = null, $customThumbnail = null, $duration = null, $options = []) {
    if (empty($videoFile)) {
        return '<div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6 text-center">
                    <p class="text-yellow-800 font-semibold mb-2">⚠️ No video file available</p>
                    <p class="text-sm text-yellow-700">The video file may not have been uploaded successfully. Please re-upload the video in the admin panel.</p>
                </div>';
    }
    
    return generateUploadedVideoEmbed($videoFile, $customThumbnail, $duration, $options);
}

/**
 * Generate embed for uploaded video file
 * 
 * @param string $videoFile Path to uploaded video file
 * @param string $customThumbnail Optional custom thumbnail path
 * @param int $duration Optional video duration in seconds
 * @param array $options Additional options
 * @return string HTML for video embed
 */
function generateUploadedVideoEmbed($videoFile, $customThumbnail = null, $duration = null, $options = []) {
    if (empty($videoFile)) {
        return '<p class="text-red-500 text-sm">No video file provided</p>';
    }
    
    $width = $options['width'] ?? '100%';
    $height = $options['height'] ?? '315';
    $autoplay = $options['autoplay'] ?? false;
    $controls = $options['controls'] ?? true;
    $responsive = $options['responsive'] ?? true;
    $preload = $options['preload'] ?? 'metadata';
    $showDuration = $options['show_duration'] ?? true;
    
    $wrapperClass = $responsive ? 'relative w-full' : '';
    $videoClass = $responsive ? 'w-full h-auto' : '';
    
    $controlsAttr = $controls ? 'controls' : '';
    $autoplayAttr = $autoplay ? 'autoplay muted playsinline' : '';
    $preloadAttr = 'preload="' . $preload . '"';
    
    // Determine MIME type
    $ext = strtolower(pathinfo($videoFile, PATHINFO_EXTENSION));
    $mimeTypes = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo'
    ];
    $mimeType = $mimeTypes[$ext] ?? 'video/mp4';
    
    $videoUrl = basePath() . $videoFile;
    
    // Use custom poster if provided (captured cover frame), otherwise fall back to video file with time fragment
    if (!empty($customThumbnail)) {
        // Custom poster is already a full URL from getVideoPoster() or similar
        $posterUrl = $customThumbnail;
    } else {
        // Fallback: use video file with timestamp to show first frame
        $posterUrl = $videoUrl . '#t=0.5';
    }
    
    $html = '<div class="' . $wrapperClass . '">';
    $html .= '<div class="relative bg-black">';
    $html .= '<video class="' . $videoClass . '" width="' . $width . '" height="' . $height . '" ' . $controlsAttr . ' ' . $autoplayAttr . ' ' . $preloadAttr . ' poster="' . htmlspecialchars($posterUrl) . '">';
    $html .= '<source src="' . htmlspecialchars($videoUrl) . '" type="' . $mimeType . '">';
    $html .= 'Your browser does not support the video tag.';
    $html .= '</video>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Get video thumbnail URL
 * Returns custom poster if available, otherwise falls back to video file itself
 * 
 * @param string $customThumbnail Optional custom thumbnail path (video_poster column)
 * @param string $videoFile Optional uploaded video file path
 * @return string Thumbnail path or null
 */
function getVideoThumbnail($customThumbnail = null, $videoFile = null) {
    // Prefer custom poster if available
    if ($customThumbnail) {
        return basePath() . $customThumbnail;
    }
    
    // Fallback to video file path
    if ($videoFile) {
        return basePath() . $videoFile;
    }
    
    return null;
}

/**
 * Format duration in human readable format
 * 
 * @param int $seconds Duration in seconds
 * @return string Formatted duration (e.g., "2:34", "1:23:45")
 */
function formatVideoDuration($seconds) {
    if ($seconds < 60) {
        return "0:" . sprintf('%02d', $seconds);
    }
    
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    
    if ($minutes < 60) {
        return $minutes . ":" . sprintf('%02d', $remainingSeconds);
    }
    
    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;
    
    return $hours . ":" . sprintf('%02d', $remainingMinutes) . ":" . sprintf('%02d', $remainingSeconds);
}


/**
 * Get video poster/thumbnail for a post
 * Wrapper function that returns the best available poster image for a video post
 * Convenience wrapper that extracts video fields from post array and calls getVideoThumbnail()
 * 
 * @param array $post Post data array (should include video_poster, video_thumbnail, video_file fields)
 * @return string|null Full URL to poster image, or null if none available
 */
function getVideoPoster($post) {
    // Extract fields and delegate to getVideoThumbnail() which handles the preference logic
    return getVideoThumbnail(
        $post['video_poster'] ?? null,
        $post['video_file'] ?? null
    );
}

/**
 * Get Tailwind aspect ratio class for video card based on orientation
 * Returns the appropriate CSS class to maintain video aspect ratio in cards
 * 
 * @param array $post Post data array (should include video_orientation field)
 * @return string Tailwind aspect-ratio class ('aspect-[9/16]', 'aspect-square', or 'aspect-video')
 */
function getVideoCardAspectClass($post) {
    $orientation = $post['video_orientation'] ?? 'landscape';
    
    switch ($orientation) {
        case 'portrait':
            return 'aspect-[9/16]';
        case 'square':
            return 'aspect-square';
        case 'landscape':
        default:
            return 'aspect-video';
    }
}

/**
 * Format video view count in compact notation
 * Displays view counts in a human-readable format (e.g., "1.2K" for 1200+)
 * 
 * @param int $views Number of views
 * @return string Formatted view count (e.g., "1.2K views", "523 views", or empty if 0)
 */
function formatVideoViews($views) {
    $views = (int)$views;
    
    if ($views === 0) {
        return '';
    }
    
    if ($views < 1000) {
        return $views . ' views';
    }
    
    if ($views < 1000000) {
        return round($views / 1000, 1) . 'K views';
    }
    
    return round($views / 1000000, 1) . 'M views';
}

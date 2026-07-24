<?php
/**
 * Verse Card Component
 * Reusable card for displaying Bible verses with audience tags
 * 
 * @var string $reference Bible reference (e.g., "Psalm 92:12")
 * @var string $text The verse text
 * @var array $audienceTags Array of audience categories (e.g., ['Children', 'Adults'])
 */

// Ensure required variables are set
if (!isset($reference) || !isset($text)) {
    return;
}

$audienceTags = $audienceTags ?? [];

// Map audience names to CSS classes
$audienceClasses = [
    'Children' => 'children',
    'Teens' => 'teens',
    'Adults' => 'adults',
    'Aged' => 'aged',
    'All' => 'all'
];
?>
<div class="verse-card fade-in-up hover-lift" style="background-color: #FDEAEA;">
    <!-- Verse Reference -->
    <p class="verse-reference" style="font-family: 'Inter', sans-serif;">
        <?php echo e($reference); ?>
    </p>
    
    <!-- Verse Text -->
    <p class="verse-text mt-3">
        <?php echo e($text); ?>
    </p>
    
    <!-- Audience Tags -->
    <?php if (!empty($audienceTags)): ?>
        <div class="flex flex-wrap gap-2 mt-4">
            <?php foreach ($audienceTags as $tag): ?>
                <?php 
                $tagLower = strtolower($tag);
                $tagClass = $audienceClasses[$tag] ?? 'all';
                ?>
                <span class="category-pill <?php echo e($tagClass); ?>">
                    <?php echo e($tag); ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

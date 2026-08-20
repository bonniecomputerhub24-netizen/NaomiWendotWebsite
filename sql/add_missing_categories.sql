-- ═══════════════════════════════════════════════════════════════════════════════
-- Migration: Add Missing Categories for Frontend Pages
-- Purpose: Ensure all category pages (Stories, Testimonies, Videos) have 
--          corresponding database entries with correct slugs
-- ═══════════════════════════════════════════════════════════════════════════════

-- Add Stories category
INSERT IGNORE INTO `categories` (`slug`, `name`, `description`, `icon`, `display_order`, `created_at`) 
VALUES ('stories', 'Stories', 'Narratives of faith, courage, and transformation', '📜', 4, NOW());

-- Add Testimonies category
INSERT IGNORE INTO `categories` (`slug`, `name`, `description`, `icon`, `display_order`, `created_at`) 
VALUES ('testimonies', 'Testimonies', 'Real stories of God\'s grace, protection, and answered prayer', '✨', 5, NOW());

-- Add Videos category if not exists
INSERT IGNORE INTO `categories` (`slug`, `name`, `description`, `icon`, `display_order`, `created_at`) 
VALUES ('videos', 'Videos', 'Short inspirational videos by Naomi', '🎥', 6, NOW());

-- Update display orders for existing categories to match navigation order
UPDATE `categories` SET `display_order` = 1 WHERE `slug` = 'poems';
UPDATE `categories` SET `display_order` = 2 WHERE `slug` = 'articles';
UPDATE `categories` SET `display_order` = 3 WHERE `slug` = 'daily-inspirations';

-- Verify categories
SELECT id, slug, name, display_order FROM categories ORDER BY display_order ASC;

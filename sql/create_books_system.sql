-- Books and Shop System for Naomi Wendot Website
-- Created: August 14, 2026

-- Table: books
CREATE TABLE IF NOT EXISTS `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text,
  `cover_image` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `status` enum('draft','writing','coming_soon','published','archived') DEFAULT 'draft',
  `category` varchar(100) DEFAULT NULL COMMENT 'Fiction, Non-Fiction, Poetry, Devotional, etc.',
  `isbn` varchar(50) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `published_date` date DEFAULT NULL,
  `pre_order` tinyint(1) DEFAULT 0,
  `preview_available` tinyint(1) DEFAULT 0 COMMENT 'Allow chapter previews',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: book_chapters
CREATE TABLE IF NOT EXISTS `book_chapters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `chapter_number` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext,
  `word_count` int(11) DEFAULT 0,
  `status` enum('draft','in_progress','review','published') DEFAULT 'draft',
  `is_preview` tinyint(1) DEFAULT 0 COMMENT 'Available as free preview',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_book_chapter` (`book_id`, `chapter_number`),
  KEY `idx_book_id` (`book_id`),
  KEY `idx_slug` (`slug`),
  CONSTRAINT `fk_chapters_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: shop_products (for future expansion - other items)
CREATE TABLE IF NOT EXISTS `shop_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `description` text,
  `product_type` enum('book','merchandise','digital','other') DEFAULT 'other',
  `product_id` int(11) DEFAULT NULL COMMENT 'Reference ID for books table or other',
  `price` decimal(10,2) NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `stock_status` enum('in_stock','out_of_stock','coming_soon','pre_order') DEFAULT 'in_stock',
  `featured` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_type` (`product_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert dummy book data
INSERT INTO `books` (`title`, `slug`, `subtitle`, `description`, `cover_image`, `price`, `status`, `category`, `pages`, `published_date`, `pre_order`, `preview_available`) VALUES
('Words That Heal', 'words-that-heal', 'A Collection of Faith-Inspired Poetry', 'A beautiful collection of poems exploring themes of hope, faith, and everyday grace. Written over two decades of walking with God, these verses carry the weight of lived experience and the lightness of divine joy.', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&q=80', 12.99, 'coming_soon', 'Poetry', 180, NULL, 1, 1),
('Anchored in Grace', 'anchored-in-grace', 'Daily Devotional for the Modern Believer', 'A 365-day devotional journey through Scripture, personal testimonies, and reflections. Each day offers a moment to pause, reflect, and anchor your soul in God''s unchanging grace.', 'https://images.unsplash.com/photo-1506869640319-fe1a24fd76dc?w=600&q=80', 15.99, 'writing', 'Devotional', 400, NULL, 0, 0),
('Run Toward the Light', 'run-toward-the-light', 'Stories of Faith, Courage, and Transformation', 'True stories from Naomi''s life and ministry, including her work with EDUCARE, Run for the Bibleless, and encounters with God''s faithfulness in unexpected places.', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&q=80', 14.99, 'coming_soon', 'Biography', 250, NULL, 1, 0);

-- Insert sample chapters for "Anchored in Grace" (the book being written)
INSERT INTO `book_chapters` (`book_id`, `chapter_number`, `title`, `slug`, `content`, `word_count`, `status`, `is_preview`, `display_order`) VALUES
(2, 1, 'Introduction: Why We Need Anchors', 'introduction-why-we-need-anchors', '# Introduction: Why We Need Anchors\n\nIn the turbulent waters of modern life, we all need anchors. Not the kind that weigh us down, but the kind that keep us steady when storms rage around us.\n\nThis book is born from two decades of journal entries, prayers, and quiet mornings with God. It is not a book of answers, but a book of companionship—a fellow traveler sharing what she has learned along the way.\n\n**What You Will Find Here:**\n- Daily reflections rooted in Scripture\n- Personal testimonies of God''s faithfulness\n- Prayers to guide your own conversations with God\n- Space to write your own journey\n\nMay these pages become a sacred space where you meet with the One who anchors your soul.\n\n*"We have this hope as an anchor for the soul, firm and secure." — Hebrews 6:19*', 180, 'published', 1, 1),
(2, 2, 'Day 1: The Morning Light', 'day-1-the-morning-light', '# Day 1: The Morning Light\n\n**Scripture:** *"The steadfast love of the LORD never ceases; his mercies never come to an end; they are new every morning; great is your faithfulness." — Lamentations 3:22-23*\n\n## Reflection\n\nEvery sunrise is a gift we did not earn. It arrives without our permission, painting the sky with colors we could never replicate.\n\nThis morning, before the demands of the day crowd in, pause. Look out the window. Notice the light.\n\nGod''s mercies are like that light—fresh, unstoppable, free.\n\n## Personal Story\n\nI remember a morning in 2015 when I woke up burdened by worry. Bills unpaid, relationships strained, dreams deferred. I stood at my window and watched the sun rise anyway. It didn''t ask if I deserved it. It just came.\n\nThat''s grace.\n\n## Prayer\n\n*Lord, thank You for this new day. Help me receive Your mercies like a child receives the morning—with wonder, not entitlement. Amen.*\n\n## Journal Prompt\n\nWhat mercy did you wake up to today that you almost missed?', 210, 'published', 1, 2),
(2, 3, 'Day 2: When Prayer Feels Heavy', 'day-2-when-prayer-feels-heavy', '[Content being written...]', 0, 'draft', 0, 3);

-- Insert shop products linking to books
INSERT INTO `shop_products` (`name`, `slug`, `description`, `product_type`, `product_id`, `price`, `image`, `stock_status`, `featured`, `display_order`) VALUES
('Words That Heal - Poetry Collection', 'words-that-heal-book', 'A beautiful collection of faith-inspired poetry spanning two decades of walking with God.', 'book', 1, 12.99, 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&q=80', 'coming_soon', 1, 1),
('Anchored in Grace - 365 Day Devotional', 'anchored-in-grace-book', 'Daily devotional journey through Scripture, testimonies, and reflections. Currently being written!', 'book', 2, 15.99, 'https://images.unsplash.com/photo-1506869640319-fe1a24fd76dc?w=600&q=80', 'coming_soon', 1, 2),
('Run Toward the Light - Biography', 'run-toward-the-light-book', 'Stories of faith, courage, and transformation from Naomi''s life and ministry.', 'book', 3, 14.99, 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&q=80', 'coming_soon', 0, 3);

-- Success message
SELECT 'Books system created successfully!' AS message;

-- Add Dual Currency Support (USD and KES) to Books System
-- Migration Date: August 14, 2026

-- Add currency fields to books table
ALTER TABLE `books`
ADD COLUMN `price_usd` decimal(10,2) DEFAULT 0.00 AFTER `price`,
ADD COLUMN `price_kes` decimal(10,2) DEFAULT 0.00 AFTER `price_usd`;

-- Migrate existing price data to price_usd
UPDATE `books` SET `price_usd` = `price` WHERE `price_usd` = 0;

-- Convert USD to KES (approximate rate: 1 USD = 130 KES)
UPDATE `books` SET `price_kes` = `price_usd` * 130 WHERE `price_kes` = 0;

-- Note: The old 'price' column is kept for backward compatibility
-- New applications should use price_usd and price_kes

-- Add currency fields to shop_products table
ALTER TABLE `shop_products`
ADD COLUMN `price_usd` decimal(10,2) DEFAULT 0.00 AFTER `price`,
ADD COLUMN `price_kes` decimal(10,2) DEFAULT 0.00 AFTER `price_usd`;

-- Migrate existing price data
UPDATE `shop_products` SET `price_usd` = `price` WHERE `price_usd` = 0;
UPDATE `shop_products` SET `price_kes` = `price_usd` * 130 WHERE `price_kes` = 0;

SELECT 'Dual currency support added successfully!' AS message;

-- Add shipping_amount column to orders table if it doesn't exist
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`;

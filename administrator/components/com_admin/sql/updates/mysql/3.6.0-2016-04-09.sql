--
-- Add ACL check for to #__menu_types
--

ALTER TABLE `#__menu_types` ADD COLUMN `asset_id` INT NOT NULL AFTER `id`;
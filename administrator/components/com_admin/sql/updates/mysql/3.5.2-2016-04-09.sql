--
-- Add ACL check for to #__menu_types
--

ALTER TABLE `#__menu_types` ADD COLUMN `asset_id` INT(11) NOT NULL AFTER `id`;
--
-- Add ACL check for to #__languages
--

ALTER TABLE `#__languages` ADD COLUMN `asset_id` INT NOT NULL AFTER `lang_id`;
--
-- Add ACL check for to #__languages
--

ALTER TABLE `#__languages` ADD COLUMN `asset_id` INT(11) NOT NULL AFTER `lang_id`;
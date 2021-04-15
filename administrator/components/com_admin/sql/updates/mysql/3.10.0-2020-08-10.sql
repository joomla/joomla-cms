--
-- These database columns are not used in Joomla 3.10 but will be used in Joomla 4.
-- They are added to 3.10 because otherwise the update to 4 will fail.
--
ALTER TABLE `#__template_styles` ADD COLUMN `inheritable` tinyint NOT NULL DEFAULT 0;
ALTER TABLE `#__template_styles` ADD COLUMN `parent` varchar(50) DEFAULT '';

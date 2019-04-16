ALTER TABLE `#__template_styles` DROP INDEX `idx_home`;
ALTER TABLE `#__template_styles` MODIFY `home` tinyint(1) unsigned DEFAULT 0;
ALTER TABLE `#__template_styles` ADD INDEX `idx_home` (`home`);

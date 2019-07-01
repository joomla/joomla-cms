ALTER TABLE `#__template_styles` DROP INDEX `idx_home`;
ALTER TABLE `#__template_styles` MODIFY `home` tinyint(1) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__template_styles` ADD INDEX `idx_client_id` (`client_id`);
ALTER TABLE `#__template_styles` ADD INDEX `idx_client_id_home` (`client_id`, `home`);

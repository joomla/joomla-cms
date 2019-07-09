ALTER TABLE `#__template_styles` DROP INDEX `idx_client_id_home`;
ALTER TABLE `#__template_styles` MODIFY `home` char(7) NOT NULL DEFAULT '0';
ALTER TABLE `#__template_styles` ADD INDEX `idx_client_id_home_2` (`client_id`, `home`);

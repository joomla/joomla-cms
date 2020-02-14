ALTER TABLE `#__template_styles` DROP INDEX `idx_home`;
# Query removed, see https://github.com/joomla/joomla-cms/pull/25484
ALTER TABLE `#__template_styles` ADD INDEX `idx_client_id` (`client_id`);
ALTER TABLE `#__template_styles` ADD INDEX `idx_client_id_home` (`client_id`, `home`);

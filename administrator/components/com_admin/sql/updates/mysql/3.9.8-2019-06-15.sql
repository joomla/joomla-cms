ALTER TABLE `#__template_styles` DROP INDEX `idx_home`;
ALTER TABLE `#__template_styles` ADD INDEX `idx_client_id` (`client_id`);
# Queries removed, see https://github.com/joomla/joomla-cms/pull/XXXXXX

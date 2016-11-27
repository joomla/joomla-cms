-- Add core field to extensions table.
ALTER TABLE `#__extensions` MODIFY `protected` tinyint(3) NOT NULL DEFAULT 0 COMMENT 'Flag to indicate if the extension can be enabled/disabled.',
ALTER TABLE `#__extensions` ADD COLUMN `core` tinyint(3) NOT NULL DEFAULT 0 COMMENT 'Flag to indicate if is a Joomla core extension. Core extensions can not be uninstalled.',

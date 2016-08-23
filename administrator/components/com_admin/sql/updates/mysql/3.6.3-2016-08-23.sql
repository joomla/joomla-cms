-- Add default value for custom_data and system_data.
ALTER TABLE `#__extensions` MODIFY `custom_data` text NOT NULL DEFAULT '';
ALTER TABLE `#__extensions` MODIFY `system_data` text NOT NULL DEFAULT '';

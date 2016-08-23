-- Add default value for custom data and 
ALTER TABLE `#__extensions` MODIFY `custom_data` text NOT NULL DEFAULT '';
ALTER TABLE `#__extensions` MODIFY `system_data` text NOT NULL DEFAULT '';

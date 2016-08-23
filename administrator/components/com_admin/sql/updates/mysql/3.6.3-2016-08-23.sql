-- Add default value for custom_data and system_data fields in the #__extensions table.
ALTER TABLE `#__extensions` MODIFY `custom_data` text NOT NULL DEFAULT '';
ALTER TABLE `#__extensions` MODIFY `system_data` text NOT NULL DEFAULT '';
-- Add default value for data field in the #__updates table.
ALTER TABLE `#__updates` MODIFY `data` text NOT NULL DEFAULT '';

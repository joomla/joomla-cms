-- Add default value for custom_data and system_data fields in the #__extensions table.
ALTER TABLE `#__extensions` MODIFY `custom_data` text NOT NULL DEFAULT '';
ALTER TABLE `#__extensions` MODIFY `system_data` text NOT NULL DEFAULT '';
-- Add default value for data field in the #__updates table.
ALTER TABLE `#__updates` MODIFY `data` text NOT NULL DEFAULT '';
-- Add default value for asset_id field in the #__languages table.
ALTER TABLE `#__languages` MODIFY `asset_id` int(11) NOT NULL DEFAULT 0;
-- Add default value for asset_id field in the #__menu_types table.
ALTER TABLE `#__menu_types` MODIFY `asset_id` int(11) NOT NULL DEFAULT 0;

-- Add the Inheritable property to the #__menu table.
ALTER TABLE `#__menu` ADD `inheritable` TINYINT( 3 ) NOT NULL DEFAULT '1' COMMENT 'Determines if menu item is inheritable by higher group access levels.' AFTER `access`;
-- Normalize contact_details table default values.
ALTER TABLE `#__contact_details` MODIFY `name` varchar(255) NOT NULL;
ALTER TABLE `#__contact_details` MODIFY `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;
ALTER TABLE `#__contact_details` MODIFY `sortname1` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__contact_details` MODIFY `sortname2` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__contact_details` MODIFY `sortname3` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__contact_details` MODIFY `language` varchar(7) NOT NULL;
ALTER TABLE `#__contact_details` MODIFY `xreference` varchar(50) NOT NULL DEFAULT '' COMMENT 'A reference to enable linkages to external data sets.';

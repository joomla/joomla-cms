-- From 4.0.0-2020-02-08.sql
ALTER TABLE `#__banners` MODIFY `metakey` text;
ALTER TABLE `#__banner_clients` MODIFY `metakey` text;
ALTER TABLE `#__contact_details` MODIFY `metakey` text;
ALTER TABLE `#__content` MODIFY `metakey` text;
ALTER TABLE `#__languages` MODIFY `metakey` text;
ALTER TABLE `#__newsfeeds` MODIFY `metakey` text;
ALTER TABLE `#__tags` MODIFY `metakey` varchar(1024) NOT NULL DEFAULT '';

-- From 4.0.0-2020-02-20.sql
ALTER TABLE `#__content_types` MODIFY `table` varchar(2048) NOT NULL DEFAULT '';

-- From 4.0.0-2020-02-22.sql
DELETE FROM `#__extensions` WHERE `name` = 'com_mailto';

-- From 4.0.0-2020-02-29.sql
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(0, 'plg_system_accessibility', 'plugin', 'accessibility', 'system', 0, 0, 1, 0, '', '{}', '', 0, NULL, 0, 0);

-- From 4.0.0-2020-03-10.sql
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `checked_out`, `checked_out_time`, `ordering`, `state`)
SELECT `extension_id`, 'English (en-GB)', 'language', 'en-GB', '', 3, 1, 1, 1, '', '', '', 0, NULL, 0, 0 FROM `#__extensions` WHERE `name` = 'English (en-GB) Language Pack';

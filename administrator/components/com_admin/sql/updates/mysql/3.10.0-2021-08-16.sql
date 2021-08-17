INSERT INTO `#__extensions` (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
('search', 'package', 'pkg_search', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'com_search' AND `type` = 'component';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'mod_search' AND `type` = 'module' AND `client_id` = 0;
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'categories' AND `type` = 'plugin' AND `folder` = 'search';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'contacts' AND `type` = 'plugin' AND `folder` = 'search';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'content' AND `type` = 'plugin' AND `folder` = 'search';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'newsfeeds' AND `type` = 'plugin' AND `folder` = 'search';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'tags' AND `type` = 'plugin' AND `folder` = 'search';

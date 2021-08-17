INSERT INTO `#__extensions` (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
('search', 'package', 'pkg_search', '', 0, 1, 1, 0, '', '', '', 0, NULL, 0, 0);

UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'com_search' AND `type` = 'component';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'mod_search' AND `type` = 'module' AND `client_id` = 0;
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'categories' AND `type` = 'plugin' AND `folder` = 'search';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'contacts' AND `type` = 'plugin' AND `folder` = 'search';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'content' AND `type` = 'plugin' AND `folder` = 'search';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'newsfeeds' AND `type` = 'plugin' AND `folder` = 'search';
UPDATE `#__extensions` SET `package_id` = (SELECT a.`extension_id` FROM `#__extensions` a WHERE a.`type`='package' AND a.`element`='pkg_search') WHERE `element` = 'tags' AND `type` = 'plugin' AND `folder` = 'search';

INSERT INTO `#__update_sites` (`name`, `type`, `location`, `enabled`) VALUES
('Search Update Site', 'extension', 'https://raw.githubusercontent.com/joomla-extensions/search/main/manifest.xml', 1);

INSERT INTO `#__update_sites_extensions` (`update_site_id`, `extension_id`) VALUES
((SELECT `update_site_id` FROM `#__update_sites` WHERE `name` = 'Search Update Site'), (SELECT `extension_id` FROM `#__extensions` WHERE `element` = 'pkg_search' AND `type` = 'package'));

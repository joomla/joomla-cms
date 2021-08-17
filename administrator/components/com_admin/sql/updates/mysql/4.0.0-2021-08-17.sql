INSERT INTO `#__extensions` (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
('search', 'package', 'pkg_search', '', 0, 1, 1, 0, '', '', '', 0, NULL, 0, 0);

UPDATE `#__extensions` a
 CROSS JOIN (SELECT `extension_id` FROM `#__extensions` WHERE `type`='package' AND `element`='pkg_search') AS b
   SET a.`package_id` = b.`extension_id`
 WHERE (`type` = 'component' AND `element` = 'com_search')
    OR (`type` = 'module' AND `element` = 'mod_search' AND `client_id` = 0)
    OR (`type` = 'plugin' AND `element` IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags') AND `folder` = 'search');

INSERT INTO `#__update_sites` (`name`, `type`, `location`, `enabled`) VALUES
('Search Update Site', 'extension', 'https://raw.githubusercontent.com/joomla-extensions/search/main/manifest.xml', 1);

INSERT INTO `#__update_sites_extensions` (`update_site_id`, `extension_id`) VALUES
((SELECT `update_site_id` FROM `#__update_sites` WHERE `name` = 'Search Update Site'), (SELECT `extension_id` FROM `#__extensions` WHERE `element` = 'pkg_search' AND `type` = 'package'));

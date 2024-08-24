-- Add new `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`) VALUES
(0, 'mod_community_info', 'module', 'mod_community_info', '', 1, 1, 1, 0, 1, '', '{}', '');

INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
('Joomla! Community and News', '', '', 1, 'cpanel', NULL, NULL, 1, 'mod_community_info', 1, 1, '{"endpoint":"https://test.joomla.spuur.ch/joomla-community-api/links.php","fallback-location":"en-GB","location":"","location_name":"","auto_location":"1","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*');

UPDATE `#__modules` SET `ordering` = 7 WHERE id = 3;  -- Popular Articles module
UPDATE `#__modules` SET `ordering` = 5 WHERE id = 4;  -- Recently Added Articles module
UPDATE `#__modules` SET `ordering` = 3 WHERE id = 10; -- Logged-in Users module
UPDATE `#__modules` SET `ordering` = 2 WHERE id = 87; -- Sample Data module
UPDATE `#__modules` SET `ordering` = 4 WHERE id = 88; -- Latest Actions module
UPDATE `#__modules` SET `ordering` = 6 WHERE id = 89; -- Privacy Dashboard module

INSERT INTO `#__modules_menu` (`moduleid`, `menuid`) VALUES (LAST_INSERT_ID(), 0);

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(437, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 1, '', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(438, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 1, '', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

ALTER TABLE  `#__update_sites` ADD COLUMN `last_check_timestamp` bigint DEFAULT '0' AFTER `enabled`;

REPLACE INTO `#__update_sites` VALUES
(1, 'Joomla Core', 'collection', 'https://update.joomla.org/core/list.xml', 1, 0),
(2, 'Joomla Extension Directory', 'collection', 'https://update.joomla.org/jed/list.xml', 1, 0);

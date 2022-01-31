-- From 4.0.0-2019-07-14.sql
ALTER TABLE `#__contact_details` DROP COLUMN `xreference`;
ALTER TABLE `#__content` DROP COLUMN `xreference`;
ALTER TABLE `#__newsfeeds` DROP COLUMN `xreference`;

-- From 4.0.0-2019-07-16.sql
-- This has been removed as com_csp has been removed from the final build

-- From 4.0.0-2019-08-03.sql
ALTER TABLE `#__update_sites` ADD COLUMN `checked_out` int unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__update_sites` ADD COLUMN `checked_out_time` datetime NULL DEFAULT NULL;

-- From 4.0.0-2019-08-20.sql
ALTER TABLE `#__content_frontpage` ADD COLUMN `featured_up` datetime;
ALTER TABLE `#__content_frontpage` ADD COLUMN `featured_down` datetime;

-- From 4.0.0-2019-08-21.sql
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(0, 'plg_webservices_banners', 'plugin', 'banners', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_config', 'plugin', 'config', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_contact', 'plugin', 'contact', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_languages', 'plugin', 'languages', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_menus', 'plugin', 'menus', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_messages', 'plugin', 'messages', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_modules', 'plugin', 'modules', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_newsfeeds', 'plugin', 'newsfeeds', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_plugins', 'plugin', 'plugins', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_privacy', 'plugin', 'privacy', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_redirect', 'plugin', 'redirect', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_tags', 'plugin', 'tags', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_templates', 'plugin', 'templates', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_webservices_users', 'plugin', 'users', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0);

-- From 4.0.0-2019-09-13.sql
INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`, `publish_up`, `publish_down`)
SELECT 'main', 'com_messages_manager', 'Private Messages', '', 'Messaging/Private Messages', 'index.php?option=com_messages&view=messages', 'component', 1, 10, 2, `extension_id`, 0, NULL, 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1, NULL, NULL FROM `#__extensions` WHERE `name` = 'com_messages';

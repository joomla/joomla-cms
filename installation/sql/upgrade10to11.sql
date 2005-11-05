# $Id$

# Joomla 1.0 to Joomla 1.1

# Mambot Additions
INSERT INTO `#__mambots` VALUES (0, 'Joomla Userbot', 'joomla.userbot', 'user', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'LDAP Userbot', 'ldap.userbot', 'user', 0, 1, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'Joomla SiteURLBot', 'joomla.siteurlbot', 'system', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'Joomla SEFURLBot', 'joomla.sefurlbot', 'system', 0, 2, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'Search XML-RPC Bot', 'search.xmlrpcbot', 'xmlrpc', 0, 2, 1, 1, 0, 0, '0000-00-00 00:00:00', '');

# Module Additions
INSERT INTO `#__modules` VALUES (0, 'Footer', '', 1, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 0);
INSERT INTO `#__modules` VALUES (0, 'Footer', '', 0, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 1);

# Expand content title lengths
ALTER TABLE `#__content` CHANGE `title` `title` varchar(255) NOT NULL default '';
ALTER TABLE `#__content` CHANGE `title_alias` `title_alias` varchar(255) NOT NULL default '';

UPDATE `#__components` SET `admin_menu_link` = 'option=com_categories&section=com_contact_details' WHERE `id` = '9'  LIMIT 1;
UPDATE `#__components` SET `admin_menu_link` = 'option=com_categories&section=com_weblinks' WHERE `id` = '6'  LIMIT 1;



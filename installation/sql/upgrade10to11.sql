# $Id$

# Joomla 1.0 to Joomla 1.1

# Mambot Additions
INSERT INTO `jos_mambots` VALUES (0, 'Joomla Userbot', 'joomla.userbot', 'user', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `jos_mambots` VALUES (0, 'LDAP Userbot', 'ldap.userbot', 'user', 0, 1, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'Joomla SSL URLs', 'joomla.siteurlbot', 'system', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'Joomla SEF URLs', 'joomla.sefurlbot', 'system', 0, 2, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'Search XML-RPC', 'search.xmlrpcbot', 'xmlrpc', 0, 7, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'Visitor Statistics', 'joomla.visitorbot', 'system', 0, 3, 1, 1, 0, 0, '0000-00-00 00:00:00', '');

# Module Additions
INSERT INTO `jos_modules` VALUES (1000, 'Footer', '', 1, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 0);
INSERT INTO `jos_modules` VALUES (1001, 'Footer', '', 0, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 1);
INSERT INTO `jos_modules` VALUES (0, 'Newsfeed', '', 11, 'left', 0, '0000-00-00 00:00:00', 0, 'mod_rss', 0, 0, 1, '', 1, 0);
INSERT INTO `jos_modules` VALUES (1002, 'Pathway', '', 1, 'pathway', 0, '0000-00-00 00:00:00', 1, 'mod_pathway', 0, 0, 1, '', 1, 0);
INSERT INTO `jos_modules_menu` VALUES (1000,0);
INSERT INTO `jos_modules_menu` VALUES (1001,0);
INSERT INTO `jos_modules_menu` VALUES (1002,0);

# Expand content title lengths
ALTER TABLE `jos_content` CHANGE `title` `title` varchar(255) NOT NULL default '';
ALTER TABLE `jos_content` CHANGE `title_alias` `title_alias` varchar(255) NOT NULL default '';

UPDATE `jos_components` SET `admin_menu_link` = 'option=com_categories&section=com_contact_details' WHERE `id` = '9'  LIMIT 1;
UPDATE `jos_components` SET `admin_menu_link` = 'option=com_categories&section=com_weblinks' WHERE `id` = '6'  LIMIT 1;



# $Id:  Exp $

# Joomla 1.0 to Joomla 1.1

# Mambot Additions
INSERT INTO `#__mambots` VALUES (0, 'Joomla Userbot', 'joomla.userbot', 'user', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'LDAP Userbot', 'ldap.userbot', 'user', 0, 1, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__mambots` VALUES (0, 'Joomla SiteURLBot', 'joomla.siteurlbot', 'system', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');

# Expand content title lengths
ALTER TABLE `#__content` CHANGE `title` `title` varchar(255) NOT NULL default '';
ALTER TABLE `#__content` CHANGE `title_alias` `title_alias` varchar(255) NOT NULL default '';

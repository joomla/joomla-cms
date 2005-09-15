# $Id: upgrade452to453.sql 4 2005-09-06 19:22:37Z akede $

# Mambo 4.5.2 to Mambo 4.5.3

# Component Additions
INSERT INTO `mos_components` VALUES (0, 'Mass Mail', '', 0, 0, 'option=com_massmail', 'Send Mass Mail', 'com_massmail', 7, 'js/ThemeOffice/mass_email.png', 0, '');

# Component Modifications
UPDATE `mos_components` SET `admin_menu_img` = 'js/ThemeOffice/globe2.png' WHERE `name` = 'Web Links';
UPDATE `mos_components` SET `admin_menu_img` = 'js/ThemeOffice/user.png' WHERE `name` = 'Contacts';
UPDATE `mos_components` SET `admin_menu_img` = 'js/ThemeOffice/edit.png' WHERE `name` = 'Manage Contacts';

# Mambot Additions
INSERT INTO `mos_mambots` VALUES (0, 'Mambo Userbot', 'mambo.userbot', 'user', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `mos_mambots` VALUES (0, 'LDAP Userbot', 'ldap.userbot', 'user', 0, 1, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `mos_mambots` VALUES (0, 'Visitor Statistics', 'visitors', 'system', 0, 0, 0, 1, 0, 0, '0000-00-00 00:00:00', '');

# Module Additions
INSERT INTO `mos_modules` VALUES (0, 'RSS', '', 4, 'right', 0, '0000-00-00 00:00:00', 0, 'mod_rss', 0, 0, 1, 'moduleclass_sfx=\nrssurl=http://news.mamboserver.com/index2.php?option=com_rss&feed=RSS1.0&no_html=1\nrssdesc=1\nrssimage=1\nrssitems=3\nrssitemdesc=1\nword_count=10\ncache=0', 0, 0);
INSERT INTO `mos_modules` VALUES (0, 'Linkbar', '', 0, 'user1', 0, '0000-00-00 00:00:00', 1, 'mod_linkbar', 0, 0, 0, '', 0, 1);        
INSERT INTO `mos_modules` VALUES (500, 'Footer', '', 1, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 0);
INSERT INTO `mos_modules` VALUES (0, 'Footer', '', 0, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 1);
INSERT INTO `mos_modules` VALUES (0, 'Logout Button', '', 3, 'header', 0, '0000-00-00 00:00:00', 1, 'mod_logoutbutton', 0, 0, 1, '', 1, 1);
INSERT INTO `mos_modules_menu` VALUES (500, 0);

# Fix Menu 
UPDATE `mos_menu` SET `link` = 'index.php?option=com_login' WHERE `menutype` = 'usermenu' AND `name` = 'Logout' AND `type` = 'components' LIMIT 1 ;

# Fix column names in phpgacl tables
ALTER TABLE `mos_core_acl_aro` CHANGE COLUMN `aro_id` `id` INTEGER NOT NULL AUTO_INCREMENT;
ALTER TABLE `mos_core_acl_aro_groups` CHANGE COLUMN `group_id` `id` INTEGER NOT NULL AUTO_INCREMENT;
ALTER TABLE `mos_core_acl_aro_sections` CHANGE COLUMN `section_id` `id` INTEGER NOT NULL AUTO_INCREMENT;

ALTER TABLE `mos_core_acl_aro_groups` ADD COLUMN `value` varchar(255) NOT NULL default '';
UPDATE `mos_core_acl_aro_groups` SET value=name;
ALTER TABLE `mos_core_acl_aro_groups` ADD UNIQUE `value_aro_groups`(`value`);
ALTER TABLE `mos_core_acl_aro_groups` DROP PRIMARY KEY, ADD PRIMARY KEY(`id`, `value`);

# Change column data length 
ALTER TABLE `mos_content` MODIFY COLUMN `title` varchar(255) NOT NULL default '';
ALTER TABLE `mos_content` MODIFY COLUMN `title_alias` varchar(255) NOT NULL default '';
ALTER TABLE `mos_categories` MODIFY COLUMN `title` varchar(255) NOT NULL default '';
ALTER TABLE `mos_sections` MODIFY COLUMN `title` varchar(255) NOT NULL default '';

# Fix Menu Stats 
UPDATE `mos_modules` SET `module` = 'mod_menustats' WHERE `module` = 'mod_stats' AND `client_id` = '1'

# $Id: upgrade451to452.sql 4 2005-09-06 19:22:37Z akede $

# Mambo 4.5.1 to Mambo 4.5.2

DROP TABLE IF EXISTS mos_help;

# New Mambots in 4.5.2
INSERT INTO `mos_mambots` VALUES (0, 'Search Categories', 'categories.searchbot', 'search', 0, 4, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `mos_mambots` VALUES (0, 'Search Sections', 'sections.searchbot', 'search', 0, 5, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `mos_mambots` VALUES (0, 'Search Newsfeeds', 'newsfeeds.searchbot', 'search', 0, 6, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `mos_mambots` VALUES (0, 'Email Cloaking', 'mosemailcloak', 'content', 0, 5, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `mos_mambots` VALUES (0, 'GeSHi Syntax Highlighter', 'geshi', 'content', 0, 5, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `mos_mambots` VALUES (0, 'Load Module Positions', 'mosloadposition', 'content', 0, 6, 1, 0, 0, 0, '0000-00-00 00:00:00', '');


# New Modules in 4.5.2
INSERT INTO `mos_modules` VALUES (0,'Wrapper','',10,'left',0,'0000-00-00 00:00:00',1,'mod_wrapper',0,0,1,'',0, 0);
INSERT INTO `mos_modules` VALUES (0,'Logged','',0,'cpanel',0,'0000-00-00 00:00:00',1,'mod_logged',0,99,1,'',0,1);

# Component Modifications
UPDATE `mos_components` SET `admin_menu_link` = 'option=com_syndicate' WHERE `admin_menu_link` = 'option=com_syndicate';
UPDATE `mos_components` SET `admin_menu_link` = 'option=com_massmail' WHERE `admin_menu_link` = 'option=com_massmail';

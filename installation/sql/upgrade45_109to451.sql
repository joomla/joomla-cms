# $Id: upgrade45_109to451.sql 4 2005-09-06 19:22:37Z akede $

# Mambo 4.5 (1.0.9) to Mambo 4.5.1

ALTER TABLE `mos_banner` CHANGE `checked_out_time` `checked_out_time` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL;

ALTER TABLE `mos_categories` ADD `parent_id` int(11) NOT NULL default 0 AFTER `id`;
ALTER TABLE `mos_categories` CHANGE `section` `section` varchar(50) NOT NULL default '';
ALTER TABLE `mos_categories` ADD `params` TEXT NOT NULL;

ALTER TABLE `mos_components` CHANGE `ordering` `ordering` int(11) NOT NULL default '0';
ALTER TABLE `mos_components` ADD `params` TEXT NOT NULL;

ALTER TABLE `mos_contact_details` ADD `params` TEXT NOT NULL;
ALTER TABLE `mos_contact_details` ADD `user_id` int(11) NOT NULL default '0';
ALTER TABLE `mos_contact_details` ADD `catid` int(11) NOT NULL default '0';
ALTER TABLE `mos_contact_details` ADD `access` tinyint(3) unsigned NOT NULL default '0';

ALTER TABLE `mos_content` CHANGE `ordering` `ordering` int(11) NOT NULL default '0';

ALTER TABLE `mos_content_frontpage` CHANGE `ordering` `ordering` int(11) NOT NULL default '0';

DROP TABLE IF EXISTS mos_help;

CREATE TABLE `mos_mambots` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `element` varchar(100) NOT NULL default '',
  `folder` varchar(100) NOT NULL default '',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  `published` tinyint(3) NOT NULL default '0',
  `iscore` tinyint(3) NOT NULL default '0',
  `client_id` tinyint(3) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_folder` (`published`,`client_id`,`access`,`folder`)
) TYPE=MyISAM;

ALTER TABLE `mos_modules` CHANGE `ordering` `ordering` int(11) NOT NULL default '0';
ALTER TABLE `mos_modules` ADD `client_id` tinyint(4) NOT NULL default '0';

ALTER TABLE `mos_newsfeeds` CHANGE `ordering` `ordering` int(11) NOT NULL default '0';

DROP TABLE IF EXISTS mos_newsfeedscache;

DROP TABLE IF EXISTS mos_newsflash;

ALTER TABLE `mos_sections` ADD `params` TEXT NOT NULL;

DROP TABLE IF EXISTS mos_templates;

CREATE TABLE `mos_template_positions` (
  `id` int(11) NOT NULL auto_increment,
  `position` varchar(10) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `mos_templates_menu` (
  `template` varchar(50) NOT NULL default '',
  `menuid` int(11) NOT NULL default '0',
  `client_id` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`template`,`menuid`)
) TYPE=MyISAM;

ALTER TABLE `mos_users` ADD `activation` varchar(100) NOT NULL default '' AFTER `lastvisitDate`;
ALTER TABLE `mos_users` ADD `params` TEXT NOT NULL;

ALTER TABLE `mos_weblinks` ADD `params` TEXT NOT NULL;

INSERT INTO `mos_modules` VALUES ('', 'Search', '', 7, 'right', 0, '0000-00-00 00:00:00', 1, 'mod_search', 0, 0, 1, '', 1, 0);
INSERT INTO `mos_modules` VALUES ('', 'Random Image', '', 2, 'user1', 0, '0000-00-00 00:00:00', 1, 'mod_random_image', 0, 0, 1, '', 1, 0);
INSERT INTO `mos_modules` VALUES ('', 'Random Image', '', 2, 'user1', 0, '0000-00-00 00:00:00', 1, 'mod_random_image', 0, 0, 1, '', 1, 0);
INSERT INTO `mos_modules` VALUES ('', 'Banners', '', 1, 'banner', 0, '0000-00-00 00:00:00', 1, 'mod_banners', 0, 0, 0, 'banner_cids=\nmoduleclass_sfx=\n', 1, 0);

DELETE FROM `mos_modules` WHERE `module` = 'mod_counter';
DELETE FROM `mos_modules` WHERE `module` = 'mod_online';

DELETE FROM `mos_components` WHERE name='Media Manager';
DELETE FROM `mos_components` WHERE `option`='com_newsflash';

INSERT INTO mos_modules VALUES (0,'Components','',1,'cpanel',0,'0000-00-00 00:00:00',1,'mod_components',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Popular','',2,'cpanel',0,'0000-00-00 00:00:00',1,'mod_popular',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Latest Items','',3,'cpanel',0,'0000-00-00 00:00:00',1,'mod_latest',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Menu Stats','',4,'cpanel',0,'0000-00-00 00:00:00',1,'mod_stats',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Unread Messages','',1,'header',0,'0000-00-00 00:00:00',1,'mod_unread',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Online Users','',2,'header',0,'0000-00-00 00:00:00',1,'mod_online',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Full Menu','',1,'top',0,'0000-00-00 00:00:00',1,'mod_fullmenu',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Pathway','',1,'pathway',0,'0000-00-00 00:00:00',1,'mod_pathway',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Toolbar','',1,'toolbar',0,'0000-00-00 00:00:00',1,'mod_toolbar',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'System Message','',1,'inset',0,'0000-00-00 00:00:00',1,'mod_mosmsg',0,99,1,'',2,1);
INSERT INTO mos_modules VALUES (0,'Quick Icons','',1,'icon',0,'0000-00-00 00:00:00',1,'mod_quickicon',0,99,1,'',1,1);

INSERT INTO `mos_templates_menu` VALUES ('rhuk_solarflare_ii', '0', '0');
INSERT INTO `mos_templates_menu` VALUES ('mambo_admin_blue', '0', '1');

INSERT INTO `mos_template_positions` VALUES (0, 'left', 'Left Column');
INSERT INTO `mos_template_positions` VALUES (0, 'right', 'Right Column');
INSERT INTO `mos_template_positions` VALUES (0, 'top', '');
INSERT INTO `mos_template_positions` VALUES (0, 'bottom', '');
INSERT INTO `mos_template_positions` VALUES (0, 'inset', '');
INSERT INTO `mos_template_positions` VALUES (0, 'banner', '');
INSERT INTO `mos_template_positions` VALUES (0, 'header', '');
INSERT INTO `mos_template_positions` VALUES (0, 'footer', '');
INSERT INTO `mos_template_positions` VALUES (0, 'newsflash', '');
INSERT INTO `mos_template_positions` VALUES (0, 'legals', '');
INSERT INTO `mos_template_positions` VALUES (0, 'pathway', '');
INSERT INTO `mos_template_positions` VALUES (0, 'toolbar', '');
INSERT INTO `mos_template_positions` VALUES (0, 'cpanel', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user1', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user2', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user3', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user4', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user5', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user6', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user7', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user8', '');
INSERT INTO `mos_template_positions` VALUES (0, 'user9', '');
INSERT INTO `mos_template_positions` VALUES (0, 'advert1', '');
INSERT INTO `mos_template_positions` VALUES (0, 'advert2', '');
INSERT INTO `mos_template_positions` VALUES (0, 'advert3', '');
INSERT INTO `mos_template_positions` VALUES (0, 'icon', '');
INSERT INTO `mos_template_positions` VALUES (0, 'debug', '');

UPDATE `mos_components` SET `link` = '', `admin_menu_link` = '' WHERE `id` = '6' LIMIT 1;
INSERT INTO `mos_components` VALUES ('', 'Contact Categories', '', 0, 6, 'option=categories&section=com_contact_details', 'Manage contact categories', '', 2, 'js/ThemeOffice/categories.png', 1, '');
INSERT INTO `mos_components` VALUES ('', 'Manage Contacts', 'option=com_contact', 0, 6, 'option=com_contact', 'Edit contact details', 'com_contact', 0, 'js/ThemeOffice/component.png', 1, '');
INSERT INTO `mos_components` VALUES ('', 'Syndicate', '', 0, 0, 'option=com_syndicate', 'Manage Syndication Settings', 'com_syndicate', 0, 'js/ThemeOffice/component.png', 0, '');

INSERT INTO mos_mambots VALUES (1,'MOS Image','mosimage','content',0,-10000,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (2,'MOS Pagination','mospaging','content',0,10000,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (3,'Legacy Mambot Includer','legacybots','content',0,1,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (4,'SEF','mossef','content',0,3,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (5,'MOS Rating','mosvote','content',0,4,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (6,'Search Content','content.searchbot','search',0,1,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (7,'Search Weblinks','weblinks.searchbot','search',0,2,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (8,'Code support','moscode','content',0,2,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (9,'No WYSIWYG Editor','none','editors',0,1,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (10,'TinyMCE WYSIWYG Editor','tinymce','editors',0,2,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (11,'MOS Image Editor Button','mosimage.btn','editors-xtd',0,0,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (12,'MOS Pagebreak Editor Button','mospage.btn','editors-xtd',0,0,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO mos_mambots VALUES (13,'Search Contacts','contacts.searchbot','search',0,3,1,1,0,0,'0000-00-00 00:00:00','');

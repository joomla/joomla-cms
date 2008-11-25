# $Id$

# 1.5 to 1.6

-- 2008-08-25

ALTER TABLE `jos_core_acl_groups_aro_map`
 ADD INDEX aro_id_group_id_group_aro_map USING BTREE(`aro_id`, `group_id`);

--

ALTER TABLE `jos_weblinks`
 CHANGE `published` `state` TINYINT( 1 ) NOT NULL DEFAULT '0';

-- 2008-10-10

DROP TABLE `jos_groups`;

--
-- Table structure for table `jos_core_acl_acl`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_acl` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `section_value` varchar(100) NOT NULL default 'system',
  `allow` int(1) unsigned NOT NULL default '0',
  `enabled` int(1) unsigned NOT NULL default '0',
  `return_value` varchar(250) default NULL,
  `note` varchar(250) default NULL,
  `updated_date` int(10) unsigned NOT NULL default '1',
  `acl_type` int(1) unsigned NOT NULL default '0' COMMENT 'Defines to what level AXOs apply to the rule',
  PRIMARY KEY  (`id`),
  KEY `core_acl_enabled_acl` (`enabled`),
  KEY `core_acl_section_value_acl` (`section_value`),
  KEY `core_acl_updated_date_acl` (`updated_date`),
  KEY `core_acl_type` USING BTREE (`acl_type`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_acl_sections`
--


CREATE TABLE IF NOT EXISTS `jos_core_acl_acl_sections` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `jos_core_acl_value_acl_sections` (`value`),
  KEY `core_acl_hidden_acl_sections` (`hidden`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

INSERT IGNORE INTO `jos_core_acl_acl_sections` VALUES (1, 'system', 1, 'System', 0);
INSERT IGNORE INTO `jos_core_acl_acl_sections` VALUES (2, 'user', 2, 'User', 0);

-- --------------------------------------------------------


--
-- Table structure for table `jos_core_acl_aco`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_aco` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  `acl_type` int(1) unsigned NOT NULL default '1' COMMENT 'Defines to what level AXOs apply',
  `note` mediumtext,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `jos_core_acl_section_value_aco` (`section_value`,`value`),
  KEY `core_acl_hidden_aco` (`hidden`),
  KEY `core_acl_type_section` USING BTREE (`acl_type`,`section_value`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_aco_map`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_aco_map` (
  `acl_id` int(10) unsigned NOT NULL default '0',
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_aco_sections`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_aco_sections` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `core_acl_value_aco_sections` (`value`),
  KEY `core_acl_hidden_aco_sections` (`hidden`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_aro_map`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_aro_map` (
  `acl_id` int(10) unsigned NOT NULL default '0',
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS  `jos_core_acl_aro_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_axo`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_axo` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `jos_core_acl_section_value_value_axo` (`section_value`,`value`),
  KEY `core_acl_hidden_axo` (`hidden`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_axo_groups`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_axo_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '0',
  `lft` int(10) unsigned NOT NULL default '0',
  `rgt` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`,`value`),
  INDEX `jos_core_acl_value_axo_groups` (`value`),
  KEY `core_acl_parent_id_axo_groups` (`parent_id`),
  KEY `core_acl_lft_rgt_axo_groups` (`lft`,`rgt`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

INSERT IGNORE INTO `jos_core_acl_axo_groups` VALUES (1, 0, 1, 8, 'ROOT', -1);
INSERT IGNORE INTO `jos_core_acl_axo_groups` VALUES (2, 1, 2, 3, 'Public', '0');
INSERT IGNORE INTO `jos_core_acl_axo_groups` VALUES (3, 1, 4, 5, 'Registered', '1');
INSERT IGNORE INTO `jos_core_acl_axo_groups` VALUES (4, 1, 6, 7, 'Special', '2');

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_axo_groups_map`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_axo_groups_map` (
  `acl_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_axo_map`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_axo_map` (
  `acl_id` int(10) unsigned NOT NULL default '0',
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_axo_sections`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_axo_sections` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `jos_core_acl_value_axo_sections` (`value`),
  KEY `core_acl_hidden_axo_sections` (`hidden`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `jos_core_acl_groups_axo_map`
--

CREATE TABLE IF NOT EXISTS `jos_core_acl_groups_axo_map` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `axo_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`axo_id`),
  KEY `jos_core_acl_axo_id` (`axo_id`),
  INDEX `group_id_axo_id_groups_axo_map` USING BTREE(`axo_id`, `group_id`),
  INDEX `aro_id_group_id_groups_axo_map` USING BTREE(`group_id`, `axo_id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_acl', 0, 'Access Control', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_admin', 0, 'Admin', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_banners', 0, 'Banners', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_cache', 0, 'Cache', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_categories', 0, 'Categories', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_checkin', 0, 'Check In', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_config', 0, 'Config', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_contact', 0, 'Contact', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_content', 0, 'Content', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_installer', 0, 'Installer', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_languages', 0, 'Languages', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_mailto', 0, 'Mail To', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_massmail', 0, 'Massmail', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_media', 0, 'Media Manager', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_menus', 0, 'Menu Manager', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_messages', 0, 'Messages', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_modules', 0, 'Modules', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_newsfeeds', 0, 'Newsfeeds', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_plugins', 0, 'Plugins', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_poll', 0, 'Polls', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_search', 0, 'Search', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_sections', 0, 'Sections', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_templates', 0, 'Templates', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_trash', 0, 'Trash', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_user', 0, 'User Frontend', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_users', 0, 'Users Backend', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_weblinks', 0, 'Weblinks', 0);
INSERT INTO `jos_core_acl_acl_sections` VALUES (0, 'com_wrapper', 0, 'Wrapper', 0);

INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'system', 0, 'System', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_acl', 0, 'Access Control', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_admin', 0, 'Admin', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_banners', 0, 'Banners', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_categories', 0, 'Categories', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_cache', 0, 'Cache', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_checkin', 0, 'Check In', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_config', 0, 'Config', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_contact', 0, 'Contact', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_content', 0, 'Content', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_installer', 0, 'Installer', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_languages', 0, 'Languages', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_mailto', 0, 'Mail To', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_massmail', 0, 'Massmail', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_media', 0, 'Media Manager', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_menus', 0, 'Menu Manager', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_messages', 0, 'Messages', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_modules', 0, 'Modules', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_newsfeeds', 0, 'Newsfeeds', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_plugins', 0, 'Plugins', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_poll', 0, 'Polls', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_search', 0, 'Search', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_sections', 0, 'Sections', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_templates', 0, 'Templates', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_trash', 0, 'Trash', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_user', 0, 'User Frontend', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_users', 0, 'Users Backend', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_weblinks', 0, 'Weblinks', 0);
INSERT INTO `jos_core_acl_aco_sections` VALUES (0, 'com_wrapper', 0, 'Wrapper', 0);

INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_banners', 0, 'Banners', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_categories', 0, 'Categories', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_contact', 0, 'Contact', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_content', 0, 'Content', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_installer', 0, 'Installer', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_languages', 0, 'Languages', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_massmail', 0, 'Massmail', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_media', 0, 'Media Manager', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_menus', 0, 'Menu Manager', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_messages', 0, 'Messages', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_newsfeeds', 0, 'Newsfeeds', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_plugins', 0, 'Plugins', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_poll', 0, 'Polls', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_user', 0, 'User Frontend', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_users', 0, 'Users Backend', 0);
INSERT INTO `jos_core_acl_axo_sections` VALUES (0, 'com_weblinks', 0, 'Weblinks', 0);

-- Type 1 Permissions

INSERT INTO `jos_core_acl_aco` VALUES (0, 'system', 'login', 0, 'Login', 0, 1, 'ACO System Login Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'system', 'event.email', 0, 'Email Event', 0, 1, 'ACO System Email Event Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_acl', 'manage', 0, 'Manage', 0, 1, 'ACO Acess Control Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_banners', 'manage', 0, 'Manage', 0, 1, 'ACO Banners Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_checkin', 'manage', 0, 'Manage', 0, 1, 'ACO Checkin Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_cache', 'manage', 0, 'Manage', 0, 1, 'ACO Cache Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_config', 'manage', 0, 'Manage', 0, 1, 'ACO Config Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_categories', 'manage', 0, 'Manage', 0, 1, 'ACO Categories Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_contact', 'manage', 0, 'Manage', 0, 1, 'ACO Contacts Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_content', 'articles.manage', 0, 'Manage Article', 0, 1, 'ACO Content Manage Article Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_content', 'frontpage.manage', 0, 'Manage Frontpage', 0, 1, 'ACO Content Manage Frontpage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_installer', 'manage', 0, 'Manage', 0, 1, 'ACO Installer Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_installer', 'extension.install', 0, 'Install', 0, 1, 'ACO Installer Extension Install Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_installer', 'extension.uninstall', 0, 'Uninstall', 0, 1, 'ACO Installer Extension Uninstall Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_languages', 'manage', 0, 'Manage', 0, 1, 'ACO Language Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_massmail', 'manage', 0, 'Manage', 0, 1, 'ACO Massmail Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_media', 'manage', 0, 'Manage', 0, 1, 'ACO Media Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_menus', 'type.manage', 0, 'Manage Menu Types', 0, 1, 'ACO Menus Manage Types Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_menus', 'menus.manage', 0, 'Manage Menu Items', 0, 1, 'ACO Menus Manage Items Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_modules', 'manage', 0, 'Manage', 0, 1, 'ACO Modules Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_newsfeeds', 'manage', 0, 'Manage', 0, 1, 'ACO Newsfeeds Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_plugins', 'manage', 0, 'Manage', 0, 1, 'ACO Plugin Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_poll', 'manage', 0, 'Manage', 0, 1, 'ACO Poll Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_sections', 'manage', 0, 'Manage', 0, 1, 'ACO Sections Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_templates', 'manage', 0, 'Manage', 0, 1, 'ACO Templates Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_trash', 'manage', 0, 'Manage', 0, 1, 'ACO Trash Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_user', 'profile.edit', 0, 'Edit Profile', 0, 1, 'ACO User Edit Profile Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_users', 'manage', 0, 'Manage', 0, 1, 'ACO Users Manage Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_users', 'user.block', 0, 'Block User', 0, 1, 'ACO Users Block User Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_users', 'event.email', 0, 'Email Event', 0, 1, 'ACO Users Email Event Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_weblinks', 'manage', 0, 'Manage', 0, 1, 'ACO Weblinks Manage Desc');

-- Type 2 Permissions

INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_content', 'article.add', 0, 'Add Article', 0, 2, 'ACO Content Add Article Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_content', 'article.edit', 0, 'Edit Article', 0, 2, 'ACO Content Edit Article Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_content', 'article.publish', 0, 'Publish Article', 0, 2, 'ACO Content Publish Article Desc');

-- Type 3 Permissions

INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_content', 'article.view', 0, 'View Articles', 0, 3, 'ACO Content View Articles Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_categories', 'category.view', 0, 'View Categories', 0, 3, 'ACO Categories View Categories Desc');
INSERT INTO `jos_core_acl_aco` VALUES (0, 'com_sections', 'section.view', 0, 'View Sections', 0, 3, 'ACO Sections View Sections Desc');


# Update Sites
CREATE TABLE  `jos_updates` (
  `update_id` int(11) NOT NULL auto_increment,
  `update_site_id` int(11) default '0',
  `extension_id` int(11) default '0',
  `categoryid` int(11) default '0',
  `name` varchar(100) default '',
  `description` text,
  `element` varchar(100) default '',
  `type` varchar(20) default '',
  `folder` varchar(20) default '',
  `client_id` tinyint(3) default '0',
  `version` varchar(10) default '',
  `data` text,
  `detailsurl` text,
  PRIMARY KEY  (`update_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Available Updates';

CREATE TABLE  `jos_update_sites` (
  `update_site_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default '',
  `type` varchar(20) default '',
  `location` text,
  `enabled` int(11) default '0',
  PRIMARY KEY  (`update_site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Sites';

CREATE TABLE `jos_update_sites_extensions` (
  `update_site_id` INT DEFAULT 0,
  `extension_id` INT DEFAULT 0,
  INDEX `newindex`(`update_site_id`, `extension_id`)
) ENGINE = MYISAM CHARACTER SET utf8 COMMENT = 'Links extensions to update sites';

CREATE TABLE  `jos_update_categories` (
  `categoryid` int(11) NOT NULL auto_increment,
  `name` varchar(20) default '',
  `description` text,
  `parent` int(11) default '0',
  `updatesite` int(11) default '0',
  PRIMARY KEY  (`categoryid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Categories';


CREATE TABLE  `jos_tasks` (
  `taskid` int(10) unsigned NOT NULL auto_increment,
  `tasksetid` int(10) unsigned NOT NULL default '0',
  `data` text,
  `offset` int(11) default '0',
  `total` int(11) default '0',
  PRIMARY KEY  (`taskid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Individual tasks';

CREATE TABLE  `jos_tasksets` (
  `tasksetid` int(10) unsigned NOT NULL auto_increment,
  `taskname` varchar(100) default '',
  `extensionid` int(10) unsigned default '0',
  `executionpage` text,
  `landingpage` text,
  PRIMARY KEY  (`tasksetid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Task Sets';

-- 2008-11-25

ALTER TABLE `jos_core_acl_axo`
 MODIFY COLUMN `value` INTEGER(10) NOT NULL;


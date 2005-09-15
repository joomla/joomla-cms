# $Id: upgrade4014to45.sql 4 2005-09-06 19:22:37Z akede $

# First rename all existing tables

ALTER TABLE `mos_categories` RENAME `old_categories`;
ALTER TABLE `mos_articles` RENAME `old_articles`;
ALTER TABLE `mos_stories` RENAME `old_stories`;
ALTER TABLE `mos_banner` RENAME `old_banner`;
ALTER TABLE `mos_bannerclient` RENAME `old_bannerclient`;
ALTER TABLE `mos_bannerfinish` RENAME `old_bannerfinish`;
ALTER TABLE `mos_component_module` RENAME `old_component_module`;
ALTER TABLE `mos_components` RENAME `old_components`;
ALTER TABLE `mos_contact_details` RENAME `old_contact_details`;
ALTER TABLE `mos_counter` RENAME `old_counter`;
ALTER TABLE `mos_faqcont` RENAME `old_faqcont`;
ALTER TABLE `mos_groups` RENAME `old_groups`;
ALTER TABLE `mos_links` RENAME `old_links`;
ALTER TABLE `mos_mambo_modules` RENAME `old_mambo_modules`;
ALTER TABLE `mos_menu` RENAME `old_menu`;
ALTER TABLE `mos_menucontent` RENAME `old_menucontent`;
ALTER TABLE `mos_newsfeedscategory` RENAME `old_newsfeedcategory`;
ALTER TABLE `mos_newsfeedslinks` RENAME `old_newsfeedlinks`;
ALTER TABLE `mos_newsflash` RENAME `old_newsflash`;
ALTER TABLE `mos_poll_data` RENAME `old_poll_data`;
ALTER TABLE `mos_poll_date` RENAME `old_poll_date`;
ALTER TABLE `mos_poll_desc` RENAME `old_poll_desc`;
ALTER TABLE `mos_poll_menu` RENAME `old_poll_menu`;
ALTER TABLE `mos_queue` RENAME `old_queue`;
ALTER TABLE `mos_session` RENAME `old_session`;
ALTER TABLE `mos_system` RENAME `old_system`;
ALTER TABLE `mos_users` RENAME `old_users`;

# Then create MOS 4.5 database

#
# Table structure for table `mos_banner`
#

CREATE TABLE `mos_banner` (
  `bid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `type` varchar(10) NOT NULL default 'banner',
  `name` varchar(50) NOT NULL default '',
  `imptotal` int(11) NOT NULL default '0',
  `impmade` int(11) NOT NULL default '0',
  `clicks` int(11) NOT NULL default '0',
  `imageurl` varchar(100) NOT NULL default '',
  `clickurl` varchar(200) NOT NULL default '',
  `date` datetime default NULL,
  `showBanner` tinyint(1) NOT NULL default '0',
  `checked_out` tinyint(1) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `editor` varchar(50) default NULL,
  `custombannercode` text,
  PRIMARY KEY  (`bid`),
  KEY `viewbanner` (`showBanner`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `mos_banner`
#

# --------------------------------------------------------

#
# Table structure for table `mos_bannerclient`
#

CREATE TABLE `mos_bannerclient` (
  `cid` int(11) NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `contact` varchar(60) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `extrainfo` text NOT NULL,
  `checked_out` tinyint(1) NOT NULL default '0',
  `checked_out_time` time default NULL,
  `editor` varchar(50) default NULL,
  PRIMARY KEY  (`cid`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_bannerclient`
#

# --------------------------------------------------------

#
# Table structure for table `mos_bannerfinish`
#

CREATE TABLE `mos_bannerfinish` (
  `bid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `impressions` int(11) NOT NULL default '0',
  `clicks` int(11) NOT NULL default '0',
  `imageurl` varchar(50) NOT NULL default '',
  `datestart` datetime default NULL,
  `dateend` datetime default NULL,
  PRIMARY KEY  (`bid`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_bannerfinish`
#

# --------------------------------------------------------

#
# Table structure for table `mos_categories`
#

CREATE TABLE `mos_categories` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `image` varchar(100) NOT NULL default '',
  `section` varchar(20) NOT NULL default '',
  `image_position` varchar(10) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `editor` varchar(50) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`section`,`published`,`access`),
  KEY `idx_section` (`section`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_categories`
#

INSERT INTO `mos_categories` VALUES (1, 'Latest', 'Latest News', 'pastarchives.jpg', '1', 'left', 'The latest news from the Mambo Team', 1, 0, '0000-00-00 00:00:00', '', 0, 0, 1);
INSERT INTO `mos_categories` VALUES (2, 'MOS', 'Mambo', 'asterisk.png', 'com_weblinks', 'left', 'A selection of links that are all related to the Mambo project.', 1, 0, '0000-00-00 00:00:00', NULL, 0, 0, 0);
INSERT INTO `mos_categories` VALUES (3, 'Administrator', 'Administrator', '', 'help', 'left', 'MOS Administrator Help', 1, 0, '0000-00-00 00:00:00', NULL, 1, 0, 0);
INSERT INTO `mos_categories` VALUES (4, 'Templates', 'Templates', '', 'help', 'left', 'MOS Templates Help and Tutorials', 1, 0, '0000-00-00 00:00:00', NULL, 3, 0, 0);
INSERT INTO `mos_categories` VALUES (5, 'Developers', 'Developers', '', 'help', 'left', 'MOS Developers\' API', 1, 0, '0000-00-00 00:00:00', NULL, 4, 0, 0);
INSERT INTO `mos_categories` VALUES (6, 'Components', 'Components', '', 'help', 'left', 'Components Help', 1, 0, '0000-00-00 00:00:00', NULL, 2, 0, 0);
# --------------------------------------------------------

#
# Table structure for table `mos_components`
#

CREATE TABLE `mos_components` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `menuid` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned NOT NULL default '0',
  `admin_menu_link` varchar(255) NOT NULL default '',
  `admin_menu_alt` varchar(255) NOT NULL default '',
  `option` varchar(50) NOT NULL default '',
  `ordering` int(11) unsigned NOT NULL default '0',
  `admin_menu_img` varchar(255) NOT NULL default '',
  `iscore` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_components`
#

INSERT INTO `mos_components` VALUES (1, 'Banners', '', 0, 0, '', 'Banner Management', 'com_banners', 0, 'js/ThemeOffice/component.png', 1);
INSERT INTO `mos_components` VALUES (2, 'Manage Banners', '', 0, 1, 'option=com_banners', 'Active Banners', 'com_banners', 1, 'js/ThemeOffice/edit.png', 1);
INSERT INTO `mos_components` VALUES (3, 'Manage Clients', '', 0, 1, 'option=com_banners&task=listclients', 'Manage Clients', 'com_banners', 2, 'js/ThemeOffice/categories.png', 1);
INSERT INTO `mos_components` VALUES (4, 'Web Links', 'option=com_weblinks', 0, 0, '', 'Manage Weblinks', 'com_weblinks', 0, 'js/ThemeOffice/component.png', 1);
INSERT INTO `mos_components` VALUES (5, 'Newsflash', '', 0, 0, 'option=com_newsflash', 'Manage newsflashes', 'com_newsflash', 0, 'js/ThemeOffice/component.png', 1);
INSERT INTO `mos_components` VALUES (6, 'Contact', 'option=com_contact', 0, 0, 'option=com_contact', 'Edit contact details', 'com_contact', 0, 'js/ThemeOffice/component.png', 1);
INSERT INTO `mos_components` VALUES (7, 'Weblink Items', '', 0, 4, 'option=com_weblinks', 'View existing weblinks', 'com_weblinks', 1, 'js/ThemeOffice/edit.png', 1);
INSERT INTO `mos_components` VALUES (8, 'Weblink Categories', '', 0, 4, 'option=com_categories&section=com_weblinks', 'Manage weblink categories', '', 2, 'js/ThemeOffice/categories.png', 1);
INSERT INTO `mos_components` VALUES (9, 'FrontPage', 'option=com_frontpage', 0, 0, '', 'Manage Front Page Items', 'com_frontpage', 0, 'js/ThemeOffice/component.png', 1);
INSERT INTO `mos_components` VALUES (10, 'Manage Items', '', 0, 9, 'option=com_frontpage', 'Manage FrontPage Items', 'com_frontpage', 1, 'js/ThemeOffice/edit.png', 1);
INSERT INTO `mos_components` VALUES (11, 'Settings', '', 0, 9, 'option=com_frontpage&act=settings', 'FrontPage Settings', 'com_frontpage', 2, 'js/ThemeOffice/config.png', 1);
INSERT INTO `mos_components` VALUES (12, 'Polls', 'option=com_poll', 0, 0, 'option=com_poll', 'Manage Polls', 'com_poll', 0, 'js/ThemeOffice/component.png', 1);
INSERT INTO `mos_components` VALUES (13, 'News Feeds', '', 0, 0, '', 'News Feeds Management', 'com_newsfeeds', 0, 'js/ThemeOffice/component.png', 1);
INSERT INTO `mos_components` VALUES (14, 'Manage News Feeds', '', 0, 13, 'option=com_newsfeeds', 'Manage News Feeds', 'com_newsfeeds', 1, 'js/ThemeOffice/edit.png', 1);
INSERT INTO `mos_components` VALUES (15, 'Manage Categories', '', 0, 13, 'option=com_categories&section=com_newsfeeds', 'Manage Categories', '', 2, 'js/ThemeOffice/categories.png', 1);
INSERT INTO `mos_components` VALUES (16, 'Newsfeeds (Component)', 'option=com_newsfeeds', '', '', '','', '', '', '', '');
INSERT INTO `mos_components` VALUES (17, 'Media Manager', '', 0, 0, 'option=com_media', 'Media Manager', 'com_media', 0, 'js/ThemeOffice/media.png', 1);
INSERT INTO `mos_components` VALUES (18, 'Login', 'option=com_login', 0, 0, '', '', 'com_login', 0, '', 1);
# --------------------------------------------------------

#
# Table structure for table `mos_contact_details`
#

CREATE TABLE `mos_contact_details` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `con_position` varchar(50) default NULL,
  `address` text,
  `suburb` varchar(50) default NULL,
  `state` varchar(20) default NULL,
  `country` varchar(50) default NULL,
  `postcode` varchar(10) default NULL,
  `telephone` varchar(25) default NULL,
  `fax` varchar(25) default NULL,
  `misc` mediumtext,
  `image` varchar(100) default NULL,
  `imagepos` varchar(20) default NULL,
  `email_to` varchar(100) default NULL,
  `default_con` tinyint(1) unsigned NOT NULL default '0',
  `published` tinyint(1) unsigned NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_contact_details`
#

# --------------------------------------------------------

#
# Table structure for table `mos_content`
#

CREATE TABLE `mos_content` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `title_alias` varchar(100) NOT NULL default '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL default '0',
  `sectionid` int(11) unsigned NOT NULL default '0',
  `mask` int(11) unsigned NOT NULL default '0',
  `catid` int(11) unsigned NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL default '0',
  `created_by_alias` varchar(100) NOT NULL default '',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL default '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `attribs` text NOT NULL,
  `version` int(11) unsigned NOT NULL default '1',
  `parentid` int(11) unsigned NOT NULL default '0',
  `ordering` float unsigned NOT NULL default '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` int(11) unsigned NOT NULL default '0',
  `hits` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_section` (`sectionid`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_mask` (`mask`)
) TYPE=MyISAM;


#
# Table structure for table `mos_content_frontpage`
#

CREATE TABLE `mos_content_frontpage` (
  `content_id` int(11) NOT NULL default '0',
  `ordering` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`content_id`)
) TYPE=MyISAM;


#
# Table structure for table `mos_content_rating`
#

CREATE TABLE `mos_content_rating` (
  `content_id` int(11) NOT NULL default '0',
  `rating_sum` int(11) unsigned NOT NULL default '',
  `rating_count` int(11) unsigned NOT NULL default '0',
  `lastip` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`content_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

# Table structure for table `mos_core_log_items`
#
# To be implemented in Version 4.6

CREATE TABLE `mos_core_log_items` (
  `time_stamp` date NOT NULL default '0000-00-00',
  `item_table` varchar(50) NOT NULL default '',
  `item_id` int(11) unsigned NOT NULL default '0',
  `hits` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `mos_core_log_searches`
#
# To be implemented in Version 4.6

CREATE TABLE `mos_core_log_searches` (
  `search_term` varchar(128) NOT NULL default '',
  `hits` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `mos_groups`
#

CREATE TABLE `mos_groups` (
  `id` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_groups`
#

INSERT INTO `mos_groups` VALUES (0, 'Public');
INSERT INTO `mos_groups` VALUES (1, 'Registered');
INSERT INTO `mos_groups` VALUES (2, 'Special');
# --------------------------------------------------------

#
# Table structure for table `mos_help`
#

CREATE TABLE `mos_help` (
  `id` int(11) NOT NULL auto_increment,
  `lang` char(3) NOT NULL default 'eng',
  `context` varchar(40) NOT NULL default '',
  `name` varchar(40) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `parent` int(11) NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  `helptext` text NOT NULL,
  `catid` int(11) unsigned NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `IdLangIdx` (`id`,`lang`),
  KEY `id_2` (`id`),
  FULLTEXT KEY `HeltextIdx` (`helptext`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_help`
#

# --------------------------------------------------------

#
# Table structure for table `mos_menu`
#

CREATE TABLE `mos_menu` (
  `id` int(11) NOT NULL auto_increment,
  `menutype` varchar(25) default NULL,
  `name` varchar(100) default NULL,
  `link` text,
  `type` varchar(50) NOT NULL default '',
  `published` tinyint(1) NOT NULL default '0',
  `parent` int(11) unsigned NOT NULL default '0',
  `componentid` int(11) unsigned NOT NULL default '0',
  `sublevel` int(11) default '0',
  `ordering` int(11) default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `pollid` int(11) NOT NULL default '0',
  `browserNav` tinyint(4) default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `utaccess` tinyint(3) unsigned NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `componentid` (`componentid`,`menutype`,`published`,`access`),
  KEY `menutype` (`menutype`)
) TYPE=MyISAM;


#
# Dumping data for table `mos_messages`
#

CREATE TABLE `mos_messages` (
  `message_id` int(10) unsigned NOT NULL auto_increment,
  `user_id_from` int(10) unsigned NOT NULL default '0',
  `user_id_to` int(10) unsigned NOT NULL default '0',
  `folder_id` int(10) unsigned NOT NULL default '0',
  `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `state` int(11) NOT NULL default '0',
  `priority` int(1) unsigned NOT NULL default '0',
  `subject` varchar(230) NOT NULL default '',
  `message` text NOT NULL,
  PRIMARY KEY  (`message_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Dumping data for table `mos_messages_cfg`
#

CREATE TABLE `mos_messages_cfg` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `cfg_name` varchar(100) NOT NULL default '',
  `cfg_value` varchar(255) NOT NULL default '',
  UNIQUE `idx_user_var_name` (`user_id`,`cfg_name`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `mos_modules`
#

CREATE TABLE `mos_modules` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `ordering` tinyint(4) NOT NULL default '0',
  `position` varchar(10) default NULL,
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL default '0',
  `module` varchar(50) default NULL,
  `numnews` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `showtitle` tinyint(3) unsigned NOT NULL default '1',
  `params` text NOT NULL,
  `iscore` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `published` (`published`,`access`),
  KEY `newsfeeds` (`module`,`published`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_modules`
#

INSERT INTO `mos_modules` VALUES (1, 'Polls', '', 1, 'right', 0, '0000-00-00 00:00:00', 1, 'mod_poll', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (2, 'User Menu', '', 2, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_mainmenu', 0, 1, 1, 'menutype=usermenu', 1);
INSERT INTO `mos_modules` VALUES (3, 'Main Menu', '', 1, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_mainmenu', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (4, 'Login Form', '', 3, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_login', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (5, 'Syndicate', '', 2, 'right', 0, '0000-00-00 00:00:00', 1, 'mod_rssfeed', 0, 0, 0, '', 1);
INSERT INTO `mos_modules` VALUES (6, 'Browser Prefs', '', 3, 'right', 0, '0000-00-00 00:00:00', 0, 'mod_browser_prefs', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (7, 'Hit Counter', '', 1, 'user2', 0, '0000-00-00 00:00:00', 1, 'mod_counter', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (8, 'Latest News', '', 4, 'right', 0, '0000-00-00 00:00:00', 0, 'mod_latestnews', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (9, 'Newsfeeds', '', 5, 'right', 0, '0000-00-00 00:00:00', 0, 'mod_newsfeeds', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (10, 'Online Users', '', 5, 'left', 0, '0000-00-00 00:00:00', 0, 'mod_online', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (11, 'Statistics', '', 4, 'left', 0, '0000-00-00 00:00:00', 0, 'mod_stats', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (12, 'Who\'s Online', '', 1, 'user1', 0, '0000-00-00 00:00:00', 1, 'mod_whosonline', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (13, 'Most Read', '', 6, 'right', 0, '0000-00-00 00:00:00', 0, 'mod_mostread', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (14, 'Template Chooser','',6,'left',0,'0000-00-00 00:00:00',0,'mod_templatechooser', 0, 0, 1, 'show_preview=1', 1);
INSERT INTO `mos_modules` VALUES (15, 'Archive', '', 7, 'left', 0, '0000-00-00 00:00:00', 0, 'mod_archive', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (16, 'Sections', '', 8, 'left', 0, '0000-00-00 00:00:00', 0, 'mod_sections', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (17, 'Newsflash', '', 1, 'top', 0, '0000-00-00 00:00:00', 1, 'mod_newsflash', 0, 0, 1, '', 1);
INSERT INTO `mos_modules` VALUES (18, 'Related Items', '', 9, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_related_items', 0, 0, 1, '', 1);

# --------------------------------------------------------

#
# Table structure for table `mos_modules_menu`
#

CREATE TABLE `mos_modules_menu` (
  `moduleid` int(11) NOT NULL default '0',
  `menuid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`moduleid`,`menuid`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_modules_menu`
#

INSERT INTO `mos_modules_menu` VALUES (1, 1);
INSERT INTO `mos_modules_menu` VALUES (2, 0);
INSERT INTO `mos_modules_menu` VALUES (3, 0);
INSERT INTO `mos_modules_menu` VALUES (4, 0);
INSERT INTO `mos_modules_menu` VALUES (5, 1);
INSERT INTO `mos_modules_menu` VALUES (6, 0);
INSERT INTO `mos_modules_menu` VALUES (7, 0);
INSERT INTO `mos_modules_menu` VALUES (8, 0);
INSERT INTO `mos_modules_menu` VALUES (9, 0);
INSERT INTO `mos_modules_menu` VALUES (10, 0);
INSERT INTO `mos_modules_menu` VALUES (11, 0);
INSERT INTO `mos_modules_menu` VALUES (12, 0);
INSERT INTO `mos_modules_menu` VALUES (13, 0);
INSERT INTO `mos_modules_menu` VALUES (14, 0);
INSERT INTO `mos_modules_menu` VALUES (16, 1);
INSERT INTO `mos_modules_menu` VALUES (17, 0);
# --------------------------------------------------------

#
# Table structure for table `mos_newsfeeds`
#

CREATE TABLE `mos_newsfeeds` (
  `catid` int(11) NOT NULL default '0',
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `link` text NOT NULL,
  `filename` varchar(200) default NULL,
  `published` tinyint(1) NOT NULL default '0',
  `numarticles` int(11) unsigned NOT NULL default '1',
  `cache_time` int(11) unsigned NOT NULL default '3600',
  `checked_out` tinyint(3) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `published` (`published`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `mos_newsfeedscache`
#

CREATE TABLE `mos_newsfeedscache` (
  `time` varchar(14) default NULL,
  `cachefile` varchar(50) NOT NULL default '',
  `filedata` text NOT NULL
) TYPE=MyISAM;

#
# Dumping data for table `mos_newsfeedscache`
#

# --------------------------------------------------------

#
# Table structure for table `mos_newsflash`
#

CREATE TABLE `mos_newsflash` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL default '',
  `content` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `catid` int(11) unsigned NOT NULL default '0',
  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL default '0000-00-00 00:00:00',
  `access` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_newsflash`
#

INSERT INTO `mos_newsflash` VALUES (1, 'Newsflash 1', 'Mambo 4.5 is \'Power In Simplicity\'!.  It has never been easier to create your own dynamic site.  Manage all your content from the best CMS admin interface.', 1, 0, '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0);
INSERT INTO `mos_newsflash` VALUES (2, 'Newsflash 2', 'Yesterday all servers in the U.S. went out on strike in a bid to get more RAM and better CPUs. A spokes person said that the need for better RAM was due to some fool increasing the front-side bus speed. In future, busses will be told to slow down in residential motherboards.', 1, 0, '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0);
INSERT INTO `mos_newsflash` VALUES (3, 'Newsflash 3', 'Aoccdrnig to a rscheearch at an Elingsh uinervtisy, it deosn\'t mttaer in waht oredr the ltteers in a wrod are, the olny iprmoetnt tihng is taht frist and lsat ltteer is at the rghit pclae. The rset can be a toatl mses and you can sitll raed it wouthit porbelm. Tihs is bcuseae we do not raed ervey lteter by itslef but the wrod as a wlohe.', 1, 0, '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0);
# --------------------------------------------------------

#
# Table structure for table `mos_poll_data`
#

CREATE TABLE `mos_poll_data` (
  `id` int(11) NOT NULL auto_increment,
  `pollid` int(4) NOT NULL default '0',
  `text` text NOT NULL default '',
  `hits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`,`text`(1))
) TYPE=MyISAM;

#
# Dumping data for table `mos_poll_data`
#

# --------------------------------------------------------

#
# Table structure for table `mos_poll_date`
#

CREATE TABLE `mos_poll_date` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `vote_id` int(11) NOT NULL default '0',
  `poll_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `poll_id` (`poll_id`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_poll_date`
#

# --------------------------------------------------------

#
# Table structure for table `mos_polls`
#

CREATE TABLE `mos_polls` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `voters` int(9) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL default '0',
  `access` int(11) NOT NULL default '0',
  `lag` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Table structure for table `mos_poll_menu`
#

CREATE TABLE `mos_poll_menu` (
  `pollid` int(11) NOT NULL default '0',
  `menuid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`pollid`,`menuid`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_poll_menu`
#

# --------------------------------------------------------

#
# Table structure for table `mos_sections`
#

CREATE TABLE `mos_sections` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `image` varchar(100) NOT NULL default '',
  `scope` varchar(50) NOT NULL default '',
  `image_position` varchar(10) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
	`count` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_scope` (`scope`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_sections`
#

INSERT INTO `mos_sections` VALUES (1, 'News', 'The News', 'articles.jpg', 'content', 'right', 'Select a news topic from the list below, then select a news article to read.', 1, 0, '0000-00-00 00:00:00', 1, 0, 1);
INSERT INTO `mos_sections` VALUES (2, 'Articles', 'Articles', 'articles.jpg', 'content', 'right', 'Select a news topic from the list below, then select a news article to read.', 1, 0, '0000-00-00 00:00:00', 1, 0, 1);
INSERT INTO `mos_sections` VALUES (3, 'Faq', 'Frequently Asked Questions', 'articles.jpg', 'content', 'right', 'Select a news topic from the list below, then select a news article to read.', 1, 0, '0000-00-00 00:00:00', 1, 0, 1);

#
# Table structure for table `mos_session`
#

CREATE TABLE `mos_session` (
  `username` varchar(50) default '',
  `time` varchar(14) default '',
  `session_id` varchar(200) NOT NULL default '0',
  `guest` tinyint(4) default '1',
  `userid` int(11) default '0',
  `usertype` varchar(50) default '',
  `gid` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`session_id`),
  KEY `whosonline` (`guest`,`usertype`)
) TYPE=MyISAM;

#
# Table structure for table `mos_stats_agents`
#

CREATE TABLE `mos_stats_agents` (
  `agent` varchar(255) NOT NULL default '',
  `type` tinyint(1) unsigned NOT NULL default '0',
  `hits` int(11) unsigned NOT NULL default '1'
) TYPE=MyISAM;

#
# Dumping data for table `mos_stats_agents`
#

# --------------------------------------------------------

#
# Table structure for table `mos_templates`
#

CREATE TABLE `mos_templates` (
  `id` int(11) NOT NULL default '0',
  `cur_template` varchar(50) NOT NULL default '',
  `col_main` char(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_templates`
#

INSERT INTO `mos_templates` VALUES (0, 'peeklime', '3');
# --------------------------------------------------------

#
# Table structure for table `mos_users`
#

CREATE TABLE `mos_users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `username` varchar(25) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `usertype` varchar(25) NOT NULL default '',
  `block` tinyint(4) NOT NULL default '0',
  `sendEmail` tinyint(4) default '0',
  `gid` tinyint(3) unsigned NOT NULL default '1',
  `registerDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `usertype` (`usertype`),
  KEY `idx_name` (`name`)
) TYPE=MyISAM;

#
# Table structure for table `mos_usertypes`
#

CREATE TABLE `mos_usertypes` (
  `id` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `mask` varchar(11) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_usertypes`
#

INSERT INTO `mos_usertypes` VALUES (0, 'superadministrator', '');
INSERT INTO `mos_usertypes` VALUES (1, 'administrator', '');
INSERT INTO `mos_usertypes` VALUES (2, 'editor', '');
INSERT INTO `mos_usertypes` VALUES (3, 'user', '');
INSERT INTO `mos_usertypes` VALUES (4, 'author', '');
INSERT INTO `mos_usertypes` VALUES (5, 'publisher', '');
INSERT INTO `mos_usertypes` VALUES (6, 'manager', '');
# --------------------------------------------------------

#
# Table structure for table `mos_weblinks`
#

CREATE TABLE `mos_weblinks` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `sid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `url` varchar(250) NOT NULL default '',
  `description` varchar(250) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `hits` int(11) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `archived` tinyint(1) NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`,`published`,`archived`)
) TYPE=MyISAM;

#
# Table structure for table `mos_core_acl_aro`
#

CREATE TABLE `mos_core_acl_aro` (
  `aro_id` int(11) NOT NULL auto_increment,
  `section_value` varchar(240) NOT NULL default '0',
  `value` varchar(240) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`aro_id`),
  UNIQUE KEY `section_value_value_aro` (`section_value`,`value`),
  UNIQUE KEY `mos_gacl_section_value_value_aro` (`section_value`,`value`),
  KEY `hidden_aro` (`hidden`),
  KEY `mos_gacl_hidden_aro` (`hidden`)
) TYPE=MyISAM;

#
# Table structure for table `mos_core_acl_aro_groups`
#
CREATE TABLE `mos_core_acl_aro_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`),
  KEY `parent_id_aro_groups` (`parent_id`),
  KEY `mos_gacl_parent_id_aro_groups` (`parent_id`),
  KEY `mos_gacl_lft_rgt_aro_groups` (`lft`,`rgt`)
) TYPE=MyISAM;

#
# Dumping data for table `mos_core_acl_aro_groups`
#
INSERT INTO `mos_core_acl_aro_groups` VALUES (17,0,'ROOT',1,22);
INSERT INTO `mos_core_acl_aro_groups` VALUES (28,17,'USERS',2,21);
INSERT INTO `mos_core_acl_aro_groups` VALUES (29,28,'Public Frontend',3,12);
INSERT INTO `mos_core_acl_aro_groups` VALUES (18,29,'Registered',4,11);
INSERT INTO `mos_core_acl_aro_groups` VALUES (19,18,'Author',5,10);
INSERT INTO `mos_core_acl_aro_groups` VALUES (20,19,'Editor',6,9);
INSERT INTO `mos_core_acl_aro_groups` VALUES (21,20,'Publisher',7,8);
INSERT INTO `mos_core_acl_aro_groups` VALUES (30,28,'Public Backend',13,20);
INSERT INTO `mos_core_acl_aro_groups` VALUES (23,30,'Manager',14,19);
INSERT INTO `mos_core_acl_aro_groups` VALUES (24,23,'Administrator',15,18);
INSERT INTO `mos_core_acl_aro_groups` VALUES (25,24,'Super Administrator',16,17);

#
# Table structure for table `mos_core_acl_groups_aro_map`
#
CREATE TABLE `mos_core_acl_groups_aro_map` (
  `group_id` int(11) NOT NULL default '0',
  `section_value` varchar(240) NOT NULL default '',
  `aro_id` int(11) NOT NULL default '0',
  UNIQUE KEY `group_id_aro_id_groups_aro_map` (`group_id`,`section_value`,`aro_id`)
) TYPE=MyISAM;

#
# Table structure for table `mos_core_acl_aro_sections`
#
CREATE TABLE `mos_core_acl_aro_sections` (
  `section_id` int(11) NOT NULL auto_increment,
  `value` varchar(230) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`section_id`),
  UNIQUE KEY `value_aro_sections` (`value`),
  UNIQUE KEY `mos_gacl_value_aro_sections` (`value`),
  KEY `hidden_aro_sections` (`hidden`),
  KEY `mos_gacl_hidden_aro_sections` (`hidden`)
) TYPE=MyISAM;

INSERT INTO mos_core_acl_aro_sections VALUES (10,'users',1,'Users',0);



# --------------------------------------------------------

# Finally import old data

#--------Import users

INSERT INTO `mos_users`
	(`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `gid`)
SELECT o.id, o.name, o.username, o.email, o.password, o.usertype, o.block, o.sendEmail, o.gid
FROM old_users AS o;

#--------Update ACL tables
INSERT INTO `mos_core_acl_aro`
	 (aro_id,section_value,value,order_value,name,hidden)
SELECT mos_users.id, 'users', mos_users.id, '0', mos_users.name, '0'
	FROM mos_users;

INSERT INTO `mos_core_acl_groups_aro_map`
	(group_id,aro_id)
SELECT arg.group_id, mos_users.id AS aro_id
FROM mos_users
LEFT JOIN `mos_core_acl_aro_groups` AS arg ON arg.name='Super Administrator'
WHERE usertype='superadministrator';

INSERT INTO `mos_core_acl_groups_aro_map`
	(group_id,aro_id)
SELECT arg.group_id, mos_users.id AS aro_id
FROM mos_users
LEFT JOIN `mos_core_acl_aro_groups` AS arg ON arg.name='Administrator'
WHERE usertype='administrator';

INSERT INTO `mos_core_acl_groups_aro_map`
	(group_id,aro_id)
SELECT arg.group_id, mos_users.id AS aro_id
FROM mos_users
LEFT JOIN `mos_core_acl_aro_groups` AS arg ON arg.name='Editor'
WHERE usertype='editor';

INSERT INTO `mos_core_acl_groups_aro_map`
	(group_id,aro_id)
SELECT arg.group_id, mos_users.id AS aro_id
FROM mos_users
LEFT JOIN `mos_core_acl_aro_groups` AS arg ON arg.name='Registered'
WHERE usertype NOT IN ('editor','administrator','superadministrator');

UPDATE `mos_users` SET gid='25' WHERE usertype='superadministrator';
UPDATE `mos_users` SET gid='24' WHERE usertype='administrator';
UPDATE `mos_users` SET gid='20' WHERE usertype='editor';
UPDATE `mos_users` SET gid='18' WHERE usertype NOT IN ('editor','administrator','superadministrator');

#--------Import Categories for content

INSERT INTO mos_categories (`title`, `name`, `image`, `section`, `image_position`,
	`description`, `published`, `checked_out`, `checked_out_time`, `ordering`,
	`access`, `count`)
SELECT categoryname, categoryname, 'articles.jpg' AS new_image, mos_sections.id,
	'left' AS new_image_position, categoryname, '1' AS new_published,
	'0' AS new_checked_out, '0000-00-00 00:00:00' AS new_checkedout_time,
	'0' AS new_ordering, '0' AS new_access, '1' AS new_count
	FROM old_categories
INNER JOIN mos_sections ON old_categories.section=mos_sections.title;

#--------Import Weblink Categories

INSERT INTO mos_categories (title, name, image, section, image_position, description, published, checked_out, checked_out_time, ordering, access, count) select categoryname, categoryname, 'articles.jpg' as new_image, 'com_weblinks', 'left' as new_image_position, categoryname, '1' as new_published, '0' as new_checked_out, '0000-00-00 00:00:00' as new_checkedout_time, '0' as new_ordering, '0' as new_access, '1'as new_count from old_categories WHERE section='Weblinks';

#--------Import articles

INSERT INTO mos_content (`title`, `introtext`, `state`, `sectionid`, `mask`, `catid`, `created`,
	`created_by`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `publish_up`,
	`publish_down`, `images`, `urls`, `attribs`, `version`, `parentid`, `ordering`, `metakey`,
	`metadesc`, `access`, `hits`)
SELECT o.title, o.content, o.published, '2' AS new_sectionid, '0' AS new_mask,
	mos_categories.id, o.date AS new_created, mos_users.id AS new_created_by,
	o.date AS new_modified, mos_users.id AS new_modified_by,
	'0' AS new_checked_out, '0000-00-00 00:00:00' AS new_checked_out_time,
	'2003-01-01 00:00:00' AS new_publish_up, '0000-00-00 00:00:00' AS new_publish_down,
	'' AS new_images, '' AS new_urls, '' AS new_attribs, '1' AS new_version,
	'0' AS new_parent, o.ordering AS new_ordering, o.title, o.title, '0' AS new_access,
	o.counter AS new_hits
FROM old_articles AS o
INNER JOIN old_categories ON o.catid=old_categories.categoryid
LEFT JOIN mos_categories on old_categories.categoryname=mos_categories.title
LEFT JOIN mos_users ON mos_users.name = o.author
WHERE o.archived = '0';

# archived

INSERT INTO mos_content (`title`, `introtext`, `state`, `sectionid`, `mask`, `catid`, `created`,
	`created_by`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `publish_up`,
	`publish_down`, `images`, `urls`, `attribs`, `version`, `parentid`, `ordering`, `metakey`,
	`metadesc`, `access`, `hits`)
SELECT o.title, o.content, o.published, '2' AS new_sectionid, '0' AS new_mask,
	mos_categories.id, o.date AS new_created, mos_users.id AS new_created_by,
	o.date AS new_modified, mos_users.id AS new_modified_by,
	'0' AS new_checked_out, '0000-00-00 00:00:00' AS new_checked_out_time,
	'2003-01-01 00:00:00' AS new_publish_up, '0000-00-00 00:00:00' AS new_publish_down,
	'' AS new_images, '' AS new_urls, '' AS new_attribs, '1' AS new_version,
	'0' AS new_parent, o.ordering AS new_ordering, o.title, o.title, '0' AS new_access,
	o.counter AS new_hits
FROM old_articles AS o
INNER JOIN old_categories ON o.catid=old_categories.categoryid
LEFT JOIN mos_categories on old_categories.categoryname=mos_categories.title
LEFT JOIN mos_users ON mos_users.name = o.author
WHERE o.archived = '1';


#--------Import Stories (News)

INSERT INTO mos_content (`title`, `introtext`, `fulltext`, `state`, `sectionid`,
	`mask`, `catid`, `created`,
	`created_by`, `modified`, `modified_by`,
	`checked_out`, `checked_out_time`,
	`publish_up`, `publish_down`,
	`images`, `urls`,
	`attribs`, `version`, `parentid`, `ordering`,
	`metakey`, `metadesc`, `access`, `hits`)
SELECT o.title, CONCAT_WS('','{mosimage}',o.introtext), o.fultext, o.published as new_state, '1' as new_sectionid,
	'0' as new_mask, mos_categories.id, o.time as new_created,
	mos_users.id as new_created_by, o.time as new_modified, mos_users.id as new_modified_by,
	'0' as new_checked_out, '0000-00-00 00:00:00' as new_checked_out_time,
	'2003-01-01 00:00:00' as new_publish_up, '0000-00-00 00:00:00' as new_publish_down,
	CONCAT_WS('|',o.newsimage,o.image_position,'Image','0') as new_images, '' as new_urls,
	'' as new_attribs, '1' as new_version, '0' as new_parent, o.ordering as new_ordering,
	o.title, o.title, o.access as new_access, o.counter as new_hits
FROM old_stories AS o
INNER JOIN old_categories ON o.catid=old_categories.categoryid
LEFT JOIN mos_categories on old_categories.categoryname=mos_categories.title
LEFT JOIN mos_users ON mos_users.name = o.author
WHERE o.archived = '0';

INSERT INTO mos_content (`title`, `introtext`, `fulltext`, `state`, `sectionid`,
	`mask`, `catid`, `created`,
	`created_by`, `modified`, `modified_by`,
	`checked_out`, `checked_out_time`,
	`publish_up`, `publish_down`,
	`images`, `urls`,
	`attribs`, `version`, `parentid`, `ordering`,
	`metakey`, `metadesc`, `access`, `hits`)
SELECT o.title, CONCAT_WS('','{mosimage}',o.introtext), o.fultext, '-1' as new_state, '1' as new_sectionid,
	'0' as new_mask, mos_categories.id, o.time as new_created,
	mos_users.id as new_created_by, o.time as new_modified, mos_users.id as new_modified_by,
	'0' as new_checked_out, '0000-00-00 00:00:00' as new_checked_out_time,
	'2003-01-01 00:00:00' as new_publish_up, '0000-00-00 00:00:00' as new_publish_down,
	CONCAT_WS('|',o.newsimage,o.image_position,'Image','0') as new_images, '' as new_urls,
	'' as new_attribs, '1' as new_version, '0' as new_parent, o.ordering as new_ordering,
	o.title, o.title, o.access as new_access, o.counter as new_hits
FROM old_stories AS o
INNER JOIN old_categories ON o.catid=old_categories.categoryid
LEFT JOIN mos_categories on old_categories.categoryname=mos_categories.title
LEFT JOIN mos_users ON mos_users.name = o.author
WHERE o.archived = '1';

#--------Import FAQs

INSERT INTO mos_content (`title`, `introtext`, `state`, `sectionid`,
	`mask`, `catid`, `created`,
	`created_by`, `modified`, `modified_by`,
	`checked_out`, `checked_out_time`,
	`publish_up`, `publish_down`,
	`images`, `urls`,
	`attribs`, `version`, `parentid`, `ordering`,
	`metakey`, `metadesc`, `access`, `hits`)
select o.title, o.content, o.published as new_state, '3' as new_sectionid,
	'0' as new_mask, mos_categories.id, NOW() as new_created,
	'62' as new_created_by, NOW() as new_modified, '62' as new_modified_by,
	'0' as new_checked_out, '0000-00-00 00:00:00' as new_checked_out_time,
	'2003-01-01 00:00:00' as new_publish_up, '0000-00-00 00:00:00' as new_publish_down,
	'' as new_images, '' as new_urls,
	'' as new_attribs, '1' as new_version, '0' as new_parent, o.ordering as new_ordering,
	o.title, o.title, '0' as new_access, o.counter as new_hits
FROM old_faqcont AS o
INNER JOIN old_categories ON o.catid=old_categories.categoryid
LEFT JOIN mos_categories on old_categories.categoryname=mos_categories.title
WHERE o.archived = '0';

#--------Import Weblinks

INSERT INTO mos_weblinks (catid, sid, title, url, description, date, hits, published,
	checked_out, checked_out_time, ordering, archived, approved)
select mos_categories.id, old_links.sid, old_links.title, old_links.url,
	old_links.description, old_links.date, old_links.hits, old_links.published,
	old_links.checked_out, old_links.checked_out_time, '0',old_links.archived, old_links.approved
FROM (old_categories
inner join mos_categories on old_categories.categoryname=mos_categories.name)
INNER JOIN old_links on old_categories.categoryid=old_links.catid;



#--------Import contact_details

INSERT INTO mos_contact_details (name, con_position, address, suburb, state,
	country, postcode, telephone, fax, misc, image, imagepos, email_to, default_con, published)
select o.name, '', o.address, o.suburb, o.state, o.country, o.postcode, o.telephone,
	o.fax, '', '', '',o.email_to, 1, 1
FROM old_contact_details AS o;

#--------Import menu typed content
# temporarily hold the menu id in the catid field

INSERT INTO mos_content (`title`, `introtext`, `state`, `sectionid`,
	`mask`, `catid`, `created`,
	`created_by`, `modified`, `modified_by`,
	`checked_out`, `checked_out_time`,
	`publish_up`, `publish_down`,
	`images`, `urls`,
	`attribs`, `version`, `parentid`, `ordering`,
	`metakey`, `metadesc`, `access`, `hits`)
select o.heading, o.content, '1' as new_state, '0' as new_sectionid,
	'0' as new_mask, o.menuid AS new_catid, NOW() as new_created,
	'62' as new_created_by, NOW() as new_modified, '62' as new_modified_by,
	'0' as new_checked_out, '0000-00-00 00:00:00' as new_checked_out_time,
	'2003-01-01 00:00:00' as new_publish_up, '0000-00-00 00:00:00' as new_publish_down,
	'' as new_images, '' as new_urls,
	'' as new_attribs, '1' as new_version, '0' as new_parent, '1' as new_ordering,
	o.heading, o.heading, '0' as new_access, '0' as new_hits
FROM old_menucontent AS o;

#--------Import menu details

# the old home page
INSERT INTO mos_menu (`id`, `menutype`, `name`,
	`link`,
	`type`, `published`, `parent`,
	`componentid`, `sublevel`,
	`ordering`, `checked_out`,
	`checked_out_time`,	`pollid`,
	`browserNav`, `access`, `utaccess`, `params`)
SELECT o.id, o.menutype AS new_menutype, o.name AS new_name,
	'index.php?option=com_frontpage' AS new_link,
	'components' AS new_contenttype, o.inuse, o.componentid,
	mos_components.id AS new_componentid, o.sublevel AS new_sublevel,
	o.ordering AS new_ordering, '0' as new_checked_out,
	'0000-00-00 0000:00:00' AS new_checked_out_time, '0' AS new_pollid,
	o.browserNav as new_browsernav, o.access AS new_access, '0', ''
FROM old_menu AS o
LEFT JOIN mos_content ON mos_content.catid=o.id AND mos_content.sectionid='0'
LEFT JOIN mos_components ON mos_components.option = 'com_frontpage' AND mos_components.id=9
WHERE o.link='index.php' AND o.contenttype='mambo';

# the old news page
INSERT INTO mos_menu (`id`, `menutype`, `name`,
	`link`,
	`type`, `published`, `parent`,
	`componentid`, `sublevel`,
	`ordering`, `checked_out`,
	`checked_out_time`,	`pollid`,
	`browserNav`, `access`, `utaccess`, `params`)
SELECT o.id, o.menutype AS new_menutype, o.name AS new_name,
	'index.php?option=com_content&task=section&id=1' AS new_link,
	'content_section' AS new_contenttype, o.inuse, o.componentid,
	mos_content.id AS new_componentid, o.sublevel AS new_sublevel,
	o.ordering AS new_ordering, '0' as new_checked_out,
	'0000-00-00 0000:00:00' AS new_checked_out_time, '0' AS new_pollid,
	o.browserNav as new_browsernav, o.access AS new_access, '0', ''
FROM old_menu AS o
LEFT JOIN mos_content ON mos_content.catid=o.id AND mos_content.sectionid='0'
WHERE o.link='index.php?option=news' AND o.contenttype='mambo';

# the old articles page
INSERT INTO mos_menu (`id`, `menutype`, `name`,
	`link`,
	`type`, `published`, `parent`,
	`componentid`, `sublevel`,
	`ordering`, `checked_out`,
	`checked_out_time`,	`pollid`,
	`browserNav`, `access`, `utaccess`, `params`)
SELECT o.id, o.menutype AS new_menutype, o.name AS new_name,
	'index.php?option=com_content&task=section&id=2' AS new_link,
	'content_section' AS new_contenttype, o.inuse, o.componentid,
	mos_content.id AS new_componentid, o.sublevel AS new_sublevel,
	o.ordering AS new_ordering, '0' as new_checked_out,
	'0000-00-00 0000:00:00' AS new_checked_out_time, '0' AS new_pollid,
	o.browserNav as new_browsernav, o.access AS new_access, '0', ''
FROM old_menu AS o
LEFT JOIN mos_content ON mos_content.catid=o.id AND mos_content.sectionid='0'
WHERE o.link='index.php?option=articles' AND o.contenttype='mambo';

# the old faq page
INSERT INTO mos_menu (`id`, `menutype`, `name`,
	`link`,
	`type`, `published`, `parent`,
	`componentid`, `sublevel`,
	`ordering`, `checked_out`,
	`checked_out_time`,	`pollid`,
	`browserNav`, `access`, `utaccess`, `params`)
SELECT o.id, o.menutype AS new_menutype, o.name AS new_name,
	'index.php?option=com_content&task=section&id=3' AS new_link,
	'content_section' AS new_contenttype, o.inuse, o.componentid,
	mos_content.id AS new_componentid, o.sublevel AS new_sublevel,
	o.ordering AS new_ordering, '0' as new_checked_out,
	'0000-00-00 0000:00:00' AS new_checked_out_time, '0' AS new_pollid,
	o.browserNav as new_browsernav, o.access AS new_access, '0', ''
FROM old_menu AS o
LEFT JOIN mos_content ON mos_content.catid=o.id AND mos_content.sectionid='0'
WHERE o.link='index.php?option=faq' AND o.contenttype='mambo';

# the old weblinks page
INSERT INTO mos_menu (`id`, `menutype`, `name`,
	`link`,
	`type`, `published`, `parent`,
	`componentid`, `sublevel`,
	`ordering`, `checked_out`,
	`checked_out_time`,	`pollid`,
	`browserNav`, `access`, `utaccess`, `params`)
SELECT o.id, o.menutype AS new_menutype, o.name AS new_name,
	'index.php?option=com_weblinks' AS new_link,
	'components' AS new_contenttype, o.inuse, o.componentid,
	mos_components.id AS new_componentid, o.sublevel AS new_sublevel,
	o.ordering AS new_ordering, '0' as new_checked_out,
	'0000-00-00 0000:00:00' AS new_checked_out_time, '0' AS new_pollid,
	o.browserNav as new_browsernav, o.access AS new_access, '0', ''
FROM old_menu AS o
LEFT JOIN mos_content ON mos_content.catid=o.id AND mos_content.sectionid='0'
LEFT JOIN mos_components ON mos_components.option = 'com_weblinks'
WHERE o.link='index.php?option=weblinks' AND o.contenttype='mambo' AND mos_components.id=4;

# the old contacts page
INSERT INTO mos_menu (`id`, `menutype`, `name`,
	`link`,
	`type`, `published`, `parent`,
	`componentid`, `sublevel`,
	`ordering`, `checked_out`,
	`checked_out_time`,	`pollid`,
	`browserNav`, `access`, `utaccess`, `params`)
SELECT o.id, o.menutype AS new_menutype, o.name AS new_name,
	'index.php?option=com_contact' AS new_link,
	'components' AS new_contenttype, o.inuse, o.componentid,
	mos_components.id AS new_componentid, o.sublevel AS new_sublevel,
	o.ordering AS new_ordering, '0' as new_checked_out,
	'0000-00-00 0000:00:00' AS new_checked_out_time, '0' AS new_pollid,
	o.browserNav as new_browsernav, o.access AS new_access, '0', ''
FROM old_menu AS o
LEFT JOIN mos_content ON mos_content.catid=o.id AND mos_content.sectionid='0'
LEFT JOIN mos_components ON mos_components.option = 'com_contact'
WHERE o.link='index.php?option=contact' AND o.contenttype='mambo';

# the old direct links
INSERT INTO mos_menu (`id`, `menutype`, `name`,
	`link`,
	`type`, `published`, `parent`,
	`componentid`, `sublevel`,
	`ordering`, `checked_out`,
	`checked_out_time`,	`pollid`,
	`browserNav`, `access`, `utaccess`, `params`)
SELECT o.id, o.menutype AS new_menutype, o.name AS new_name,
	o.link AS new_link,
	'url' AS new_contenttype, o.inuse, o.componentid,
	mos_content.id AS new_componentid, o.sublevel AS new_sublevel,
	o.ordering AS new_ordering, '0' as new_checked_out,
	'0000-00-00 0000:00:00' AS new_checked_out_time, '0' AS new_pollid,
	o.browserNav as new_browsernav, o.access AS new_access, '0', ''
FROM old_menu AS o
LEFT JOIN mos_content ON mos_content.catid=o.id AND mos_content.sectionid='0'
WHERE o.contenttype='web';

# the typed content
INSERT INTO mos_menu (`id`, `menutype`, `name`,
	`link`,
	`type`, `published`, `parent`,
	`componentid`, `sublevel`,
	`ordering`, `checked_out`,
	`checked_out_time`,	`pollid`,
	`browserNav`, `access`, `utaccess`, `params`)
SELECT o.id, o.menutype AS new_menutype, o.name AS new_name,
	CONCAT_WS('','index.php?option=com_content&task=view&id=',mos_content.id) AS new_link,
	'content_typed' AS new_contenttype, o.inuse, o.componentid,
	mos_content.id AS new_componentid, o.sublevel AS new_sublevel,
	o.ordering AS new_ordering, '0' as new_checked_out,
	'0000-00-00 0000:00:00' AS new_checked_out_time, '0' AS new_pollid,
	o.browserNav as new_browsernav, o.access AS new_access, '0', ''
FROM old_menu AS o
LEFT JOIN mos_content ON mos_content.catid=o.id AND mos_content.sectionid='0'
WHERE o.contenttype='typed';

# the old frontpage item
INSERT INTO `mos_content_frontpage`
SELECT mos_content.id, old_stories.ordering FROM mos_content 
INNER JOIN old_stories ON mos_content.title=old_stories.title WHERE old_stories.frontpage=1;

# usermenu
INSERT INTO `mos_menu` VALUES ('', 'usermenu', 'Your Details', 'index.php?option=com_user&task=UserDetails', 'url', 1, 0, 0, 0, 1, 0, '2000-00-00 00:00:00', 0, 0, 1, 3, '');
INSERT INTO `mos_menu` VALUES ('', 'usermenu', 'Submit News', 'index.php?option=com_content&task=new&sectionid=1', 'url', 1, 0, 0, 0, 2, 0, '2000-00-00 00:00:00', 0, 0, 1, 2, '');
INSERT INTO `mos_menu` VALUES ('', 'usermenu', 'Submit WebLink', 'index.php?option=com_weblinks&task=new', 'url', 1, 0, 0, 0, 4, 0, '2000-00-00 00:00:00', 0, 0, 1, 2, '');
INSERT INTO `mos_menu` VALUES ('', 'usermenu', 'Check-In My Items', 'index.php?option=com_user&task=CheckIn', 'url', 1, 0, 0, 0, 5, 0, '0000-00-00 00:00:00', 0, 0, 1, 2, '');


# null the catid field
UPDATE mos_content SET catid='0' where sectionid='0';

# --------------------------------------------------------
# statistics

INSERT INTO `mos_stats_agents`
	(`agent`,`hits`,`type`)
SELECT `name`,`count`,'0'
FROM old_counter
WHERE `type`='browser';

INSERT INTO `mos_stats_agents`
	(`agent`,`hits`,`type`)
SELECT `name`,`count`,'1'
FROM old_counter
WHERE `type`='OS';
# --------------------------------------------------------

UPDATE `mos_menu` SET link='index.php?option=com_user&task=UserDetails' WHERE link='index.php?option=user&op=UserDetails';
UPDATE `mos_menu` SET link='index.php?option=com_user&task=CheckIn' WHERE link='index.php?option=user&op=CheckIn';

# registration control moved to configuration
UPDATE `mos_modules` SET params='' WHERE module='mod_login';

# special group not supported in the core pending ACL improvements in 4.6
DELETE FROM `mos_groups` WHERE name='Special';

## add activation
ALTER TABLE `mos_users` ADD `activation` varchar(100) NOT NULL default '' AFTER `lastvisitDate`;

DROP TABLE IF EXISTS mos_templates;
# Table structure for table `mos_templates_menu`

CREATE TABLE `mos_templates_menu` (
  `template` varchar(50) NOT NULL default '',
  `menuid` int(11) NOT NULL default '0',
  `client_id` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`template`,`menuid`)
) TYPE=MyISAM;

INSERT INTO `mos_templates_menu` VALUES ('rhuk_solarflare_ii', '0', '0');
INSERT INTO `mos_templates_menu` VALUES ('mambo_admin_blue', '0', '1');

# $Id$


#
# Table structure for table `#__assets`
#

CREATE TABLE IF NOT EXISTS `#__assets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set parent.',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `level` int(10) unsigned NOT NULL COMMENT 'The cached level in the nested tree.',
  `name` varchar(50) NOT NULL COMMENT 'The unique name for the asset.\n',
  `title` varchar(100) NOT NULL COMMENT 'The descriptive title for the asset.',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_asset_name` (`name`),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

#
# Dumping data for table `#__assets`
#

INSERT INTO `#__assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)
VALUES
	(1,0,1,74,0,'root.1','Root Asset','{"core.admin":{"8":1},"core.login":{"2":1},"core.manage":{"7":1},"core.create":{"7":1},"core.delete":{"7":1},"core.edit":{"7":1},"core.edit.state":{"7":1}}'),
	(2,1,2,3,1,'com_admin','com_admin','{}'),
	(3,1,4,5,1,'com_banners','com_banners','{}'),
	(4,1,6,7,1,'com_cache','com_cache','{"core.manage":{"6":0}}'),
	(5,1,8,9,1,'com_checkin','com_checkin','{"core.manage":{"6":0}}'),
	(6,1,10,11,1,'com_config','com_config','{}'),
	(7,1,12,13,1,'com_contact','com_contact','{"core.manage":{"6":0},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(8,1,14,31,1,'com_content','com_content','{"core.manage":[],"core.create":{"5":1},"core.delete":[],"core.edit":{"5":1,"3":0},"core.edit.state":{"5":1,"4":0}}'),
	(9,1,32,33,1,'com_cpanel','com_cpanel','{}'),
	(10,1,34,35,1,'com_installer','com_installer','{"core.manage":{"6":0},"core.create":[],"core.delete":[],"core.edit.state":[]}'),
	(11,1,36,37,1,'com_languages','com_languages','{"core.manage":{"6":0},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(12,1,38,39,1,'com_login','com_login','{}'),
	(13,1,40,41,1,'com_mailto','com_mailto','{}'),
	(14,1,42,43,1,'com_massmail','com_massmail','{}'),
	(15,1,44,45,1,'com_media','com_media','{"core.manage":[],"core.create":{"3":1,"4":1,"5":1},"core.delete":{"5":1},"core.edit":[],"core.edit.state":[]}'),
	(16,1,46,47,1,'com_menus','com_menus','{"core.manage":{"6":0},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(17,1,48,49,1,'com_messages','com_messages','{}'),
	(18,1,50,51,1,'com_modules','com_modules','{"core.manage":{"6":0},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(19,1,52,53,1,'com_newsfeeds','com_newsfeeds','{}'),
	(20,1,54,55,1,'com_plugins','com_plugins','{"core.manage":{"6":0},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(21,1,56,57,1,'com_redirect','com_redirect','{}'),
	(22,1,58,59,1,'com_search','com_search','{}'),
	(23,1,60,61,1,'com_templates','com_templates','{"core.manage":{"6":0,"7":0},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(24,1,62,63,1,'com_users','com_users','{"core.manage":{"6":0},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(25,1,64,71,1,'com_weblinks','com_weblinks','{}'),
	(26,1,72,73,1,'com_wrapper','com_wrapper','{}');

# -------------------------------------------------------

#
# Table structure for table `#__banner`
#

CREATE TABLE `#__banner` (
  `bid` integer NOT NULL auto_increment,
  `cid` integer NOT NULL default '0',
  `type` varchar(30) NOT NULL default 'banner',
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `imptotal` integer NOT NULL default '0',
  `impmade` integer NOT NULL default '0',
  `clicks` integer NOT NULL default '0',
  `imageurl` varchar(100) NOT NULL default '',
  `clickurl` varchar(200) NOT NULL default '',
  `date` datetime default NULL,
  `showBanner` tinyint(1) NOT NULL default '0',
  `checked_out` tinyint(1) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `editor` varchar(50) default NULL,
  `custombannercode` text,
  `catid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT NOT NULL,
  `sticky` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `ordering` INTEGER NOT NULL DEFAULT 0,
  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL default '0000-00-00 00:00:00',
  `tags` TEXT NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY  (`bid`),
  KEY `viewbanner` (`showBanner`),
  INDEX `idx_banner_catid`(`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__bannerclient`
#

CREATE TABLE `#__bannerclient` (
  `cid` integer NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `contact` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `extrainfo` text NOT NULL,
  `checked_out` tinyint(1) NOT NULL default '0',
  `checked_out_time` time default NULL,
  `editor` varchar(50) default NULL,
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__bannertrack`
#

CREATE TABLE  `#__bannertrack` (
  `track_date` date NOT NULL,
  `track_type` integer unsigned NOT NULL,
  `banner_id` integer unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__categories`
#

CREATE TABLE `#__categories` (
  `id` int(11) NOT NULL auto_increment,
  `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `level` int(10) unsigned NOT NULL default '0',
  `path` varchar(255) NOT NULL default '',
  `extension` varchar(50) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL default '',
  `description` varchar(5120) NOT NULL default '',
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `params` varchar(2048) NOT NULL default '',
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL default '0',
  `created_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `modified_user_id` int(10) unsigned NOT NULL default '0',
  `modified_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `hits` int(10) unsigned NOT NULL default '0',
  `language` varchar(7) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`extension`,`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__categories` VALUES 
(1, 0, 0, 0, 17, 0, '', 'system', 'ROOT', 'root', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-22 20:25:13', 0, '0000-00-00 00:00:00', 0, '');

# -------------------------------------------------------

#
# Table structure for table `#__components`
#

CREATE TABLE `#__components` (
  `id` integer NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `menuid` integer unsigned NOT NULL default '0',
  `parent` integer unsigned NOT NULL default '0',
  `admin_menu_link` varchar(255) NOT NULL default '',
  `admin_menu_alt` varchar(255) NOT NULL default '',
  `option` varchar(50) NOT NULL default '',
  `ordering` integer NOT NULL default '0',
  `admin_menu_img` varchar(255) NOT NULL default '',
  `iscore` tinyint(4) NOT NULL default '0',
  `params` text NOT NULL,
  `enabled` tinyint(4) UNSIGNED NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `parent_option` (`parent`, `option`(32))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Dumping data for table `#__components`
#

INSERT INTO `#__components` VALUES (1, 'Banners', '', 0, 0, '', 'Banner Management', 'com_banners', 0, 'js/ThemeOffice/component.png', 0, 'track_impressions=0\ntrack_clicks=0\ntag_prefix=\n\n', 1);
INSERT INTO `#__components` VALUES (2, 'Banners', '', 0, 1, 'option=com_banners', 'Active Banners', 'com_banners', 1, 'js/ThemeOffice/edit.png', 0, '', 1);
INSERT INTO `#__components` VALUES (3, 'Clients', '', 0, 1, 'option=com_banners&c=client', 'Manage Clients', 'com_banners', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (4, 'Web Links', 'option=com_weblinks', 0, 0, '', 'Manage Weblinks', 'com_weblinks', 0, 'js/ThemeOffice/component.png', 0, 'show_comp_description=1\ncomp_description=\nshow_link_hits=1\nshow_link_description=1\nshow_other_cats=1\nshow_headings=1\nshow_page_title=1\nlink_target=0\nlink_icons=\n\n', 1);
INSERT INTO `#__components` VALUES (5, 'Links', '', 0, 4, 'option=com_weblinks', 'View existing weblinks', 'com_weblinks', 1, 'js/ThemeOffice/edit.png', 0, '', 1);
INSERT INTO `#__components` VALUES (6, 'Categories', '', 0, 4, 'option=com_categories&extension=com_weblinks', 'Manage weblink categories', '', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (7, 'Contacts', 'option=com_contact', 0, 0, '', 'Edit contact details', 'com_contact', 0, 'js/ThemeOffice/component.png', 1, 'contact_icons=0\nicon_address=\nicon_email=\nicon_telephone=\nicon_fax=\nicon_misc=\nshow_headings=1\nshow_position=1\nshow_email=0\nshow_telephone=1\nshow_mobile=1\nshow_fax=1\nbannedEmail=\nbannedSubject=\nbannedText=\nsession=1\ncustomReply=0\n\n', 1);
INSERT INTO `#__components` VALUES (8, 'Contacts', '', 0, 7, 'option=com_contact', 'Edit contact details', 'com_contact', 0, 'js/ThemeOffice/edit.png', 1, '', 1);
INSERT INTO `#__components` VALUES (9, 'Categories', '', 0, 7, 'option=com_categories&extension=com_contact', 'Manage contact categories', '', 2, 'js/ThemeOffice/categories.png', 1, 'contact_icons=0\nicon_address=\nicon_email=\nicon_telephone=\nicon_fax=\nicon_misc=\nshow_headings=1\nshow_position=1\nshow_email=0\nshow_telephone=1\nshow_mobile=1\nshow_fax=1\nbannedEmail=\nbannedSubject=\nbannedText=\nsession=1\ncustomReply=0\n\n', 1);
INSERT INTO `#__components` VALUES (11, 'News Feeds', 'option=com_newsfeeds', 0, 0, '', 'News Feeds Management', 'com_newsfeeds', 0, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (12, 'Feeds', '', 0, 11, 'option=com_newsfeeds', 'Manage News Feeds', 'com_newsfeeds', 1, 'js/ThemeOffice/edit.png', 0, 'show_headings=1\nshow_name=1\nshow_articles=1\nshow_link=1\nshow_cat_description=1\nshow_cat_items=1\nshow_feed_image=1\nshow_feed_description=1\nshow_item_description=1\nfeed_word_count=0\n\n', 1);
INSERT INTO `#__components` VALUES (13, 'Categories', '', 0, 11, 'option=com_categories&extension=com_newsfeeds', 'Manage Categories', '', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (14, 'User', 'option=com_user', 0, 0, '', '', 'com_user', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (15, 'Search', 'option=com_search', 0, 0, 'option=com_search', 'Search Statistics', 'com_search', 0, 'js/ThemeOffice/component.png', 1, 'enabled=0\n\n', 1);
INSERT INTO `#__components` VALUES (16, 'Categories', '', 0, 1, 'option=com_categories&extension=com_banner', 'Categories', '', 3, '', 1, '', 1);
INSERT INTO `#__components` VALUES (17, 'Wrapper', 'option=com_wrapper', 0, 0, '', 'Wrapper', 'com_wrapper', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (18, 'Mail To', '', 0, 0, '', '', 'com_mailto', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (19, 'Media Manager', '', 0, 0, 'option=com_media', 'Media Manager', 'com_media', 0, '', 1, 'upload_extensions=bmp,csv,doc,epg,gif,ico,jpg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,EPG,GIF,ICO,JPG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS\nupload_maxsize=10000000\nfile_path=images\nimage_path=images/stories\nrestrict_uploads=1\ncheck_mime=1\nimage_extensions=bmp,gif,jpg,png\nignore_extensions=\nupload_mime=image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip\nupload_mime_illegal=text/html', 1);
INSERT INTO `#__components` VALUES (20, 'Articles', 'option=com_content', 0, 0, 'option=com_content&view=articles', 'Articles', 'com_content', 0, '', 1, 'show_noauth=0\nshow_title=1\nlink_titles=0\nshow_intro=1\nshow_section=0\nlink_section=0\nshow_category=0\nlink_category=0\nshow_author=1\nshow_create_date=1\nshow_modify_date=1\nshow_item_navigation=0\nshow_readmore=1\nshow_vote=0\nshow_icons=1\nshow_pdf_icon=1\nshow_print_icon=1\nshow_email_icon=1\nshow_hits=1\nfeed_summary=0\n\n', 1);
INSERT INTO `#__components` VALUES (21, 'Configuration Manager', '', 0, 0, '', 'Configuration', 'com_config', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (22, 'Installation Manager', '', 0, 0, '', 'Installer', 'com_installer', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (23, 'Language Manager', '', 0, 0, '', 'Languages', 'com_languages', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (24, 'Mass mail', '', 0, 0, '', 'Mass Mail', 'com_massmail', 0, '', 1, 'mailSubjectPrefix=\nmailBodySuffix=\n\n', 1);
INSERT INTO `#__components` VALUES (25, 'Menu Editor', '', 0, 0, '', 'Menu Editor', 'com_menus', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (27, 'Messaging', '', 0, 0, '', 'Messages', 'com_messages', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (28, 'Module Manager', '', 0, 0, '', 'Modules', 'com_modules', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (29, 'Plug-in Manager', '', 0, 0, '', 'Plugins', 'com_plugins', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (30, 'Template Manager', '', 0, 0, '', 'Templates', 'com_templates', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (31, 'User Manager', '', 0, 0, '', 'Users', 'com_users', 0, '', 1, 'allowUserRegistration=1\nnew_usertype=Registered\nuseractivation=1\nfrontend_userparams=1\n\n', 1);
INSERT INTO `#__components` VALUES (32, 'Cache Manager', '', 0, 0, '', 'Cache', 'com_cache', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (33, 'Control Panel', '', 0, 0, '', 'Control Panel', 'com_cpanel', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES
 (35, 'Articles', '', 0, 20, 'option=com_content&view=articles', 'com_content_Articles', 'com_content', 1, '', 1, '{}', 1),
 (36, 'Categories', '', 0, 20, 'option=com_categories&view=categories&extension=com_content', 'com_content_Categories', 'com_content', 2, '', 1, '{}', 1),
 (37, 'Featured', '', 0, 20, 'option=com_content&view=featured', 'com_content_Featured', 'com_content', 3, '', 1, '{}', 1),
 (38, 'Redirects', '', 0, 0, 'option=com_redirect', 'Manage Redirects', 'com_redirect', 0, 'js/ThemeOffice/component.png', 1, '{}', 1),
 (39, 'Checkin', '', 0, 0, 'option=com_checkin', 'Checkin', 'com_checkin', 0, 'js/ThemeOffice/component.png', 1, '{}', 1);


# -------------------------------------------------------

#
# Table structure for table `#__contact_details`
#

CREATE TABLE `#__contact_details` (
  `id` integer NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `con_position` varchar(255) default NULL,
  `address` text,
  `suburb` varchar(100) default NULL,
  `state` varchar(100) default NULL,
  `country` varchar(100) default NULL,
  `postcode` varchar(100) default NULL,
  `telephone` varchar(255) default NULL,
  `fax` varchar(255) default NULL,
  `misc` mediumtext,
  `image` varchar(255) default NULL,
  `imagepos` varchar(20) default NULL,
  `email_to` varchar(255) default NULL,
  `default_con` tinyint(1) unsigned NOT NULL default '0',
  `published` tinyint(1) unsigned NOT NULL default '0',
  `checked_out` integer unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` integer NOT NULL default '0',
  `params` text NOT NULL,
  `user_id` integer NOT NULL default '0',
  `catid` integer NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `mobile` varchar(255) NOT NULL default '',
  `webpage` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__content`
#

CREATE TABLE `#__content` (
  `id` integer unsigned NOT NULL auto_increment,
  `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
  `title` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `title_alias` varchar(255) NOT NULL default '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL default '0',
  `sectionid` integer unsigned NOT NULL default '0',
  `mask` integer unsigned NOT NULL default '0',
  `catid` integer unsigned NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` integer unsigned NOT NULL default '0',
  `created_by_alias` varchar(255) NOT NULL default '',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified_by` integer unsigned NOT NULL default '0',
  `checked_out` integer unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL default '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `attribs` text NOT NULL,
  `version` integer unsigned NOT NULL default '1',
  `parentid` integer unsigned NOT NULL default '0',
  `ordering` integer NOT NULL default '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` integer unsigned NOT NULL default '0',
  `hits` integer unsigned NOT NULL default '0',
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL default '0' COMMENT 'Set if article is featured.',
  `language` varchar(10) NOT NULL COMMENT 'The language code for the article.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  PRIMARY KEY  (`id`),
  KEY `idx_section` (`sectionid`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__content_frontpage`
#

CREATE TABLE `#__content_frontpage` (
  `content_id` integer NOT NULL default '0',
  `ordering` integer NOT NULL default '0',
  PRIMARY KEY  (`content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__content_rating`
#

CREATE TABLE `#__content_rating` (
  `content_id` integer NOT NULL default '0',
  `rating_sum` integer unsigned NOT NULL default '0',
  `rating_count` integer unsigned NOT NULL default '0',
  `lastip` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__core_log_items`
#

CREATE TABLE `#__core_log_items` (
  `time_stamp` date NOT NULL default '0000-00-00',
  `item_table` varchar(50) NOT NULL default '',
  `item_id` integer unsigned NOT NULL default '0',
  `hits` integer unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__core_log_searches`
#

CREATE TABLE `#__core_log_searches` (
  `search_term` varchar(128) NOT NULL default '',
  `hits` integer unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# -------------------------------------------------------

#
# Table structure for table `#__extensions`
#

CREATE TABLE `#__extensions` (
  `extension_id` INT  NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100)  NOT NULL,
  `type` VARCHAR(20)  NOT NULL,
  `element` VARCHAR(100) NOT NULL,
  `folder` VARCHAR(100) NOT NULL,
  `client_id` TINYINT(3) NOT NULL,
  `enabled` TINYINT(3) NOT NULL DEFAULT '1',
  `access` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `protected` TINYINT(3) NOT NULL DEFAULT '0',
  `manifest_cache` TEXT  NOT NULL,
  `params` TEXT NOT NULL,
  `custom_data` text NOT NULL,
  `system_data` text NOT NULL,
  `checked_out` int(10) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) default '0',
  `state` int(11) default '0',
  PRIMARY KEY (`extension_id`),
  INDEX `element_clientid`(`element`, `client_id`),
  INDEX `element_folder_clientid`(`element`, `folder`, `client_id`),
  INDEX `extension`(`type`,`element`,`folder`,`client_id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# Components

INSERT INTO `#__extensions` VALUES 
(0, 'com_admin', 'component', 'com_admin', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'Banners', 'component', 'com_banners', '', 1, 1, 0, 0, '', 'track_impressions=0\ntrack_clicks=0\ntag_prefix=\n\n', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Cache Manager', 'component', 'com_cache', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'com_categories', 'component', 'com_categories', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'Configuration Manager', 'component', 'com_config', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Contacts', 'component', 'com_contact', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Articles', 'component', 'com_content', '', 1, 1, 0, 1, '', 'show_noauth=0\nshow_title=1\nlink_titles=0\nshow_intro=1\nshow_section=0\nlink_section=0\nshow_category=0\nlink_category=0\nshow_author=1\nshow_create_date=1\nshow_modify_date=1\nshow_item_navigation=0\nshow_readmore=1\nshow_vote=0\nshow_icons=1\nshow_pdf_icon=1\nshow_print_icon=1\nshow_email_icon=1\nshow_hits=1\nfeed_summary=0\n\n', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Control Panel', 'component', 'com_cpanel', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Installation Manager', 'component', 'com_installer', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Language Manager', 'component', 'com_languages', '', 1, 1, 0, 1, '', 'administrator=en-GB\nsite=en-GB', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'com_login', 'component', 'com_login', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'Mail To', 'component', 'com_mailto', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Mass Mail', 'component', 'com_massmail', '', 1, 1, 0, 1, '', 'mailSubjectPrefix=\nmailBodySuffix=\n\n', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Media Manager', 'component', 'com_media', '', 1, 1, 0, 1, '', 'upload_extensions=bmp,csv,doc,epg,gif,ico,jpg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,EPG,GIF,ICO,JPG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS\nupload_maxsize=10000000\nfile_path=images\nimage_path=images\nrestrict_uploads=1\ncheck_mime=1\nimage_extensions=bmp,gif,jpg,png\nignore_extensions=\nupload_mime=image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave\nflash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip\nupload_mime_illegal=text/html\nenable_flash=1\n\n', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Member Manager', 'component', 'com_members', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Menu Editor', 'component', 'com_menus', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Messaging', 'component', 'com_messages', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Module Manager', 'component', 'com_modules', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'News Feeds', 'component', 'com_newsfeeds', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Plug-in Manager', 'component', 'com_plugins', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Redirect Manager', 'component', 'com_redirect', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Search', 'component', 'com_search', '', 1, 1, 0, 1, '', 'enabled=0\n\n', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Template Manager', 'component', 'com_templates', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'User', 'component', 'com_user', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'User Manager', 'component', 'com_users', '', 1, 1, 0, 1, '', 'allowUserRegistration=1\nnew_usertype=Registered\nuseractivation=1\nfrontend_userparams=1\n\n', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Web Links', 'component', 'com_weblinks', '', 1, 1, 0, 0, '', 'show_comp_description=1\ncomp_description=\nshow_link_hits=1\nshow_link_description=1\nshow_other_cats=1\nshow_headings=1\nshow_page_title=1\nlink_target=0\nlink_icons=\n\n', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Wrapper', 'component', 'com_wrapper', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

# Languages

INSERT INTO `#__extensions` VALUES 
(0, 'en-GB', 'language', 'en-GB', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'en-GB', 'language', 'en-GB', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1);

# Libraries

INSERT INTO `#__extensions` VALUES 
(0, 'phpmailer', 'library', 'phpmailer', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'phpxmlrpc', 'library', 'phpxmlrpc', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'simplepie', 'library', 'simplepie', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1);

# Modules

INSERT INTO `#__extensions` VALUES 
(0, 'mod_archive', 'module', 'mod_archive', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_custom', 'module', 'mod_custom', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_feed', 'module', 'mod_feed', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_footer', 'module', 'mod_footer', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_latest', 'module', 'mod_latest', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_logged', 'module', 'mod_logged', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_login', 'module', 'mod_login', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_menu', 'module', 'mod_menu', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_newsflash', 'module', 'mod_newsflash', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_online', 'module', 'mod_online', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_popular', 'module', 'mod_popular', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_quickicon', 'module', 'mod_quickicon', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_random_image', 'module', 'mod_random_image', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_related_items', 'module', 'mod_related_items', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_search', 'module', 'mod_search', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_stats', 'module', 'mod_stats', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_status', 'module', 'mod_status', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_syndicate', 'module', 'mod_syndicate', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_title', 'module', 'mod_title', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_toolbar', 'module', 'mod_toolbar', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_unread', 'module', 'mod_unread', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1);

# Plugins

INSERT INTO `#__extensions` VALUES 
(0, 'System - Cache', 'plugin', 'cache', 'system', 0, 0, 1, 0, '', '{"browsercache":"0","cachetime":"15"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Search - Categories', 'plugin', 'categories', 'search', 1, 1, 0, 0, '', '{"search_limit":"50"}', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(0, 'Editor - CodeMirror', 'plugin', 'codemirror', 'editors', 1, 1, 1, 1, '', '{"linenumbers":"0"}', '', '', 0, '0000-00-00 00:00:00', 7, 0),
(0, 'Search - Contact', 'plugin', 'contact', 'search', 0, 1, 1, 0, '', '{"search_limit":"50"}', '', '', 0, '0000-00-00 00:00:00', 7, 0),
(0, 'Contacts', 'plugin', 'contacts', 'search', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'Search - Content', 'plugin', 'content', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_uncategorised":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(0, 'System - Debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, '', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"2","language_prefix":"(Mod_[^_]*)"}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(0, 'Content - Email Cloaking', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, '', '{"mode":"1"}', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(0, 'Content - Code Hightlighter (GeSHi)', 'plugin', 'geshi', 'content', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(0, 'Authentication - GMail', 'plugin', 'gmail', 'authentication', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(0, 'Editor Button - Image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Authentication - Joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(0, 'User - Joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, '', '{"autoregister":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Authentication - LDAP', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, '', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"","password":"","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(0, 'Content - Load Module', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, '', '{"enabled":"1","style":"table"}', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(0, 'System - Log', 'plugin', 'log', 'system', 0, 0, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(0, 'Search - Newsfeeds', 'plugin', 'newsfeeds', 'search', 0, 1, 1, 0, '', '{"search_limit":"50"}', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(0, 'Editor - No Editor', 'plugin', 'none', 'editors', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Authentication - OpenID', 'plugin', 'openid', 'authentication', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(0, 'Content - Pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 0, '', '{"enabled":"1","title":"1","multipage_toc":"1","showall":"1"}', '', '', 0, '0000-00-00 00:00:00', 10000, 0),
(0, 'Editor Button - Pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Content - Page Navigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 0, '', '{"position":"1"}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(0, 'User - Profile', 'plugin', 'profile', 'user', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Editor Button - Readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'System - Redirect', 'plugin', 'redirect', 'system', 0, 0, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 7, 0),
(0, 'System - Remember Me', 'plugin', 'remember', 'system', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(0, 'Search - Sections', 'plugin', 'sections', 'search', 0, 1, 1, 0, '', '{"search_limit":"50"}', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(0, 'System - SEF', 'plugin', 'sef', 'system', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(0, 'Editor - TinyMCE 3.2', 'plugin', 'tinymce', 'editors', 0, 1, 1, 0, '', '{"theme":"advanced","cleanup_startup":"0","cleanup_save":"2","cleanup_entities":"1","autosave":"1","compressed":"0","relative_urls":"0","text_direction":"ltr","lang_mode":"0","lang_code":"en","invalid_elements":"applet","content_css":"1","content_css_custom":"","newlines":"0","extended_elements":"","toolbar":"top","hr":"1","smilies":"1","table":"1","style":"1","layer":"1","xhtmlxtras":"0","template":"0","directionality":"1","fullscreen":"1","html_height":"550","html_width":"750","preview":"1","element_path":"0","insertdate":"1","format_date":"%Y-%m-%d","inserttime":"1","format_time":"%H:%M:%S"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'Content - Rating', 'plugin', 'vote', 'content', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(0, 'Search - Weblinks', 'plugin', 'weblinks', 'search', 0, 1, 1, 0, '', '{"search_limit":"50"}', '', '', 0, '0000-00-00 00:00:00', 2, 0);

# Templates

INSERT INTO `#__extensions` VALUES 
(0, 'beez', 'template', 'beez', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'bluestork', 'template', 'bluestork', '', 1, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1),
(0, 'rhuk_milkyway', 'template', 'rhuk_milkyway', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, -1);

# -------------------------------------------------------

#
# Table structure for table `#__languages`
#

CREATE TABLE `#__languages` (
  `lang_id` int(11) unsigned NOT NULL auto_increment,
  `lang_code` char(7) NOT NULL,
  `title` varchar(50) NOT NULL,
  `title_native` varchar(50) NOT NULL,
  `description` varchar(512) NOT NULL,
  `published` int(11) NOT NULL default '0',
  PRIMARY KEY  (`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Table structure for table `#__menu`
#

CREATE TABLE `#__menu` (
  `id` integer NOT NULL auto_increment,
  `menutype` varchar(24) NOT NULL COMMENT 'The type of menu this item belongs to. FK to #__menu_types.menutype',
  `title` varchar(255) NOT NULL COMMENT 'The display title of the menu item.',
  `alias` varchar(255) NOT NULL COMMENT 'The SEF alias of the menu item.',
  `path` varchar(1024) NOT NULL COMMENT 'The computed path of the menu item based on the alias field.',
  `link` varchar(1024) NOT NULL COMMENT 'The actually link the menu item refers to.',
  `type` varchar(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator',
  `published` tinyint(4) NOT NULL default '0' COMMENT 'The published state of the menu link.',
  `parent_id` integer unsigned NOT NULL default '0' COMMENT 'The parent menu item in the menu tree.',
  `level` integer unsigned NOT NULL default '0' COMMENT 'The relative level in the tree.',
  `component_id` integer unsigned NOT NULL default '0' COMMENT 'FK to #__components.id',
  `ordering` integer NOT NULL default '0' COMMENT 'The relative ordering of the menu item in the tree.',
  `checked_out` integer unsigned NOT NULL default '0' COMMENT 'FK to #__users.id',
  `checked_out_time` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'The time the menu item was checked out.',
  `browserNav` tinyint(4) NOT NULL default '0' COMMENT 'The click behaviour of the link.',
  `access` tinyint(3) unsigned NOT NULL default '0' COMMENT 'The access level required to view the menu item.',
  `template_id` integer default '0',
  `params` varchar(10240) NOT NULL COMMENT 'JSON encoded data for the menu item.',
  `lft` integer NOT NULL default '0' COMMENT 'Nested set lft.',
  `rgt` integer NOT NULL default '0' COMMENT 'Nested set rgt.',
  `home` tinyint(3) unsigned NOT NULL default '0' COMMENT 'Indicates if this menu item is the home or default page.',
  PRIMARY KEY  (`id`),
  KEY `idx_componentid` (`component_id`,`menutype`,`published`,`access`),
  KEY `idx_menutype` (`menutype`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_path` (`path`(333))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


INSERT INTO `#__menu` VALUES 
(1, '', 'Menu_Item_Root', 'root', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, 0, 'show_page_title=1\npage_title=Welcome to the Frontpage\nshow_description=0\nshow_description_image=0\nnum_leading_articles=1\nnum_intro_articles=4\nnum_columns=2\nnum_links=4\nshow_title=1\npageclass_sfx=\nmenu_image=-1\nsecure=0\norderby_pri=\norderby_sec=front\nshow_pagination=2\nshow_pagination_results=1\nshow_noauth=0\nlink_titles=0\nshow_intro=1\nshow_section=0\nlink_section=0\nshow_category=0\nlink_category=0\nshow_author=1\nshow_create_date=1\nshow_modify_date=1\nshow_item_navigation=0\nshow_readmore=1\nshow_vote=0\nshow_icons=1\nshow_pdf_icon=1\nshow_print_icon=1\nshow_email_icon=1\nshow_hits=1\n\n', 0, 17, 0),
(2, 'mainmenu', 'Home', 'home', 'home', 'index.php?option=com_content&view=frontpage', 'component', 1, 1, 1, 20, 0, 0, '0000-00-00 00:00:00', 0, 1, 0, 'show_page_title=1\r\npage_title=Welcome to the Frontpage\r\nshow_description=0\r\nshow_description_image=0\r\nnum_leading_articles=1\r\nnum_intro_articles=4\r\nnum_columns=2\r\nnum_links=4\r\nshow_title=1\r\npageclass_sfx=\r\nmenu_image=-1\r\nsecure=0\r\norderby_pri=\r\norderby_sec=front\r\nshow_pagination=2\r\nshow_pagination_results=1\r\nshow_noauth=0\r\nlink_titles=0\r\nshow_intro=1\r\nshow_section=0\r\nlink_section=0\r\nshow_category=0\r\nlink_category=0\r\nshow_author=1\r\nshow_create_date=1\r\nshow_modify_date=1\r\nshow_item_navigation=0\r\nshow_readmore=1\r\nshow_vote=0\r\nshow_icons=1\r\nshow_pdf_icon=1\r\nshow_print_icon=1\r\nshow_email_icon=1\r\nshow_hits=1\r\n\r\n', 1, 2, 1);

# -------------------------------------------------------

#
# Table structure for table `#__menu_template`
#

CREATE TABLE IF NOT EXISTS `#__menu_template` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `template` varchar(255) NOT NULL,
  `client_id` integer NOT NULL,
  `home` tinyint(1) NOT NULL,
  `description` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

INSERT INTO `#__menu_template` VALUES (1, 'rhuk_milkyway', '0', '1', 'Default', '{"colorVariation":"blue","backgroundVariation":"blue","widthStyle":"fmax"}');
INSERT INTO `#__menu_template` VALUES (2, 'bluestork', '1', '1', 'Default', '{"useRoundedCorners":"1","showSiteName":"0"}');

# -------------------------------------------------------

#
# Table structure for table `#__menu_types`
#

CREATE TABLE `#__menu_types` (
  `id` integer unsigned NOT NULL auto_increment,
  `menutype` varchar(24) NOT NULL,
  `title` varchar(48) NOT NULL,
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__menu_types` VALUES (1, 'mainmenu', 'Main Menu', 'The main menu for the site');

# -------------------------------------------------------

#
# Table structure for table `#__messages`
#

CREATE TABLE `#__messages` (
  `message_id` integer unsigned NOT NULL auto_increment,
  `user_id_from` integer unsigned NOT NULL default '0',
  `user_id_to` integer unsigned NOT NULL default '0',
  `folder_id` integer unsigned NOT NULL default '0',
  `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `state` integer NOT NULL default '0',
  `priority` int(1) unsigned NOT NULL default '0',
  `subject` text NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`message_id`),
  KEY `useridto_state` (`user_id_to`, `state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
# -------------------------------------------------------

#
# Table structure for table `#__messages_cfg`
#

CREATE TABLE `#__messages_cfg` (
  `user_id` integer unsigned NOT NULL default '0',
  `cfg_name` varchar(100) NOT NULL default '',
  `cfg_value` varchar(255) NOT NULL default '',
  UNIQUE `idx_user_var_name` (`user_id`,`cfg_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
# -------------------------------------------------------

#
# Table structure for table `#__modules`
#

CREATE TABLE `#__modules` (
  `id` integer NOT NULL auto_increment,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `ordering` integer NOT NULL default '0',
  `position` varchar(50) default NULL,
  `checked_out` integer unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL default '0',
  `module` varchar(50) default NULL,
  `numnews` integer NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `showtitle` tinyint(3) unsigned NOT NULL default '1',
  `params` text NOT NULL,
  `iscore` tinyint(4) NOT NULL default '0',
  `client_id` tinyint(4) NOT NULL default '0',
  `control` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `published` (`published`,`access`),
  KEY `newsfeeds` (`module`,`published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__modules` VALUES (1, 'Main Menu', '', 1, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_menu', 0, 1, 1, 'menutype=mainmenu\nmoduleclass_sfx=_menu\n', 1, 0, '');
INSERT INTO `#__modules` VALUES (2, 'Login', '', 1, 'login', 0, '0000-00-00 00:00:00', 1, 'mod_login', 0, 1, 1, '', 1, 1, '');
INSERT INTO `#__modules` VALUES (3, 'Popular','',3,'cpanel',0,'0000-00-00 00:00:00',1,'mod_popular',0,3,1,'',0, 1, '');
INSERT INTO `#__modules` VALUES (4, 'Recently Added Articles','',4,'cpanel',0,'0000-00-00 00:00:00',1,'mod_latest',0,3,1,'ordering=c_dsc\nuser_id=0\ncache=0\n\n',0, 1, '');
INSERT INTO `#__modules` VALUES (6, 'Unread Messages','',1,'header',0,'0000-00-00 00:00:00',1,'mod_unread',0,3,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (7, 'Online Users','',2,'header',0,'0000-00-00 00:00:00',1,'mod_online',0,3,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (8, 'Toolbar','',1,'toolbar',0,'0000-00-00 00:00:00',1,'mod_toolbar',0,3,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (9, 'Quick Icons','',1,'icon',0,'0000-00-00 00:00:00',1,'mod_quickicon',0,3,1,'',1,1, '');
INSERT INTO `#__modules` VALUES (10, 'Logged-in Users','',2,'cpanel',0,'0000-00-00 00:00:00',1,'mod_logged',0,3,1,'',0,1, '');
INSERT INTO `#__modules` VALUES (12, 'Admin Menu','', 1,'menu', 0,'0000-00-00 00:00:00', 1,'mod_menu', 0, 3, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (13, 'Admin Submenu','', 1,'submenu', 0,'0000-00-00 00:00:00', 1,'mod_submenu', 0, 3, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (14, 'User Status','', 1,'status', 0,'0000-00-00 00:00:00', 1,'mod_status', 0, 3, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (15, 'Title','', 1,'title', 0,'0000-00-00 00:00:00', 1,'mod_title', 0, 3, 1, '', 0, 1, '');

INSERT INTO `#__modules` VALUES
(16, 'User Menu', '', 4, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_menu', 0, 2, 1, 'menutype=usermenu\nmoduleclass_sfx=_menu\ncache=1', 1, 0, ''),
(17, 'Login Form', '', 8, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_login', 0, 1, 1, 'greeting=1\nname=0', 1, 0, ''),
(18, 'Breadcrumbs', '', 1, 'breadcrumb', 0, '0000-00-00 00:00:00', 1, 'mod_breadcrumbs', 0, 1, 1, 'moduleclass_sfx=\ncache=0\nshowHome=1\nhomeText=Home\nshowComponent=1\nseparator=\n\n', 1, 0, '');

# -------------------------------------------------------

#
# Table structure for table `#__modules_menu`
#

CREATE TABLE `#__modules_menu` (
  `moduleid` integer NOT NULL default '0',
  `menuid` integer NOT NULL default '0',
  PRIMARY KEY  (`moduleid`,`menuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Dumping data for table `#__modules_menu`
#

INSERT INTO `#__modules_menu` VALUES
(1,0),
(2,0),
(3,0),
(4,0),
(5,0),
(6,0),
(7,0),
(8,0),
(9,0),
(10,0),
(11,0),
(12,0),
(13,0),
(14,0),
(15,0),
(16,0),
(17,0),
(18,0);

# -------------------------------------------------------

#
# Table structure for table `#__newsfeeds`
#

CREATE TABLE `#__newsfeeds` (
  `catid` integer NOT NULL default '0',
  `id` integer NOT NULL auto_increment,
  `name` text NOT NULL,
  `alias` varchar(255) NOT NULL default '',
  `link` text NOT NULL,
  `filename` varchar(200) default NULL,
  `published` tinyint(1) NOT NULL default '0',
  `numarticles` integer unsigned NOT NULL default '1',
  `cache_time` integer unsigned NOT NULL default '3600',
  `checked_out` tinyint(3) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` integer NOT NULL default '0',
  `rtl` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `published` (`published`),
  KEY `catid` (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__redirect_links`
#

CREATE TABLE `#__redirect_links` (
  `id` integer unsigned NOT NULL auto_increment,
  `old_url` varchar(150) NOT NULL,
  `new_url` varchar(150) NOT NULL,
  `referer` varchar(150) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `published` tinyint(4) NOT NULL,
  `created_date` integer NOT NULL,
  `updated_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_link_old` (`old_url`),
  KEY `idx_link_updated` (`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# -------------------------------------------------------

#
# Table structure for table `#__schemas`
#

CREATE TABLE `#__schemas` (
  `extensionid` int(11) NOT NULL,
  `versionid` varchar(20) NOT NULL,
  PRIMARY KEY (`extensionid`, `versionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__session`
#

CREATE TABLE `#__session` (
  `session_id` varchar(32) NOT NULL default '',
  `client_id` tinyint(3) unsigned NOT NULL default '0',
  `guest` tinyint(4) unsigned default '1',
  `time` varchar(14) default '',
  `data` varchar(20480) default NULL,
  `userid` int(11) default '0',
  `username` varchar(150) default '',
  `usertype` varchar(50) default '',
  PRIMARY KEY  (`session_id`),
  KEY `whosonline` (`guest`,`usertype`),
  KEY `userid` (`userid`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# -------------------------------------------------------

# Update Sites
CREATE TABLE  `#__updates` (
  `update_id` int(11) NOT NULL auto_increment,
  `update_site_id` int(11) default '0',
  `extension_id` int(11) default '0',
  `categoryid` int(11) default '0',
  `name` varchar(100) default '',
  `description` text NOT NULL,
  `element` varchar(100) default '',
  `type` varchar(20) default '',
  `folder` varchar(20) default '',
  `client_id` tinyint(3) default '0',
  `version` varchar(10) default '',
  `data` text NOT NULL,
  `detailsurl` text NOT NULL,
  PRIMARY KEY  (`update_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Available Updates';

CREATE TABLE  `#__update_sites` (
  `update_site_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default '',
  `type` varchar(20) default '',
  `location` text NOT NULL,
  `enabled` int(11) default '0',
  PRIMARY KEY  (`update_site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Sites';

CREATE TABLE `#__update_sites_extensions` (
  `update_site_id` INT DEFAULT 0,
  `extension_id` INT DEFAULT 0,
  INDEX `newindex`(`update_site_id`, `extension_id`)
) ENGINE = MYISAM CHARACTER SET utf8 COMMENT = 'Links extensions to update sites';

CREATE TABLE  `#__update_categories` (
  `categoryid` int(11) NOT NULL auto_increment,
  `name` varchar(20) default '',
  `description` text NOT NULL,
  `parent` int(11) default '0',
  `updatesite` int(11) default '0',
  PRIMARY KEY  (`categoryid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Categories';


# -------------------------------------------------------

#
# Table structure for table `#__stats_agents`
#

CREATE TABLE `#__stats_agents` (
  `agent` varchar(255) NOT NULL default '',
  `type` tinyint(1) unsigned NOT NULL default '0',
  `hits` integer unsigned NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__user_usergroup_map`
#

CREATE TABLE IF NOT EXISTS `#__user_usergroup_map` (
  `user_id` integer unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__users.id',
  `group_id` integer unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__usergroups.id',
  PRIMARY KEY  (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__usergroups`
#

CREATE TABLE IF NOT EXISTS `#__usergroups` (
  `id` integer unsigned NOT NULL auto_increment COMMENT 'Primary Key',
  `parent_id` integer unsigned NOT NULL default '0' COMMENT 'Adjacency List Reference Id',
  `lft` integer NOT NULL default '0' COMMENT 'Nested set lft.',
  `rgt` integer NOT NULL default '0' COMMENT 'Nested set rgt.',
  `title` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_usergroup_title_lookup` (`title`),
  KEY `idx_usergroup_adjacency_lookup` (`parent_id`),
  KEY `idx_usergroup_nested_set_lookup` USING BTREE (`lft`,`rgt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__usergroups` (`id` ,`parent_id` ,`lft` ,`rgt` ,`title`)
VALUES
	(1,0,1,18,'Public'),
	(2,1,2,17,'Registered'),
	(3,2,3,8,'Author'),
	(4,3,4,7,'Editor'),
	(5,4,5,6,'Publisher'),
	(6,2,9,14,'Manager'),
	(7,6,10,13,'Administrator'),
	(8,7,11,12,'Super Administrator');

# -------------------------------------------------------

#
# Table structure for table `#__users`
#

CREATE TABLE `#__users` (
  `id` integer NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `username` varchar(150) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `usertype` varchar(25) NOT NULL default '',
  `block` tinyint(4) NOT NULL default '0',
  `sendEmail` tinyint(4) default '0',
  `registerDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL default '',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `usertype` (`usertype`),
  KEY `idx_name` (`name`),
  KEY `idx_block` (`block`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__user_profiles` (
  `user_id` int(11) NOT NULL,
  `profile_key` varchar(100) NOT NULL,
  `profile_value` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL default '0',
  UNIQUE KEY `idx_user_id_profile_key` (`user_id`,`profile_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Simple user profile storage table';

#
# Table structure for table `#__weblinks`
#

CREATE TABLE `#__weblinks` (
  `id` integer unsigned NOT NULL auto_increment,
  `catid` integer NOT NULL default '0',
  `sid` integer NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `url` varchar(250) NOT NULL default '',
  `description` TEXT NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `hits` integer NOT NULL default '0',
  `state` tinyint(1) NOT NULL default '0',
  `checked_out` integer NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` integer NOT NULL default '0',
  `archived` tinyint(1) NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '1',
  `access` integer NOT NULL default '1',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`,`state`,`archived`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# -------------------------------------------------------

#
# Table structure for table `#__viewlevels`
#

CREATE TABLE IF NOT EXISTS `#__viewlevels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `title` varchar(100) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_assetgroup_title_lookup` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

#
# Dumping data for table `#__viewlevels`
#

INSERT INTO `#__viewlevels` (`id`, `title`, `ordering`, `rules`) VALUES
(1, 'Public', 0, '[]'),
(2, 'Registered', 1, '[2]'),
(3, 'Special', 2, '["6","7","8"]');

# -------------------------------------------------------

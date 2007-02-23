# $Id: joomla_backward.sql 6311 2007-01-18 23:18:53Z hackwar $

# --------------------------------------------------------

#
# Table structure for table `#__banner`
#

CREATE TABLE `#__banner` (
  `bid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `type` varchar(90) NOT NULL default 'banner',
  `name` TEXT NOT NULL default '',
  `imptotal` int(11) NOT NULL default '0',
  `impmade` int(11) NOT NULL default '0',
  `clicks` int(11) NOT NULL default '0',
  `imageurl` varchar(100) NOT NULL default '',
  `clickurl` varchar(200) NOT NULL default '',
  `date` datetime default NULL,
  `showBanner` tinyint(1) NOT NULL default '0',
  `checked_out` tinyint(1) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `editor` varchar(150) default NULL,
  `custombannercode` text,
  `catid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT NOT NULL DEFAULT '',
  `sticky` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `ordering` INTEGER NOT NULL DEFAULT 0,
  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL default '0000-00-00 00:00:00',
  `tags` TEXT NOT NULL DEFAULT '',
  `params` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY  (`bid`),
  KEY `viewbanner` (`showBanner`),
  INDEX `idx_banner_catid`(`catid`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__bannerclient`
#

CREATE TABLE `#__bannerclient` (
  `cid` int(11) NOT NULL auto_increment,
  `name` TEXT NOT NULL default '',
  `contact` TEXT NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `extrainfo` text NOT NULL,
  `checked_out` tinyint(1) NOT NULL default '0',
  `checked_out_time` time default NULL,
  `editor` varchar(150) default NULL,
  PRIMARY KEY  (`cid`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__bannertrack`
#

CREATE TABLE  `#__bannertrack` (
  `track_date` date NOT NULL,
  `track_type` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__categories`
#

CREATE TABLE `#__categories` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default 0,
  `title` TEXT NOT NULL default '',
  `name` TEXT NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `section` varchar(150) NOT NULL default '',
  `image_position` varchar(90) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `editor` varchar(150) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`section`,`published`,`access`),
  KEY `idx_section` (`section`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`)
) TYPE=MyISAM ;

# --------------------------------------------------------

#
# Table structure for table `#__components`
#

CREATE TABLE `#__components` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `menuid` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned NOT NULL default '0',
  `admin_menu_link` varchar(255) NOT NULL default '',
  `admin_menu_alt` TEXT NOT NULL default '',
  `option` varchar(50) NOT NULL default '',
  `ordering` int(11) NOT NULL default '0',
  `admin_menu_img` varchar(255) NOT NULL default '',
  `iscore` tinyint(4) NOT NULL default '0',
  `params` text NOT NULL,
  `enabled` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `#__components`
#

INSERT INTO `#__components` VALUES (1, 'Banners', '', 0, 0, '', 'Banner Management', 'com_banners', 0, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (2, 'Banners', '', 0, 1, 'option=com_banners', 'Active Banners', 'com_banners', 1, 'js/ThemeOffice/edit.png', 0, '', 1);
INSERT INTO `#__components` VALUES (3, 'Clients', '', 0, 1, 'option=com_banners&c=client', 'Manage Clients', 'com_banners', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (4, 'Web Links', 'option=com_weblinks', 0, 0, '', 'Manage Weblinks', 'com_weblinks', 0, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (5, 'Links', '', 0, 4, 'option=com_weblinks', 'View existing weblinks', 'com_weblinks', 1, 'js/ThemeOffice/edit.png', 0, '', 1);
INSERT INTO `#__components` VALUES (6, 'Categories', '', 0, 4, 'option=com_categories&section=com_weblinks', 'Manage weblink categories', '', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (7, 'Contacts', 'option=com_contact', 0, 0, '', 'Edit contact details', 'com_contact', 0, 'js/ThemeOffice/component.png', 1, '', 1);
INSERT INTO `#__components` VALUES (8, 'Contacts', '', 0, 7, 'option=com_contact', 'Edit contact details', 'com_contact', 0, 'js/ThemeOffice/edit.png', 1, '', 1);
INSERT INTO `#__components` VALUES (9, 'Categories', '', 0, 7, 'option=com_categories&section=com_contact_details', 'Manage contact categories', '', 2, 'js/ThemeOffice/categories.png', 1, '', 1);
INSERT INTO `#__components` VALUES (10, 'Polls', 'option=com_poll', 0, 0, 'option=com_poll', 'Manage Polls', 'com_poll', 0, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (11, 'News Feeds', 'option=com_newsfeeds', 0, 0, '', 'News Feeds Management', 'com_newsfeeds', 0, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (12, 'Feeds', '', 0, 11, 'option=com_newsfeeds', 'Manage News Feeds', 'com_newsfeeds', 1, 'js/ThemeOffice/edit.png', 0, '', 1);
INSERT INTO `#__components` VALUES (13, 'Categories', '', 0, 11, 'option=com_categories&section=com_newsfeeds', 'Manage Categories', '', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (14, 'User', 'option=com_user', 0, 0, '', '', 'com_user', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (15, 'Search', 'option=com_search', 0, 0, 'option=com_search', 'Search Statistics', 'com_search', 0, 'js/ThemeOffice/component.png', 1, '', 1);
INSERT INTO `#__components` VALUES (16, 'Categories','',0,1,'option=com_categories&section=com_banner', 'Categories','',3,'',1,'',1);
INSERT INTO `#__components` VALUES (17,'Wrapper','option=com_wrapper',0,0,'','Wrapper','com_wrapper',0,'',1,'',1);
INSERT INTO `#__components` VALUES (18,'Mail To','',0,0,'','','com_mailto',0,'',1,'',1);
INSERT INTO `#__components` VALUES (19,'Media Manager','',0,0,'option=com_media','Media Manager','com_media',0,'',1,'upload_extensions=bmp,csv,doc,epg,gif,ico,jpg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,EPG,GIF,ICO,JPG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS\nupload_maxsize=10000000\n\n',1);
INSERT INTO `#__components` VALUES (20, 'Articles', 'option=com_content', 0, 0, '', '', 'com_content', 0, '', 1, 'shownoauth=0\nlink_titles=0\nreadmore=1\nvote=0\nshowAuthor=1\nshowCreateDate=1\nshowModifyDate=1\nshowItemNavigation=0\nicons=1\nshowPdf=1\nshowPrint=1\nshowEmail=1\nhits=1\n\n', 1);
INSERT INTO `#__components` VALUES (21,'Configuration Manager','',0,0,'','Configuration','com_config',0,'',1,'',1);
INSERT INTO `#__components` VALUES (22,'Installation Manager','',0,0,'','Installer','com_installer',0,'',1,'',1);
INSERT INTO `#__components` VALUES (23,'Lanuage Manager','',0,0,'','Lanaguages','com_languages',0,'',1,'',1);
INSERT INTO `#__components` VALUES (24,'Mass mail','',0,0,'','Mass Mail','com_massmail',0,'',1,'',1);
INSERT INTO `#__components` VALUES (25,'Menu Editor','',0,0,'','Menu Editor','com_menus',0,'',1,'',1);
INSERT INTO `#__components` VALUES (26,'Menu Manager','',0,0,'','Menu Manager','com_menumanager',0,'',1,'',1);
INSERT INTO `#__components` VALUES (27,'Messaging','',0,0,'','Messages','com_messages',0,'',1,'',1);
INSERT INTO `#__components` VALUES (28,'Modules Manager','',0,0,'','Modules','com_modules',0,'',1,'',1);
INSERT INTO `#__components` VALUES (29,'Plugin Manager','',0,0,'','Plugins','com_plugins',0,'',1,'',1);
INSERT INTO `#__components` VALUES (31,'Template Manager','',0,0,'','Templates','com_templates',0,'',1,'',1);
INSERT INTO `#__components` VALUES (32,'User Manager','',0,0,'','Users','com_users',0,'',1,'allowUserRegistration=1\nnew_usertype=Registered\nuseractivation=1\nfrontend_userparams=1\n\n',1);
INSERT INTO `#__components` VALUES (33,'Cache Manager','',0,0,'','Cache','com_cache',0,'',1,'',1);
INSERT INTO `#__components` VALUES (34,'Control Panel','',0,0,'','Control Panel','com_cpanel',0,'',1,'',1);

# --------------------------------------------------------

#
# Table structure for table `#__contact_details`
#

CREATE TABLE `#__contact_details` (
  `id` int(11) NOT NULL auto_increment,
  `name` TEXT NOT NULL default '',
  `con_position` TEXT default NULL,
  `address` text,
  `suburb` TEXT default NULL,
  `state` TEXT default NULL,
  `country` TEXT default NULL,
  `postcode` varchar(255) default NULL,
  `telephone` varchar(255) default NULL,
  `fax` varchar(255) default NULL,
  `misc` mediumtext,
  `image` varchar(255) default NULL,
  `imagepos` varchar(60) default NULL,
  `email_to` varchar(255) default NULL,
  `default_con` tinyint(1) unsigned NOT NULL default '0',
  `published` tinyint(1) unsigned NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `params` text NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  `catid` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `mobile` varchar(255) NOT NULL default '',
  `webpage` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__content`
#

CREATE TABLE `#__content` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` TEXT NOT NULL default '',
  `title_alias` TEXT NOT NULL default '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL default '0',
  `sectionid` int(11) unsigned NOT NULL default '0',
  `mask` int(11) unsigned NOT NULL default '0',
  `catid` int(11) unsigned NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL default '0',
  `created_by_alias` TEXT NOT NULL default '',
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
  `ordering` int(11) NOT NULL default '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` int(11) unsigned NOT NULL default '0',
  `hits` int(11) unsigned NOT NULL default '0',
  `metadata` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `idx_section` (`sectionid`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_mask` (`mask`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__content_frontpage`
#

CREATE TABLE `#__content_frontpage` (
  `content_id` int(11) NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  PRIMARY KEY  (`content_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__content_rating`
#

CREATE TABLE `#__content_rating` (
  `content_id` int(11) NOT NULL default '0',
  `rating_sum` int(11) unsigned NOT NULL default '0',
  `rating_count` int(11) unsigned NOT NULL default '0',
  `lastip` varchar(150) NOT NULL default '',
  PRIMARY KEY  (`content_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

# Table structure for table `#__core_log_items`
#
# To be implemented in Version 4.6

CREATE TABLE `#__core_log_items` (
  `time_stamp` date NOT NULL default '0000-00-00',
  `item_table` varchar(50) NOT NULL default '',
  `item_id` int(11) unsigned NOT NULL default '0',
  `hits` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;

# --------------------------------------------------------

# Table structure for table `#__core_log_searches`
#
# To be implemented in Version 4.6

CREATE TABLE `#__core_log_searches` (
  `search_term` TEXT NOT NULL default '',
  `hits` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;

#
# Table structure for table `#__groups`
#

# --------------------------------------------------------

CREATE TABLE `#__groups` (
  `id` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(150) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `#__groups`
#

INSERT INTO `#__groups` VALUES (0, 'Public');
INSERT INTO `#__groups` VALUES (1, 'Registered');
INSERT INTO `#__groups` VALUES (2, 'Special');

# --------------------------------------------------------

#
# Table structure for table `#__plugins`
#

CREATE TABLE `#__plugins` (
  `id` int(11) NOT NULL auto_increment,
  `name` TEXT NOT NULL default '',
  `element` TEXT NOT NULL default '',
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

INSERT INTO `#__plugins` VALUES (2, 'Content - Pagebreak','pagebreak','content',0,10000,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (4, 'Content - SEF','sef','content',0,3,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (5, 'Content - Rating','vote','content',0,4,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (6, 'Search - Content','content','search',0,1,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (7, 'Search - Weblinks','weblinks','search',0,2,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (8, 'Content - Code Highlighter (Joomla)','code','content',0,2,0,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (9, 'Editor - No Editor','none','editors',0,0,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `jos_plugins` VALUES (10, 'Editor - TinyMCE 2.0', 'tinymce', 'editors', 0, 0, 1, 1, 0, 0, '0000-00-00 00:00:00', 'theme=advanced\ncleanup=1\ncleanup_startup=0\nautosave=0\ncompressed=0\nrelative_urls=1\ntext_direction=ltr\nlang_mode=0\nlang_code=en\ninvalid_elements=applet\ncontent_css=1\ncontent_css_custom=\nnewlines=0\ntoolbar=top\nhr=1\nsmilies=1\ntable=1\nstyle=1\nlayer=1\nxhtmlxtras=0\ntemplate=0\ndirectionality=1\nfullscreen=1\nhtml_height=550\nhtml_width=750\npreview=1\ninsertdate=1\nformat_date=%Y-%m-%d\ninserttime=1\nformat_time=%H:%M:%S\n\n');
INSERT INTO `#__plugins` VALUES (11, 'Editor Button - Image','image','editors-xtd',0,0,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (12, 'Editor Button - Pagebreak','pagebreak','editors-xtd',0,0,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (13, 'Search - Contacts','contacts','search',0,3,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (14, 'Search - Categories', 'categories', 'search', 0, 4, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (15, 'Search - Sections', 'sections', 'search', 0, 5, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (16, 'Content - Email Cloaking', 'emailcloak', 'content', 0, 5, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (17, 'Content - Code Hightlighter (GeSHi)', 'geshi', 'content', 0, 5, 0, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (18, 'Search - Newsfeeds', 'newsfeeds', 'search', 0, 6, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (19, 'Content - Load Module', 'loadmodule', 'content', 0, 6, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (20, 'Authentication - Joomla', 'joomla', 'authentication', 0, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (21, 'Authentication - LDAP', 'ldap', 'authentication', 0, 2, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (22, 'Authentication - GMail', 'gmail', 'authentication', 0, 4, 0, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (25, 'Content - Page Navigation','pagenavigation','content',0,2,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (26, 'Editor - XStandard Lite 1.7', 'xstandard', 'editors', 0, 0, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (27, 'XML-RPC - Joomla', 'joomla', 'xmlrpc', 0, 7, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (28, 'XML-RPC - Blogger API', 'blogger', 'xmlrpc', 0, 7, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
#INSERT INTO `#__plugins` VALUES (29, 'XML-RPC - MetaWeblog API', 'metaweblog', 'xmlrpc', 0, 7, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (30, 'Editor Button - Readmore','readmore','editors-xtd',0,0,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (31, 'User - Joomla!', 'joomla', 'user', 0, 0, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (32, 'Authentication - OpenID', 'openid', 'authentication', 0, 3, 0, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (33, 'System - Debug', 'debug', 'system', 0, 1, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (34, 'System - Legacy', 'legacy', 'system', 0, 2, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (35, 'System - Cache', 'cache', 'system', 0, 3, 0, 1, 0, 0, '0000-00-00 00:00:00', '');

# --------------------------------------------------------

#
# Table structure for table `#__menu`
#

CREATE TABLE `#__menu` (
  `id` int(11) NOT NULL auto_increment,
  `menutype` varchar(225) default NULL,
  `name` TEXT default NULL,
  `link` text,
  `type` varchar(150) NOT NULL default '',
  `published` tinyint(1) NOT NULL default 0,
  `parent` int(11) unsigned NOT NULL default 0,
  `componentid` int(11) unsigned NOT NULL default 0,
  `sublevel` int(11) default 0,
  `ordering` int(11) default 0,
  `checked_out` int(11) unsigned NOT NULL default 0,
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `pollid` int(11) NOT NULL default 0,
  `browserNav` tinyint(4) default 0,
  `access` tinyint(3) unsigned NOT NULL default 0,
  `utaccess` tinyint(3) unsigned NOT NULL default 0,
  `params` text NOT NULL,
  `lft` int(11) unsigned NOT NULL default 0,
  `rgt` int(11) unsigned NOT NULL default 0,
  `home` INTEGER(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`),
  KEY `componentid` (`componentid`,`menutype`,`published`,`access`),
  KEY `menutype` (`menutype`)
) TYPE=MyISAM;

INSERT INTO `#__menu` VALUES (1, 'mainmenu', 'Home', 'index.php?option=com_content&view=frontpage', 'component', 1, 0, 20, 0, 1, 0, '0000-00-00 00:00:00', 0, 0, 0, 3, 'leading=1\r\nintro=2\r\nlink=1\r\nimage=1\r\npage_title=0\r\nheader=Welcome to the Frontpage\r\norderby_sec=front\r\nprint=0\r\npdf=0\r\nemail=0\r\nback_button=0', 0, 0, 1);

# --------------------------------------------------------

#
# Table structure for table `#__menu_types`
#

CREATE TABLE `#__menu_types` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `menutype` VARCHAR(225) NOT NULL DEFAULT '',
  `title` TEXT NOT NULL DEFAULT '',
  `description` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY(`id`),
  UNIQUE `menutype`(`menutype`)
) TYPE=MyISAM;

INSERT INTO `#__menu_types` VALUES (1, 'mainmenu', 'Main Menu', 'The main menu for the site');

# --------------------------------------------------------

#
# Table structure for table `#__messages`
#

CREATE TABLE `#__messages` (
  `message_id` int(10) unsigned NOT NULL auto_increment,
  `user_id_from` int(10) unsigned NOT NULL default '0',
  `user_id_to` int(10) unsigned NOT NULL default '0',
  `folder_id` int(10) unsigned NOT NULL default '0',
  `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `state` int(11) NOT NULL default '0',
  `priority` int(1) unsigned NOT NULL default '0',
  `subject` text NOT NULL default '',
  `message` text NOT NULL,
  PRIMARY KEY  (`message_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `#__messages_cfg`
#

CREATE TABLE `#__messages_cfg` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `cfg_name` TEXT NOT NULL default '',
  `cfg_value` TEXT NOT NULL default '',
  UNIQUE `idx_user_var_name` (`user_id`,`cfg_name`(100))
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `#__modules`
#

CREATE TABLE `#__modules` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `ordering` int(11) NOT NULL default '0',
  `position` varchar(150) default NULL,
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL default '0',
  `module` varchar(150) default NULL,
  `numnews` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `showtitle` tinyint(3) unsigned NOT NULL default '1',
  `params` text NOT NULL,
  `iscore` tinyint(4) NOT NULL default '0',
  `client_id` tinyint(4) NOT NULL default '0',
  `control` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `published` (`published`,`access`),
  KEY `newsfeeds` (`module`,`published`)
) TYPE=MyISAM;

#
# Dumping data for table `#__modules`
#

INSERT INTO `#__modules` VALUES (3, 'Main Menu', '', 1, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_mainmenu', 0, 0, 1, 'menutype=mainmenu\nmoduleclass_sfx=_menu\n', 1, 0, '');
INSERT INTO `#__modules` VALUES (9, 'Login', '', 1, 'login', 0, '0000-00-00 00:00:00', 1, 'mod_login', 0, 0, 1, '', 1, 1, '');
INSERT INTO `#__modules` VALUES (18, 'Popular','',3,'cpanel',0,'0000-00-00 00:00:00',1,'mod_popular',0,2,1,'',0, 1, '');
INSERT INTO `#__modules` VALUES (19, 'Recent added Articles','',4,'cpanel',0,'0000-00-00 00:00:00',1,'mod_latest',0,2,1,'',0, 1, '');
INSERT INTO `#__modules` VALUES (20, 'Menu Stats','',5,'cpanel',0,'0000-00-00 00:00:00',1,'mod_stats',0,2,1,'',0, 1, '');
INSERT INTO `#__modules` VALUES (21, 'Unread Messages','',1,'header',0,'0000-00-00 00:00:00',1,'mod_unread',0,2,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (22, 'Online Users','',2,'header',0,'0000-00-00 00:00:00',1,'mod_online',0,2,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (23, 'Toolbar','',1,'toolbar',0,'0000-00-00 00:00:00',1,'mod_toolbar',0,2,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (24, 'Quick Icons','',1,'icon',0,'0000-00-00 00:00:00',1,'mod_quickicon',0,2,1,'',1,1, '');
INSERT INTO `#__modules` VALUES (27, 'Logged in Users','',2,'cpanel',0,'0000-00-00 00:00:00',1,'mod_logged',0,2,1,'',0,1, '');
INSERT INTO `#__modules` VALUES (29, 'Footer', '', 0, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 1, '');
INSERT INTO `#__modules` VALUES (31, 'Breadcrumbs', '', 1, 'breadcrumb', 0, '0000-00-00 00:00:00', 1, 'mod_breadcrumbs', 0, 0, 1, 'moduleclass_sfx=\ncache=0\nshowHome=1\nhomeText=Home\nshowComponent=1\nseparator=\n\n', 1, 0, '');
INSERT INTO `#__modules` VALUES (33, 'Admin Menu','', 1,'menu', 0,'0000-00-00 00:00:00', 1,'mod_menu', 0, 2, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (34, 'Admin SubMenu','', 1,'submenu', 0,'0000-00-00 00:00:00', 1,'mod_submenu', 0, 2, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (35, 'User Status','', 1,'status', 0,'0000-00-00 00:00:00', 1,'mod_status', 0, 2, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (36, 'Title','', 1,'title', 0,'0000-00-00 00:00:00', 1,'mod_title', 0, 2, 1, '', 0, 1, '');

# --------------------------------------------------------

#
# Table structure for table `#__modules_menu`
#

CREATE TABLE `#__modules_menu` (
  `moduleid` int(11) NOT NULL default '0',
  `menuid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`moduleid`,`menuid`)
) TYPE=MyISAM;

#
# Dumping data for table `#__modules_menu`
#

INSERT INTO `#__modules_menu` VALUES (3,0);
INSERT INTO `#__modules_menu` VALUES (31,0);

# --------------------------------------------------------

#
# Table structure for table `#__newsfeeds`
#

CREATE TABLE `#__newsfeeds` (
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
  `ordering` int(11) NOT NULL default '0',
  `rtl` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `published` (`published`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__poll_data`
#

CREATE TABLE `#__poll_data` (
  `id` int(11) NOT NULL auto_increment,
  `pollid` int(11) NOT NULL default '0',
  `text` text NOT NULL default '',
  `hits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`,`text`(1))
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__poll_date`
#

CREATE TABLE `#__poll_date` (
  `id` bigint(20) NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `vote_id` int(11) NOT NULL default '0',
  `poll_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `poll_id` (`poll_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__polls`
#

CREATE TABLE `#__polls` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` TEXT NOT NULL default '',
  `voters` int(9) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL default '0',
  `access` int(11) NOT NULL default '0',
  `lag` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__poll_menu`
#

CREATE TABLE `#__poll_menu` (
  `pollid` int(11) NOT NULL default '0',
  `menuid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`pollid`,`menuid`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__sections`
#

CREATE TABLE `#__sections` (
  `id` int(11) NOT NULL auto_increment,
  `title` TEXT NOT NULL default '',
  `name` TEXT NOT NULL default '',
  `image` TEXT NOT NULL default '',
  `scope` varchar(50) NOT NULL default '',
  `image_position` varchar(90) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_scope` (`scope`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__session`
#

CREATE TABLE `#__session` (
  `username` varchar(150) default '',
  `time` varchar(14) default '',
  `session_id` varchar(200) NOT NULL default '0',
  `guest` tinyint(4) default '1',
  `userid` int(11) default '0',
  `usertype` varchar(150) default '',
  `gid` tinyint(3) unsigned NOT NULL default '0',
  `client_id` tinyint(3) unsigned NOT NULL default '0',
  `data` text,
  PRIMARY KEY  (`session_id`),
  KEY `whosonline` (`guest`,`usertype`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__stats_agents`
#

CREATE TABLE `#__stats_agents` (
  `agent` varchar(255) NOT NULL default '',
  `type` tinyint(1) unsigned NOT NULL default '0',
  `hits` int(11) unsigned NOT NULL default '1'
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__templates_menu`
#

CREATE TABLE `#__templates_menu` (
  `template` TEXT NOT NULL default '',
  `menuid` int(11) NOT NULL default '0',
  `client_id` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`template`(255),`menuid`)
) TYPE=MyISAM;

# Dumping data for table `#__templates_menu`

INSERT INTO `#__templates_menu` VALUES ('rhuk_milkyway', '0', '0');
INSERT INTO `#__templates_menu` VALUES ('khepri', '0', '1');

# --------------------------------------------------------

#
# Table structure for table `#__template_positions`
#

CREATE TABLE `#__template_positions` (
  `id` int(11) NOT NULL auto_increment,
  `position` varchar(150) NOT NULL default '',
  `description` TEXT NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `#__template_positions`
#

INSERT INTO `#__template_positions` VALUES (0, 'left', '');
INSERT INTO `#__template_positions` VALUES (0, 'right', '');
INSERT INTO `#__template_positions` VALUES (0, 'top', '');
INSERT INTO `#__template_positions` VALUES (0, 'bottom', '');
INSERT INTO `#__template_positions` VALUES (0, 'inset', '');
INSERT INTO `#__template_positions` VALUES (0, 'banner', '');
INSERT INTO `#__template_positions` VALUES (0, 'header', '');
INSERT INTO `#__template_positions` VALUES (0, 'footer', '');
INSERT INTO `#__template_positions` VALUES (0, 'newsflash', '');
INSERT INTO `#__template_positions` VALUES (0, 'legals', '');
INSERT INTO `#__template_positions` VALUES (0, 'pathway', '');
INSERT INTO `#__template_positions` VALUES (0, 'breadcrumb', '');
INSERT INTO `#__template_positions` VALUES (0, 'toolbar', '');
INSERT INTO `#__template_positions` VALUES (0, 'menu', '');
INSERT INTO `#__template_positions` VALUES (0, 'cpanel', '');
INSERT INTO `#__template_positions` VALUES (0, 'user1', '');
INSERT INTO `#__template_positions` VALUES (0, 'user2', '');
INSERT INTO `#__template_positions` VALUES (0, 'user3', '');
INSERT INTO `#__template_positions` VALUES (0, 'user4', '');
INSERT INTO `#__template_positions` VALUES (0, 'user5', '');
INSERT INTO `#__template_positions` VALUES (0, 'user6', '');
INSERT INTO `#__template_positions` VALUES (0, 'user7', '');
INSERT INTO `#__template_positions` VALUES (0, 'user8', '');
INSERT INTO `#__template_positions` VALUES (0, 'user9', '');
INSERT INTO `#__template_positions` VALUES (0, 'advert1', '');
INSERT INTO `#__template_positions` VALUES (0, 'advert2', '');
INSERT INTO `#__template_positions` VALUES (0, 'advert3', '');
INSERT INTO `#__template_positions` VALUES (0, 'icon', '');
INSERT INTO `#__template_positions` VALUES (0, 'debug', '');
INSERT INTO `#__template_positions` VALUES (0, 'submenu', '');
INSERT INTO `#__template_positions` VALUES (0, 'status', '');
INSERT INTO `#__template_positions` VALUES (0, 'title', '');
INSERT INTO `#__template_positions` VALUES (0, 'syndicate', '');
INSERT INTO `#__template_positions` VALUES (0, 'cp_shell', '');

# --------------------------------------------------------

#
# Table structure for table `#__users`
#

CREATE TABLE `#__users` (
  `id` int(11) NOT NULL auto_increment,
  `name` TEXT NOT NULL default '',
  `username` varchar(150) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `usertype` varchar(75) NOT NULL default '',
  `block` tinyint(4) NOT NULL default '0',
  `sendEmail` tinyint(4) default '0',
  `gid` tinyint(3) unsigned NOT NULL default '1',
  `registerDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL default '',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `usertype` (`usertype`),
  KEY `idx_name` (`name`(255))
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__weblinks`
#

CREATE TABLE `#__weblinks` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `sid` int(11) NOT NULL default '0',
  `title` TEXT NOT NULL default '',
  `url` varchar(250) NOT NULL default '',
  `description` text NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `hits` int(11) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `archived` tinyint(1) NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '1',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`,`published`,`archived`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__core_acl_aro`
#

CREATE TABLE `#__core_acl_aro` (
  `id` int(11) NOT NULL auto_increment,
  `section_value` varchar(240) NOT NULL default '0',
  `value` varchar(240) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `__section_value_value_aro` (`section_value`(100),`value`(100)),
  KEY `#__gacl_hidden_aro` (`hidden`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `#__core_acl_aro_groups`
#
CREATE TABLE `#__core_acl_aro_groups` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `#__gacl_parent_id_aro_groups` (`parent_id`),
  KEY `#__gacl_lft_rgt_aro_groups` (`lft`,`rgt`)
) TYPE=MyISAM ;

#
# Dumping data for table `#__core_acl_aro_groups`
#

INSERT INTO `#__core_acl_aro_groups` VALUES (17,0,'ROOT',1,22,'ROOT');
INSERT INTO `#__core_acl_aro_groups` VALUES (28,17,'USERS',2,21,'USERS');
INSERT INTO `#__core_acl_aro_groups` VALUES (29,28,'Public Frontend',3,12,'Public Frontend');
INSERT INTO `#__core_acl_aro_groups` VALUES (18,29,'Registered',4,11,'Registered');
INSERT INTO `#__core_acl_aro_groups` VALUES (19,18,'Author',5,10,'Author');
INSERT INTO `#__core_acl_aro_groups` VALUES (20,19,'Editor',6,9,'Editor');
INSERT INTO `#__core_acl_aro_groups` VALUES (21,20,'Publisher',7,8,'Publisher');
INSERT INTO `#__core_acl_aro_groups` VALUES (30,28,'Public Backend',13,20,'Public Backend');
INSERT INTO `#__core_acl_aro_groups` VALUES (23,30,'Manager',14,19,'Manager');
INSERT INTO `#__core_acl_aro_groups` VALUES (24,23,'Administrator',15,18,'Administrator');
INSERT INTO `#__core_acl_aro_groups` VALUES (25,24,'Super Administrator',16,17,'Super Administrator');

# --------------------------------------------------------

#
# Table structure for table `#__core_acl_groups_aro_map`
#
CREATE TABLE `#__core_acl_groups_aro_map` (
  `group_id` int(11) NOT NULL default '0',
  `section_value` varchar(240) NOT NULL default '',
  `aro_id` int(11) NOT NULL default '0',
  UNIQUE KEY `group_id_aro_id_groups_aro_map` (`group_id`,`section_value`,`aro_id`)
) TYPE=MyISAM ;

# --------------------------------------------------------

#
# Table structure for table `#__core_acl_aro_sections`
#
CREATE TABLE `#__core_acl_aro_sections` (
  `section_id` int(11) NOT NULL auto_increment,
  `value` varchar(230) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`section_id`),
  UNIQUE KEY `value_aro_sections` (`value`),
  UNIQUE KEY `#__gacl_value_aro_sections` (`value`),
  KEY `hidden_aro_sections` (`hidden`),
  KEY `#__gacl_hidden_aro_sections` (`hidden`)
) TYPE=MyISAM ;

INSERT INTO `#__core_acl_aro_sections` VALUES (10,'users',1,'Users',0);

# --------------------------------------------------------

# $Id: joomla.sql 320 2009-05-27 19:52:28Z rob.schley $

# --------------------------------------------------------

#
# Table structure for table `#__banner`
#

CREATE TABLE `#__banner` (
  `bid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `type` varchar(30) NOT NULL default 'banner',
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
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
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__bannerclient`
#

CREATE TABLE `#__bannerclient` (
  `cid` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `contact` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `extrainfo` text NOT NULL,
  `checked_out` tinyint(1) NOT NULL default '0',
  `checked_out_time` time default NULL,
  `editor` varchar(50) default NULL,
  PRIMARY KEY  (`cid`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__bannertrack`
#

CREATE TABLE  `#__bannertrack` (
  `track_date` date NOT NULL,
  `track_type` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__categories`
#

CREATE TABLE `#__categories` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default 0,
  `title` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `section` varchar(50) NOT NULL default '',
  `image_position` varchar(30) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `editor` varchar(50) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`section`,`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__components`
#

CREATE TABLE `#__components` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `menuid` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned NOT NULL default '0',
  `admin_menu_link` varchar(255) NOT NULL default '',
  `admin_menu_alt` varchar(255) NOT NULL default '',
  `option` varchar(50) NOT NULL default '',
  `ordering` int(11) NOT NULL default '0',
  `admin_menu_img` varchar(255) NOT NULL default '',
  `iscore` tinyint(4) NOT NULL default '0',
  `params` text NOT NULL,
  `enabled` tinyint(4) UNSIGNED NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `parent_option` (`parent`, `option`(32))
) TYPE=MyISAM CHARACTER SET `utf8`;

#
# Dumping data for table `#__components`
#

INSERT INTO `#__components` VALUES (1, 'Banners', '', 0, 0, '', 'Banner Management', 'com_banners', 0, 'js/ThemeOffice/component.png', 0, 'track_impressions=0\ntrack_clicks=0\ntag_prefix=\n\n', 1);
INSERT INTO `#__components` VALUES (2, 'Banners', '', 0, 1, 'option=com_banners', 'Active Banners', 'com_banners', 1, 'js/ThemeOffice/edit.png', 0, '', 1);
INSERT INTO `#__components` VALUES (3, 'Clients', '', 0, 1, 'option=com_banners&c=client', 'Manage Clients', 'com_banners', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (4, 'Web Links', 'option=com_weblinks', 0, 0, '', 'Manage Weblinks', 'com_weblinks', 0, 'js/ThemeOffice/component.png', 0, 'show_comp_description=1\ncomp_description=\nshow_link_hits=1\nshow_link_description=1\nshow_other_cats=1\nshow_headings=1\nshow_page_title=1\nlink_target=0\nlink_icons=\n\n', 1);
INSERT INTO `#__components` VALUES (5, 'Links', '', 0, 4, 'option=com_weblinks', 'View existing weblinks', 'com_weblinks', 1, 'js/ThemeOffice/edit.png', 0, '', 1);
INSERT INTO `#__components` VALUES (6, 'Categories', '', 0, 4, 'option=com_categories&section=com_weblinks', 'Manage weblink categories', '', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (7, 'Contacts', 'option=com_contact', 0, 0, '', 'Edit contact details', 'com_contact', 0, 'js/ThemeOffice/component.png', 1, 'contact_icons=0\nicon_address=\nicon_email=\nicon_telephone=\nicon_fax=\nicon_misc=\nshow_headings=1\nshow_position=1\nshow_email=0\nshow_telephone=1\nshow_mobile=1\nshow_fax=1\nbannedEmail=\nbannedSubject=\nbannedText=\nsession=1\ncustomReply=0\n\n', 1);
INSERT INTO `#__components` VALUES (8, 'Contacts', '', 0, 7, 'option=com_contact', 'Edit contact details', 'com_contact', 0, 'js/ThemeOffice/edit.png', 1, '', 1);
INSERT INTO `#__components` VALUES (9, 'Categories', '', 0, 7, 'option=com_categories&section=com_contact_details', 'Manage contact categories', '', 2, 'js/ThemeOffice/categories.png', 1, 'contact_icons=0\nicon_address=\nicon_email=\nicon_telephone=\nicon_fax=\nicon_misc=\nshow_headings=1\nshow_position=1\nshow_email=0\nshow_telephone=1\nshow_mobile=1\nshow_fax=1\nbannedEmail=\nbannedSubject=\nbannedText=\nsession=1\ncustomReply=0\n\n', 1);
INSERT INTO `#__components` VALUES (11, 'News Feeds', 'option=com_newsfeeds', 0, 0, '', 'News Feeds Management', 'com_newsfeeds', 0, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (12, 'Feeds', '', 0, 11, 'option=com_newsfeeds', 'Manage News Feeds', 'com_newsfeeds', 1, 'js/ThemeOffice/edit.png', 0, 'show_headings=1\nshow_name=1\nshow_articles=1\nshow_link=1\nshow_cat_description=1\nshow_cat_items=1\nshow_feed_image=1\nshow_feed_description=1\nshow_item_description=1\nfeed_word_count=0\n\n', 1);
INSERT INTO `#__components` VALUES (13, 'Categories', '', 0, 11, 'option=com_categories&section=com_newsfeeds', 'Manage Categories', '', 2, 'js/ThemeOffice/categories.png', 0, '', 1);
INSERT INTO `#__components` VALUES (14, 'User', 'option=com_user', 0, 0, '', '', 'com_user', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (15, 'Search', 'option=com_search', 0, 0, 'option=com_search', 'Search Statistics', 'com_search', 0, 'js/ThemeOffice/component.png', 1, 'enabled=0\n\n', 1);
INSERT INTO `#__components` VALUES (16, 'Categories', '', 0, 1, 'option=com_categories&section=com_banner', 'Categories', '', 3, '', 1, '', 1);
INSERT INTO `#__components` VALUES (17, 'Wrapper', 'option=com_wrapper', 0, 0, '', 'Wrapper', 'com_wrapper', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (18, 'Mail To', '', 0, 0, '', '', 'com_mailto', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (19, 'Media Manager', '', 0, 0, 'option=com_media', 'Media Manager', 'com_media', 0, '', 1, 'upload_extensions=bmp,csv,doc,epg,gif,ico,jpg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,EPG,GIF,ICO,JPG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS\nupload_maxsize=10000000\nfile_path=images\nimage_path=images/stories\nrestrict_uploads=1\ncheck_mime=1\nimage_extensions=bmp,gif,jpg,png\nignore_extensions=\nupload_mime=image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip\nupload_mime_illegal=text/html', 1);
INSERT INTO `#__components` VALUES (20, 'Articles', 'option=com_content', 0, 0, '', '', 'com_content', 0, '', 1, 'show_noauth=0\nshow_title=1\nlink_titles=0\nshow_intro=1\nshow_section=0\nlink_section=0\nshow_category=0\nlink_category=0\nshow_author=1\nshow_create_date=1\nshow_modify_date=1\nshow_item_navigation=0\nshow_readmore=1\nshow_vote=0\nshow_icons=1\nshow_pdf_icon=1\nshow_print_icon=1\nshow_email_icon=1\nshow_hits=1\nfeed_summary=0\n\n', 1);
INSERT INTO `#__components` VALUES (21, 'Configuration Manager', '', 0, 0, '', 'Configuration', 'com_config', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (22, 'Installation Manager', '', 0, 0, '', 'Installer', 'com_installer', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (23, 'Language Manager', '', 0, 0, '', 'Languages', 'com_languages', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (24, 'Mass mail', '', 0, 0, '', 'Mass Mail', 'com_massmail', 0, '', 1, 'mailSubjectPrefix=\nmailBodySuffix=\n\n', 1);
INSERT INTO `#__components` VALUES (25, 'Menu Editor', '', 0, 0, '', 'Menu Editor', 'com_menus', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (27, 'Messaging', '', 0, 0, '', 'Messages', 'com_messages', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (28, 'Modules Manager', '', 0, 0, '', 'Modules', 'com_modules', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (29, 'Plugin Manager', '', 0, 0, '', 'Plugins', 'com_plugins', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (30, 'Template Manager', '', 0, 0, '', 'Templates', 'com_templates', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (31, 'User Manager', '', 0, 0, '', 'Users', 'com_users', 0, '', 1, 'allowUserRegistration=1\nnew_usertype=Registered\nuseractivation=1\nfrontend_userparams=1\n\n', 1);
INSERT INTO `#__components` VALUES (32, 'Cache Manager', '', 0, 0, '', 'Cache', 'com_cache', 0, '', 1, '', 1);
INSERT INTO `#__components` VALUES (33, 'Control Panel', '', 0, 0, '', 'Control Panel', 'com_cpanel', 0, '', 1, '', 1);

# --------------------------------------------------------

#
# Table structure for table `#__contact_details`
#

CREATE TABLE `#__contact_details` (
  `id` int(11) NOT NULL auto_increment,
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
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `params` text NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  `catid` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `mobile` varchar(255) NOT NULL default '',
  `webpage` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__content`
#

CREATE TABLE `#__content` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `title_alias` varchar(255) NOT NULL default '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL default '0',
  `sectionid` int(11) unsigned NOT NULL default '0',
  `mask` int(11) unsigned NOT NULL default '0',
  `catid` int(11) unsigned NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL default '0',
  `created_by_alias` varchar(255) NOT NULL default '',
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
  KEY `idx_createdby` (`created_by`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__content_frontpage`
#

CREATE TABLE `#__content_frontpage` (
  `content_id` int(11) NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  PRIMARY KEY  (`content_id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__content_rating`
#

CREATE TABLE `#__content_rating` (
  `content_id` int(11) NOT NULL default '0',
  `rating_sum` int(11) unsigned NOT NULL default '0',
  `rating_count` int(11) unsigned NOT NULL default '0',
  `lastip` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`content_id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

# Table structure for table `#__core_log_items`

CREATE TABLE `#__core_log_items` (
  `time_stamp` date NOT NULL default '0000-00-00',
  `item_table` varchar(50) NOT NULL default '',
  `item_id` int(11) unsigned NOT NULL default '0',
  `hits` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

# Table structure for table `#__core_log_searches`

CREATE TABLE `#__core_log_searches` (
  `search_term` varchar(128) NOT NULL default '',
  `hits` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM CHARACTER SET `utf8`;

#
# Table structure for table `#__plugins`
#

CREATE TABLE `#__plugins` (
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
) TYPE=MyISAM CHARACTER SET `utf8`;

INSERT INTO `#__plugins` VALUES (1, 'Authentication - Joomla', 'joomla', 'authentication', 1, 1, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (2, 'Authentication - LDAP', 'ldap', 'authentication', 1, 2, 0, 1, 0, 0, '0000-00-00 00:00:00', 'host=\nport=389\nuse_ldapV3=0\nnegotiate_tls=0\nno_referrals=0\nauth_method=bind\nbase_dn=\nsearch_string=\nusers_dn=\nusername=\npassword=\nldap_fullname=fullName\nldap_email=mail\nldap_uid=uid\n\n');
INSERT INTO `#__plugins` VALUES (3, 'Authentication - GMail', 'gmail', 'authentication', 1, 4, 0, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (4, 'Authentication - OpenID', 'openid', 'authentication', 1, 3, 0, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (5, 'User - Joomla!', 'joomla', 'user', 1, 0, 1, 0, 0, 0, '0000-00-00 00:00:00', 'autoregister=1\n\n');
INSERT INTO `#__plugins` VALUES (6, 'Search - Content','content','search',1,1,1,1,0,0,'0000-00-00 00:00:00','search_limit=50\nsearch_content=1\nsearch_uncategorised=1\nsearch_archived=1\n\n');
INSERT INTO `#__plugins` VALUES (7, 'Search - Contacts','contacts','search',1,3,1,1,0,0,'0000-00-00 00:00:00','search_limit=50\n\n');
INSERT INTO `#__plugins` VALUES (8, 'Search - Categories', 'categories', 'search', 1, 4, 1, 0, 0, 0, '0000-00-00 00:00:00', 'search_limit=50\n\n');
INSERT INTO `#__plugins` VALUES (9, 'Search - Sections', 'sections', 'search', 1, 5, 1, 0, 0, 0, '0000-00-00 00:00:00', 'search_limit=50\n\n');
INSERT INTO `#__plugins` VALUES (10, 'Search - Newsfeeds', 'newsfeeds', 'search', 0, 6, 1, 0, 0, 0, '0000-00-00 00:00:00', 'search_limit=50\n\n');
INSERT INTO `#__plugins` VALUES (11, 'Search - Weblinks','weblinks','search',1,2,1,1,0,0,'0000-00-00 00:00:00','search_limit=50\n\n');
INSERT INTO `#__plugins` VALUES (12, 'Content - Pagebreak','pagebreak','content',1,10000,1,1,0,0,'0000-00-00 00:00:00','enabled=1\ntitle=1\nmultipage_toc=1\nshowall=1\n\n');
INSERT INTO `#__plugins` VALUES (13, 'Content - Rating','vote','content',1,4,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (14, 'Content - Email Cloaking', 'emailcloak', 'content', 1, 5, 1, 0, 0, 0, '0000-00-00 00:00:00', 'mode=1\n\n');
INSERT INTO `#__plugins` VALUES (15, 'Content - Code Hightlighter (GeSHi)', 'geshi', 'content', 1, 5, 0, 0, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (16, 'Content - Load Module', 'loadmodule', 'content', 1, 6, 1, 0, 0, 0, '0000-00-00 00:00:00', 'enabled=1\nstyle=0\n\n');
INSERT INTO `#__plugins` VALUES (17, 'Content - Page Navigation','pagenavigation','content',1,2,1,1,0,0,'0000-00-00 00:00:00','position=1\n\n');
INSERT INTO `#__plugins` VALUES (18, 'Editor - No Editor','none','editors',1,0,1,1,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (19, 'Editor - TinyMCE 2.0', 'tinymce', 'editors', 1, 0, 1, 1, 0, 0, '0000-00-00 00:00:00', 'theme=advanced\ncleanup=1\ncleanup_startup=0\nautosave=0\ncompressed=0\nrelative_urls=1\ntext_direction=ltr\nlang_mode=0\nlang_code=en\ninvalid_elements=applet\ncontent_css=1\ncontent_css_custom=\nnewlines=0\ntoolbar=top\nhr=1\nsmilies=1\ntable=1\nstyle=1\nlayer=1\nxhtmlxtras=0\ntemplate=0\ndirectionality=1\nfullscreen=1\nhtml_height=550\nhtml_width=750\npreview=1\ninsertdate=1\nformat_date=%Y-%m-%d\ninserttime=1\nformat_time=%H:%M:%S\n\n');
INSERT INTO `#__plugins` VALUES (21, 'Editor Button - Image','image','editors-xtd',1,0,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (22, 'Editor Button - Pagebreak','pagebreak','editors-xtd',1,0,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (23, 'Editor Button - Readmore','readmore','editors-xtd',1,0,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (27, 'System - SEF','sef','system',1,1,1,0,0,0,'0000-00-00 00:00:00','');
INSERT INTO `#__plugins` VALUES (28, 'System - Debug', 'debug', 'system', 1, 2, 1, 0, 0, 0, '0000-00-00 00:00:00', 'queries=1\nmemory=1\nlangauge=1\n\n');
INSERT INTO `#__plugins` VALUES (29, 'System - Legacy', 'legacy', 'system', 1, 3, 0, 1, 0, 0, '0000-00-00 00:00:00', 'route=0\n\n');
INSERT INTO `#__plugins` VALUES (30, 'System - Cache', 'cache', 'system', 1, 4, 0, 1, 0, 0, '0000-00-00 00:00:00', 'browsercache=0\ncachetime=15\n\n');
INSERT INTO `#__plugins` VALUES (31, 'System - Log', 'log', 'system', 1, 5, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (32, 'System - Remember Me', 'remember', 'system', 1, 6, 1, 1, 0, 0, '0000-00-00 00:00:00', '');
INSERT INTO `#__plugins` VALUES (33, 'System - Backlink', 'backlink', 'system', 1, 7, 0, 1, 0, 0, '0000-00-00 00:00:00', '');

# --------------------------------------------------------

#
# Table structure for table `#__menu`
#

CREATE TABLE `#__menu` (
  `id` int(11) NOT NULL auto_increment,
  `menutype` varchar(75) default NULL,
  `name` varchar(255) default NULL,
  `alias` varchar(255) NOT NULL default '',
  `link` text,
  `type` varchar(50) NOT NULL default '',
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
) TYPE=MyISAM CHARACTER SET `utf8`;

INSERT INTO `#__menu` VALUES (1, 'mainmenu', 'Home', 'home', 'index.php?option=com_content&view=frontpage', 'component', 1, 0, 20, 0, 1, 0, '0000-00-00 00:00:00', 0, 0, 1, 3, 'num_leading_articles=1\nnum_intro_articles=4\nnum_columns=2\nnum_links=4\norderby_pri=\norderby_sec=front\nshow_pagination=2\nshow_pagination_results=1\nshow_feed_link=1\nshow_noauth=\nshow_title=\nlink_titles=\nshow_intro=\nshow_section=\nlink_section=\nshow_category=\nlink_category=\nshow_author=\nshow_create_date=\nshow_modify_date=\nshow_item_navigation=\nshow_readmore=\nshow_vote=\nshow_icons=\nshow_pdf_icon=\nshow_print_icon=\nshow_email_icon=\nshow_hits=\nfeed_summary=\npage_title=\nshow_page_title=1\npageclass_sfx=\nmenu_image=-1\nsecure=0\n\n', 0, 0, 1);

# --------------------------------------------------------

#
# Table structure for table `#__menu_types`
#

CREATE TABLE `#__menu_types` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `menutype` VARCHAR(75) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY(`id`),
  UNIQUE `menutype`(`menutype`)
) TYPE=MyISAM CHARACTER SET `utf8`;

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
  PRIMARY KEY  (`message_id`),
  KEY `useridto_state` (`user_id_to`, `state`)
) TYPE=MyISAM CHARACTER SET `utf8`;
# --------------------------------------------------------

#
# Table structure for table `#__messages_cfg`
#

CREATE TABLE `#__messages_cfg` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `cfg_name` varchar(100) NOT NULL default '',
  `cfg_value` varchar(255) NOT NULL default '',
  UNIQUE `idx_user_var_name` (`user_id`,`cfg_name`)
) TYPE=MyISAM CHARACTER SET `utf8`;
# --------------------------------------------------------

#
# Table structure for table `#__modules`
#

CREATE TABLE `#__modules` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `ordering` int(11) NOT NULL default '0',
  `position` varchar(50) default NULL,
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL default '0',
  `module` varchar(50) default NULL,
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
) TYPE=MyISAM CHARACTER SET `utf8`;

INSERT INTO `#__modules` VALUES (1, 'Main Menu', '', 1, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_mainmenu', 0, 1, 1, 'menutype=mainmenu\nmoduleclass_sfx=_menu\n', 1, 0, '');
INSERT INTO `#__modules` VALUES (2, 'Login', '', 1, 'login', 0, '0000-00-00 00:00:00', 1, 'mod_login', 0, 1, 1, '', 1, 1, '');
INSERT INTO `#__modules` VALUES (3, 'Popular','',3,'cpanel',0,'0000-00-00 00:00:00',1,'mod_popular',0,3,1,'',0, 1, '');
INSERT INTO `#__modules` VALUES (4, 'Recent added Articles','',4,'cpanel',0,'0000-00-00 00:00:00',1,'mod_latest',0,3,1,'ordering=c_dsc\nuser_id=0\ncache=0\n\n',0, 1, '');
INSERT INTO `#__modules` VALUES (5, 'Menu Stats','',5,'cpanel',0,'0000-00-00 00:00:00',1,'mod_stats',0,3,1,'',0, 1, '');
INSERT INTO `#__modules` VALUES (6, 'Unread Messages','',1,'header',0,'0000-00-00 00:00:00',1,'mod_unread',0,3,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (7, 'Online Users','',2,'header',0,'0000-00-00 00:00:00',1,'mod_online',0,3,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (8, 'Toolbar','',1,'toolbar',0,'0000-00-00 00:00:00',1,'mod_toolbar',0,3,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (9, 'Quick Icons','',1,'icon',0,'0000-00-00 00:00:00',1,'mod_quickicon',0,3,1,'',1,1, '');
INSERT INTO `#__modules` VALUES (10, 'Logged in Users','',2,'cpanel',0,'0000-00-00 00:00:00',1,'mod_logged',0,3,1,'',0,1, '');
INSERT INTO `#__modules` VALUES (11, 'Footer', '', 0, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 1, 1, '', 1, 1, '');
INSERT INTO `#__modules` VALUES (12, 'Admin Menu','', 1,'menu', 0,'0000-00-00 00:00:00', 1,'mod_menu', 0, 3, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (13, 'Admin SubMenu','', 1,'submenu', 0,'0000-00-00 00:00:00', 1,'mod_submenu', 0, 3, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (14, 'User Status','', 1,'status', 0,'0000-00-00 00:00:00', 1,'mod_status', 0, 3, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (15, 'Title','', 1,'title', 0,'0000-00-00 00:00:00', 1,'mod_title', 0, 3, 1, '', 0, 1, '');

INSERT INTO `#__modules` VALUES
(16, 'User Menu', '', 4, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_mainmenu', 0, 2, 1, 'menutype=usermenu\nmoduleclass_sfx=_menu\ncache=1', 1, 0, ''),
(17, 'Login Form', '', 8, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_login', 0, 1, 1, 'greeting=1\nname=0', 1, 0, ''),
(18, 'Breadcrumbs', '', 1, 'breadcrumb', 0, '0000-00-00 00:00:00', 1, 'mod_breadcrumbs', 0, 1, 1, 'moduleclass_sfx=\ncache=0\nshowHome=1\nhomeText=Home\nshowComponent=1\nseparator=\n\n', 1, 0, '');

# --------------------------------------------------------

#
# Table structure for table `#__modules_menu`
#

CREATE TABLE `#__modules_menu` (
  `moduleid` int(11) NOT NULL default '0',
  `menuid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`moduleid`,`menuid`)
) TYPE=MyISAM CHARACTER SET `utf8`;

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

# --------------------------------------------------------

#
# Table structure for table `#__newsfeeds`
#

CREATE TABLE `#__newsfeeds` (
  `catid` int(11) NOT NULL default '0',
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `alias` varchar(255) NOT NULL default '',
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
  KEY `published` (`published`),
  KEY `catid` (`catid`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__sections`
#

CREATE TABLE `#__sections` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `image` TEXT NOT NULL default '',
  `scope` varchar(50) NOT NULL default '',
  `image_position` varchar(30) NOT NULL default '',
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
) TYPE=MyISAM CHARACTER SET `utf8`;

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
  `usertype` varchar(50) default '',
  `gid` tinyint(3) unsigned NOT NULL default '0',
  `client_id` tinyint(3) unsigned NOT NULL default '0',
  `data` longtext,
  PRIMARY KEY  (`session_id`(64)),
  KEY `whosonline` (`guest`,`usertype`),
  KEY `userid` (`userid`),
  KEY `time` (`time`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__stats_agents`
#

CREATE TABLE `#__stats_agents` (
  `agent` varchar(255) NOT NULL default '',
  `type` tinyint(1) unsigned NOT NULL default '0',
  `hits` int(11) unsigned NOT NULL default '1'
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__templates_menu`
#

CREATE TABLE `#__templates_menu` (
  `template` varchar(255) NOT NULL default '',
  `menuid` int(11) NOT NULL default '0',
  `client_id` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`menuid`, `client_id`, `template`(255))
) TYPE=MyISAM CHARACTER SET `utf8`;

# Dumping data for table `#__templates_menu`
INSERT INTO `#__templates_menu` VALUES ('rhuk_milkyway', '0', '0');
INSERT INTO `#__templates_menu` VALUES ('khepri', '0', '1');

# --------------------------------------------------------

#
# Table structure for table `#__users`
#

CREATE TABLE `#__users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `username` varchar(150) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `usertype` varchar(25) NOT NULL default '',
  `block` tinyint(4) NOT NULL default '0',
  `sendEmail` tinyint(4) default '0',
  `gid` tinyint(3) unsigned NOT NULL default '1',
  `registerDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL default '',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `usertype` (`usertype`),
  KEY `idx_name` (`name`),
  KEY `gid_block` (`gid`, `block`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__weblinks`
#

CREATE TABLE `#__weblinks` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `sid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `url` varchar(250) NOT NULL default '',
  `description` TEXT NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `hits` int(11) NOT NULL default '0',
  `state` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `archived` tinyint(1) NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '1',
  `access` int(11) NOT NULL default '1',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`,`state`,`archived`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------


##########################################################
##
## ACCESS CONTROL
##
##########################################################

--
-- Table structure for table `#__access_actions`
--

CREATE TABLE IF NOT EXISTS `#__access_actions` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Primary Key',
  `section_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_sections.id',
  `name` varchar(100) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `description` varchar(1024) NOT NULL,
  `access_type` tinyint(1) unsigned NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_action_name_lookup` (`section_id`,`name`),
  KEY `idx_acl_manager_lookup` (`access_type`,`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__access_actions` VALUES 
(1, 1, 'core.view', 'View', '', 3, 0),
(2, 1, 'core.checkin.manage', 'JAction_Checkin_Manage', 'JAction_Checkin_Manage_Desc', 1, 0),
(3, 1, 'core.cache.manage', 'JAction_Cache_Manage', 'JAction_Cache_Manage_Desc', 1, 0),
(4, 1, 'core.config.manage', 'JAction_Config_Manage', 'JAction_Config_Manage_Desc', 1, 0),
(5, 1, 'core.installer.manage', 'JAction_Installer_Manage', 'JJAction_Installer_Manage_Desc', 1, 0),
(6, 1, 'core.languages.manage', 'JAction_Languages_Manage', 'JAction_Languages_Manage_Desc', 1, 0),
(7, 1, 'core.modules.manage', 'JAction_Modules_Manage', 'JAction_Modules_Manage_Desc', 1, 0),
(8, 1, 'core.plugins.manage', 'JAction_Plugins_Manage', 'JAction_Plugins_Manage_Desc', 1, 0),
(9, 1, 'core.templates.manage', 'JAction_Templates_Manage', 'JAction_Templates_Manage_Desc', 1, 0),
(10, 1, 'core.menus.manage', 'JAction_Menus_Manage', 'JAction_Menus_Manage_Desc', 1, 0),
(11, 1, 'core.users.manage', 'JAction_Users_Manage', 'JAction_Users_Manage_Desc', 1, 0),
(12, 1, 'core.media.manage', 'JAction_Media_Manage', 'JAction_Media_Manage_Desc', 1, 0),
(13, 1, 'core.categories.manage', 'JAction_Categories_Manage', 'JAction_Categories_Manage_Desc', 1, 0),
(14, 1, 'core.massmail.manage', 'JAction_Massmail_Manage', 'JAction_Massmail_Manage_Desc', 1, 0),
(15, 1, 'core.messages.manage', 'JAction_Messages_Manage', 'JAction_Messages_Manage_Desc', 1, 0),
(16, 1, 'core.site.login', 'JAction_Site_Login', 'JAction_Site_Login_Desc', 1, -1),
(17, 1, 'core.administrator.login', 'JAction_Administrator_Login', 'JAction_Administrator_Login_Desc', 1, -1),
(18, 1, 'core.root', 'JAction_Root', 'JAction_Root_Desc', 1, -2),
(19, 1, 'core.plugins.view', 'JAction_Plugins_View', 'JAction_Plugins_View_Desc', 3, 0),
(21, 1, 'core.menu.view', 'JAction_Menu_View', 'JAction_Menu_View_Desc', 3, 0),
(22, 2, 'com_content.manage', 'JAction_Content_Manage', 'JAction_Content_Manage_Desc', 1, 0),
(23, 2, 'com_content.article.edit_article', 'JAction_Content_Edit_Article', 'JAction_Content_Edit_Article_Desc', 1, 0),
(24, 2, 'com_content.article.edit_own', 'JAction_Content_Edit_Own', 'JAction_Content_Edit_Own_Desc', 1, 0),
(25, 2, 'com_content.article.publish', 'JAction_Content_Article_Publish', 'JAction_Content_Article_Publish_Desc', 1, 0),
(26, 2, 'com_content.article.edit', 'JAction_Content_Article_Edit', 'JAction_Content_Article_Edit_Desc', 2, 0),
(27, 2, 'com_content.article.view', 'JAction_Content_Article_View', 'JAction_Content_Article_View_Desc', 3, 0),
(28, 2, 'com_content.category.view', 'JAction_Content_Category_View', 'JAction_Content_Category_View_Desc', 3, 0),
(29, 3, 'com_banners.manage', 'JAction_Banners_Manage', 'JAction_Banners_Manage_Desc', 1, 0),
(30, 4, 'com_contact.manage', 'JAction_Contact_Manage', 'JAction_Contact_Manage_Desc', 1, 0),
(31, 5, 'com_newsfeeds.manage', 'JAction_Newsfeeds_Manage', 'JAction_Newsfeeds_Manage_Desc', 1, 0),
(32, 6, 'com_trash.manage', 'JAction_Trash_Manage', 'JAction_Trash_Manage_Desc', 1, 0),
(33, 7, 'com_weblinks.manage', 'JAction_Weblinks_Manage', 'JAction_Weblinks_Manage_Desc', 1, 0)
;

-- --------------------------------------------------------

--
-- Table structure for table `#__access_action_rule_map`
--

CREATE TABLE IF NOT EXISTS `#__access_action_rule_map` (
  `action_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_actions.id',
  `rule_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_rules.id',
  PRIMARY KEY  (`action_id`,`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__access_action_rule_map` VALUES 
(1, 1),
(1, 2),
(1, 3),
(16, 4),
(17, 5),
(2, 6),
(3, 7),
(4, 8),
(5, 9),
(6, 10),
(7, 11),
(8, 12),
(9, 13),
(10, 14),
(11, 15),
(12, 16),
(13, 17),
(14, 18),
(15, 19),
(19, 20),
(21, 22),
(22, 23),
(23, 24),
(24, 25),
(25, 26),
(26, 27),
(27, 28),
(28, 29),
(29, 30),
(30, 31),
(31, 32),
(32, 33),
(33, 34)
;

-- --------------------------------------------------------

--
-- Table structure for table `#__access_assetgroups`
--

CREATE TABLE IF NOT EXISTS `#__access_assetgroups` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Primary Key',
  `parent_id` int(10) unsigned NOT NULL default '0' COMMENT 'Adjacency List Reference Id',
  `left_id` int(10) unsigned NOT NULL default '0' COMMENT 'Nested Set Reference Id',
  `right_id` int(10) unsigned NOT NULL default '0' COMMENT 'Nested Set Reference Id',
  `title` varchar(100) NOT NULL default '',
  `section_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_sections.id',
  `section` varchar(100) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_assetgroup_title_lookup` (`section`,`title`),
  KEY `idx_assetgroup_adjacency_lookup` (`parent_id`),
  KEY `idx_assetgroup_nested_set_lookup` USING BTREE (`left_id`,`right_id`, `section_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `#__access_assetgroups` VALUES 
(1, 0, 1, 6, 'Public', 1, 'core'),
(2, 1, 2, 3, 'Registered', 1, 'core'),
(3, 1, 4, 5, 'Special', 1, 'core');

-- --------------------------------------------------------

--
-- Table structure for table `#__access_assetgroup_rule_map`
--

CREATE TABLE IF NOT EXISTS `#__access_assetgroup_rule_map` (
  `group_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_assetgroups.id',
  `rule_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_rules.id',
  PRIMARY KEY  (`group_id`,`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `jos_access_assetgroup_rule_map` VALUES 
(1, 1),
(2, 2),
(3, 3),
(1, 20),
(1, 21),
(1, 22),
(1, 27),
(1, 28),
(1, 29)
;

-- --------------------------------------------------------

--
-- Table structure for table `#__access_assets`
--

CREATE TABLE IF NOT EXISTS `#__access_assets` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Primary Key',
  `section_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_sections.id',
  `section` varchar(100) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_asset_name_lookup` (`section_id`,`name`),
  KEY `idx_asset_section_lookup` (`section`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__access_asset_assetgroup_map`
--

CREATE TABLE IF NOT EXISTS `#__access_asset_assetgroup_map` (
  `asset_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_assets.id',
  `group_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_assetgroups.id',
  PRIMARY KEY  (`asset_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__access_asset_rule_map`
--

CREATE TABLE IF NOT EXISTS `#__access_asset_rule_map` (
  `asset_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_assets.id',
  `rule_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_rules.id',
  PRIMARY KEY  (`asset_id`,`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__access_rules`
--

CREATE TABLE IF NOT EXISTS `#__access_rules` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Primary Key',
  `section_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_sections.id',
  `section` varchar(100) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `description` varchar(1024) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `allow` tinyint(1) unsigned NOT NULL default '0',
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  `access_type` tinyint(1) unsigned NOT NULL default '0',
  `updated_date` int(10) unsigned NOT NULL default '0',
  `return` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_rule_name_lookup` (`section_id`,`name`),
  KEY `idx_access_check` (`enabled`, `allow`),
  KEY `idx_updated_lookup` (`updated_date`),
  KEY `idx_action_section_lookup` (`section`),
  KEY `idx_acl_manager_lookup` (`access_type`,`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__access_rules` VALUES 
(1, 1, 'core', 'core.view.1', 'SYSTEM', NULL, 0, 1, 1, 3, 0, NULL),
(2, 1, 'core', 'core.view.2', 'SYSTEM', NULL, 0, 1, 1, 3, 0, NULL),
(3, 1, 'core', 'core.view.3', 'SYSTEM', NULL, 0, 1, 1, 3, 0, NULL),
(4, 1, 'core', 'core.site.login', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(5, 1, 'core', 'core.administrator.login', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(6, 1, 'core', 'core.checkin.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(7, 1, 'core', 'core.cache.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(8, 1, 'core', 'core.config.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(9, 1, 'core', 'core.installer.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(10, 1, 'core', 'core.languages.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(11, 1, 'core', 'core.modules.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(12, 1, 'core', 'core.plugins.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(13, 1, 'core', 'core.templates.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(14, 1, 'core', 'core.menus.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(15, 1, 'core', 'core.users.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(16, 1, 'core', 'core.media.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(17, 1, 'core', 'core.categories.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(18, 1, 'core', 'core.massmail.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(19, 1, 'core', 'core.messages.manage', 'SYSTEM', NULL, 0, 1, 1, 1, 0, NULL),
(20, 1, 'core', 'core.plugins.view', 'SYSTEM', NULL, 0, 1, 1, 3, 0, NULL),
(21, 1, 'core', 'core.modules.view', 'SYSTEM', NULL, 0, 1, 1, 3, 0, NULL),
(22, 1, 'core', 'core.menu.view', 'SYSTEM', NULL, 0, 1, 1, 3, 0, NULL),
(23, 2, 'com_content', 'com_content.manage', 'Content', NULL, 0, 1, 1, 1, 0, NULL),
(24, 2, 'com_content', 'com_content.article.edit_article', 'Content', NULL, 0, 1, 1, 1, 0, NULL),
(25, 2, 'com_content', 'com_content.article.edit_own', 'Content', NULL, 0, 1, 1, 1, 0, NULL),
(26, 2, 'com_content', 'com_content.article.publish', 'Content', NULL, 0, 1, 1, 1, 0, NULL),
(27, 2, 'com_content', 'com_content.article.edit', 'Content', NULL, 0, 1, 1, 2, 0, NULL),
(28, 2, 'com_content', 'com_content.article.view', 'Content', NULL, 0, 1, 1, 3, 0, NULL),
(29, 2, 'com_content', 'com_content.category.view', 'Content', NULL, 0, 1, 1, 3, 0, NULL),
(30, 3, 'com_banners', 'com_banners.manage', 'Banners', NULL, 0, 1, 1, 1, 0, NULL),
(31, 4, 'com_contact', 'com_contact.manage', 'Contact', NULL, 0, 1, 1, 1, 0, NULL),
(32, 5, 'com_newsfeeds', 'com_newsfeeds.manage', 'Newsfeeds', NULL, 0, 1, 1, 1, 0, NULL),
(33, 6, 'com_trash', 'com_trash.manage', 'Trash', NULL, 0, 1, 1, 1, 0, NULL),
(34, 7, 'com_weblinks', 'com_weblinks.manage', 'Weblinks', NULL, 0, 1, 1, 1, 0, NULL)
;

-- --------------------------------------------------------

--
-- Table structure for table `#__access_sections`
--

CREATE TABLE IF NOT EXISTS `#__access_sections` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Primary Key',
  `name` varchar(100) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `ordering` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_section_name_lookup` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `#__access_sections` VALUES 
(1, 'core', 'Core', -1),
(2, 'com_content', 'Content', 0),
(3, 'com_banners', 'Banners', 0),
(4, 'com_contact', 'Contact', 0),
(5, 'com_newsfeeds', 'Newsfeeds', 0),
(6, 'com_trash', 'Trash', 0),
(7, 'com_weblinks', 'Weblinks', 0);

-- --------------------------------------------------------

--
-- Table structure for table `#__usergroups`
--

CREATE TABLE IF NOT EXISTS `#__usergroups` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Primary Key',
  `parent_id` int(10) unsigned NOT NULL default '0' COMMENT 'Adjacency List Reference Id',
  `left_id` int(10) unsigned NOT NULL default '0' COMMENT 'Nested Set Reference Id',
  `right_id` int(10) unsigned NOT NULL default '0' COMMENT 'Nested Set Reference Id',
  `title` varchar(100) NOT NULL default '',
  `section_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_sections.id',
  `section` varchar(100) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_usergroup_title_lookup` (`section`,`title`),
  KEY `idx_usergroup_adjacency_lookup` (`parent_id`),
  KEY `idx_usergroup_nested_set_lookup` USING BTREE (`left_id`,`right_id`, `section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__usergroups`
 (`id` ,`parent_id` ,`left_id` ,`right_id` ,`title` ,`section_id` ,`section`) VALUES
 (1 , 0, 1, 16, "Public", 1, "core"),
 (2 , 1, 2, 15, "Registered", 1, "core"),
 (3 , 2, 3, 8, "Author", 1, "core"),
 (4 , 3, 4, 7, "Editor", 1, "core"),
 (5 , 4, 5, 6, "Publisher", 1, "core"),
 (6 , 2, 9, 14, "Manager", 1, "core"),
 (7 , 6, 10, 13, "Administrator", 1, "core"),
 (8 , 7, 11, 12, "Super Administrator", 1, "core");

-- --------------------------------------------------------

--
-- Table structure for table `#__usergroup_rule_map`
--

CREATE TABLE IF NOT EXISTS `#__usergroup_rule_map` (
  `group_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__usergroups.id',
  `rule_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_rules.id',
  PRIMARY KEY  (`group_id`,`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__usergroup_rule_map` VALUES 
(1, 1),
(2, 2),
(6, 3),
(2, 4),
(6, 4),
(6, 5),
(6, 6),
(6, 7),
(6, 8),
(6, 9),
(6, 10),
(6, 11),
(6, 12),
(6, 13),
(6, 14),
(6, 15),
(6, 16),
(6, 17),
(6, 18),
(6, 19),
(6, 23),
(6, 30),
(6, 31),
(6, 32),
(6, 33),
(6, 34),
(1, 20),
(1, 21),
(1, 22),
(4, 27),
(6, 27),
(1, 28),
(1, 29),
(5, 26),
(3, 25),
(4, 24)
;

-- --------------------------------------------------------

--
-- Table structure for table `#__user_rule_map`
--

CREATE TABLE IF NOT EXISTS `#__user_rule_map` (
  `user_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__users.id',
  `rule_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_rules.id',
  PRIMARY KEY  (`user_id`,`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__user_usergroup_map`
--

CREATE TABLE IF NOT EXISTS `#__user_usergroup_map` (
  `user_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__users.id',
  `group_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__usergroups.id',
  PRIMARY KEY  (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


##########################################################
##
## CORE ASSET MAPPINGS
##
##########################################################

INSERT INTO `jos_access_assets` VALUES 
(7, 1, 'core', 'plugin.28', 'System - Debug')
;
INSERT INTO `jos_access_asset_assetgroup_map` VALUES 
(7, 1)
;
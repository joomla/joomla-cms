# $Id$

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
  `enabled` tinyint(4) NOT NULL default '1',
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
INSERT INTO `#__components` VALUES (10, 'Polls', 'option=com_poll', 0, 0, 'option=com_poll', 'Manage Polls', 'com_poll', 0, 'js/ThemeOffice/component.png', 0, '', 1);
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
INSERT INTO `#__components` VALUES (34, 'Contact Directory', 'option=com_contactdirectory', 0, 0, 'option=com_contactdirectory', 'Contact Directory', 'com_contactdirectory', 0, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (35, 'Contacts', '', 0, 34, 'option=com_contactdirectory&controller=contact', 'Contacts', 'com_contactdirectory', 0, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (36, 'Categories', '', 0, 34, 'option=com_categories&section=com_contactdirectory', 'Categories', 'com_contactdirectory', 1, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (37, 'Fields', '', 0, 34, 'option=com_contactdirectory&controller=field', 'Fields', 'com_contactdirectory', 2, 'js/ThemeOffice/component.png', 0, '', 1);
INSERT INTO `#__components` VALUES (38, 'Access Control', '', 0, 0, 'option=com_acl', 'Access Control', 'com_acl', 0, 'js/ThemeOffice/component.png', 0, '', 1);

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

# --------------------------------------------------------

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
  `access` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',  
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


# Plugins
INSERT INTO #__extensions VALUES(0,"Authentication - Joomla","plugin","joomla","authentication",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",1,0);
INSERT INTO #__extensions VALUES(0,"Authentication - LDAP","plugin","ldap","authentication",0,0,0,1,"","host=\nport=389\nuse_ldapV3=0\nnegotiate_tls=0\nno_referrals=0\nauth_method=bind\nbase_dn=\nsearch_string=\nusers_dn=\nusername=\npassword=\nldap_fullname=fullName\nldap_email=mail\nldap_uid=uid\n\n","","",0,"0000-00-00 00:00:00",2,0);
INSERT INTO #__extensions VALUES(0,"Authentication - GMail","plugin","gmail","authentication",0,0,0,0,"","","","",0,"0000-00-00 00:00:00",4,0);
INSERT INTO #__extensions VALUES(0,"Authentication - OpenID","plugin","openid","authentication",0,0,0,0,"","","","",0,"0000-00-00 00:00:00",3,0);
INSERT INTO #__extensions VALUES(0,"User - Joomla!","plugin","joomla","user",0,1,0,0,"","autoregister=1\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Search - Content","plugin","content","search",0,1,0,1,"","search_limit=50\nsearch_content=1\nsearch_uncategorised=1\nsearch_archived=1\n","","",0,"0000-00-00 00:00:00",1,0);
INSERT INTO #__extensions VALUES(0,"Search - Contacts","plugin","contacts","search",0,1,0,1,"","search_limit=50\n","","",0,"0000-00-00 00:00:00",3,0);
INSERT INTO #__extensions VALUES(0,"Search - Categories","plugin","categories","search",0,1,0,0,"","search_limit=50\n","","",0,"0000-00-00 00:00:00",4,0);
INSERT INTO #__extensions VALUES(0,"Search - Sections","plugin","sections","search",0,1,0,0,"","search_limit=50\n","","",0,"0000-00-00 00:00:00",5,0);
INSERT INTO #__extensions VALUES(0,"Search - Newsfeeds","plugin","newsfeeds","search",0,1,0,0,"","search_limit=50\n","","",0,"0000-00-00 00:00:00",6,0);
INSERT INTO #__extensions VALUES(0,"Search - Weblinks","plugin","weblinks","search",0,1,0,1,"","search_limit=50\n","","",0,"0000-00-00 00:00:00",2,0);
INSERT INTO #__extensions VALUES(0,"Content - Pagebreak","plugin","pagebreak","content",0,1,0,1,"","enabled=1\ntitle=1\nmultipage_toc=1\nshowall=1\n","","",0,"0000-00-00 00:00:00",10000,0);
INSERT INTO #__extensions VALUES(0,"Content - Rating","plugin","vote","content",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",4,0);
INSERT INTO #__extensions VALUES(0,"Content - Email Cloaking","plugin","emailcloak","content",0,1,0,0,"","mode=1\n","","",0,"0000-00-00 00:00:00",5,0);
INSERT INTO #__extensions VALUES(0,"Content - Code Hightlighter (GeSHi)","plugin","geshi","content",0,0,0,0,"","","","",0,"0000-00-00 00:00:00",5,0);
INSERT INTO #__extensions VALUES(0,"Content - Load Module","plugin","loadmodule","content",0,1,0,0,"","enabled=1\nstyle=0\n","","",0,"0000-00-00 00:00:00",6,0);
INSERT INTO #__extensions VALUES(0,"Content - Page Navigation","plugin","pagenavigation","content",0,1,0,1,"","position=1\n","","",0,"0000-00-00 00:00:00",2,0);
INSERT INTO #__extensions VALUES(0,"Editor - No Editor","plugin","none","editors",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Editor - TinyMCE 2.0","plugin","tinymce","editors",0,1,0,1,"","theme=advanced\ncleanup=1\ncleanup_startup=0\nautosave=0\ncompressed=0\nrelative_urls=1\ntext_direction=ltr\nlang_mode=0\nlang_code=en\ninvalid_elements=applet\ncontent_css=1\ncontent_css_custom=\nnewlines=0\ntoolbar=top\nhr=1\nsmilies=1\ntable=1\nstyle=1\nlayer=1\nxhtmlxtras=0\ntemplate=0\ndirectionality=1\nfullscreen=1\nhtml_height=550\nhtml_width=750\npreview=1\ninsertdate=1\nformat_date=%Y-%m-%d\ninserttime=1\nformat_time=%H:%M:%S\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Editor - XStandard Lite 2.0","plugin","xstandard","editors",0,0,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Editor Button - Image","plugin","image","editors-xtd",0,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Editor Button - Pagebreak","plugin","pagebreak","editors-xtd",0,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Editor Button - Readmore","plugin","readmore","editors-xtd",0,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"XML-RPC - Joomla","plugin","joomla","xmlrpc",0,0,0,1,"","","","",0,"0000-00-00 00:00:00",7,0);
INSERT INTO #__extensions VALUES(0,"XML-RPC - Blogger API","plugin","blogger","xmlrpc",0,0,0,1,"","catid=1\nsectionid=0\n\n","","",0,"0000-00-00 00:00:00",7,0);
INSERT INTO #__extensions VALUES(0,"System - SEF","plugin","sef","system",0,1,0,0,"","","","",0,"0000-00-00 00:00:00",1,0);
INSERT INTO #__extensions VALUES(0,"System - Debug","plugin","debug","system",0,1,0,0,"","queries=1\nmemory=1\nlangauge=1\n\n","","",0,"0000-00-00 00:00:00",2,0);
INSERT INTO #__extensions VALUES(0,"System - Legacy","plugin","legacy","system",0,0,0,1,"","route=0\n\n","","",0,"0000-00-00 00:00:00",3,0);
INSERT INTO #__extensions VALUES(0,"System - Cache","plugin","cache","system",0,0,0,1,"","browsercache=0\ncachetime=15\n\n","","",0,"0000-00-00 00:00:00",4,0);
INSERT INTO #__extensions VALUES(0,"System - Log","plugin","log","system",0,0,0,1,"","","","",0,"0000-00-00 00:00:00",5,0);
INSERT INTO #__extensions VALUES(0,"System - Remember Me","plugin","remember","system",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",6,0);
INSERT INTO #__extensions VALUES(0,"System - Backlink","plugin","backlink","system",0,0,0,1,"","","","",0,"0000-00-00 00:00:00",7,0);
# Components
INSERT INTO #__extensions VALUES(0,"Banners","component","com_banners","",0,1,0,0,"","track_impressions=0\ntrack_clicks=0\ntag_prefix=\n\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Cache Manager","component","com_cache","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Configuration Manager","component","com_config","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Contacts","component","com_contact","",0,1,0,1,"","contact_icons=0\nicon_address=\nicon_email=\nicon_telephone=\nicon_fax=\nicon_misc=\nshow_headings=1\nshow_position=1\nshow_email=0\nshow_telephone=1\nshow_mobile=1\nshow_fax=1\nbannedEmail=\nbannedSubject=\nbannedText=\nsession=1\ncustomReply=0\n\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Articles","component","com_content","",0,1,0,1,"","show_noauth=0\nshow_title=1\nlink_titles=0\nshow_intro=1\nshow_section=0\nlink_section=0\nshow_category=0\nlink_category=0\nshow_author=1\nshow_create_date=1\nshow_modify_date=1\nshow_item_navigation=0\nshow_readmore=1\nshow_vote=0\nshow_icons=1\nshow_pdf_icon=1\nshow_print_icon=1\nshow_email_icon=1\nshow_hits=1\nfeed_summary=0\n\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Control Panel","component","com_cpanel","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Installation Manager","component","com_installer","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Language Manager","component","com_languages","",0,1,0,1,"","administrator=en-GB\nsite=en-GB","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Mail To","component","com_mailto","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Mass mail","component","com_massmail","",0,1,0,1,"","mailSubjectPrefix=\nmailBodySuffix=\n\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Media Manager","component","com_media","",0,1,0,1,"","upload_extensions=bmp,csv,doc,epg,gif,ico,jpg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,EPG,GIF,ICO,JPG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS\nupload_maxsize=10000000\nfile_path=images\nimage_path=images/stories\nrestrict_uploads=1\ncheck_mime=1\nimage_extensions=bmp,gif,jpg,png\nignore_extensions=\nupload_mime=image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave\nflash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip\nupload_mime_illegal=text/html\nenable_flash=1\n\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Menu Editor","component","com_menus","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Messaging","component","com_messages","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Modules Manager","component","com_modules","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"News Feeds","component","com_newsfeeds","",0,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Plugin Manager","component","com_plugins","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Polls","component","com_poll","",0,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Search","component","com_search","",0,1,0,1,"","enabled=0\n\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Template Manager","component","com_templates","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"User","component","com_user","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"User Manager","component","com_users","",0,1,0,1,"","allowUserRegistration=1\nnew_usertype=Registered\nuseractivation=1\nfrontend_userparams=1\n\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Web Links","component","com_weblinks","",0,1,0,0,"","show_comp_description=1\ncomp_description=\nshow_link_hits=1\nshow_link_description=1\nshow_other_cats=1\nshow_headings=1\nshow_page_title=1\nlink_target=0\nlink_icons=\n\n","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"Wrapper","component","com_wrapper","",0,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
# Modules
INSERT INTO #__extensions VALUES(0,"mod_login","module","mod_login","",1,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_popular","module","mod_popular","",1,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_latest","module","mod_latest","",1,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_stats","module","mod_stats","",1,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_unread","module","mod_unread","",1,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_online","module","mod_online","",1,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_toolbar","module","mod_toolbar","",1,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_quickicon","module","mod_quickicon","",1,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_logged","module","mod_logged","",1,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_footer","module","mod_footer","",1,1,0,1,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_menu","module","mod_menu","",1,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_submenu","module","mod_submenu","",1,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_status","module","mod_status","",1,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);
INSERT INTO #__extensions VALUES(0,"mod_title","module","mod_title","",1,1,0,0,"","","","",0,"0000-00-00 00:00:00",0,0);

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

INSERT INTO `#__menu` VALUES (1, 'mainmenu', 'Home', 'home', 'index.php?option=com_content&view=frontpage', 'component', 1, 0, 20, 0, 1, 0, '0000-00-00 00:00:00', 0, 0, 0, 3, 'num_leading_articles=1\nnum_intro_articles=4\nnum_columns=2\nnum_links=4\norderby_pri=\norderby_sec=front\nshow_pagination=2\nshow_pagination_results=1\nshow_feed_link=1\nshow_noauth=\nshow_title=\nlink_titles=\nshow_intro=\nshow_section=\nlink_section=\nshow_category=\nlink_category=\nshow_author=\nshow_create_date=\nshow_modify_date=\nshow_item_navigation=\nshow_readmore=\nshow_vote=\nshow_icons=\nshow_pdf_icon=\nshow_print_icon=\nshow_email_icon=\nshow_hits=\nfeed_summary=\npage_title=\nshow_page_title=1\npageclass_sfx=\nmenu_image=-1\nsecure=0\n\n', 0, 0, 1);

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

INSERT INTO `#__modules` VALUES (1, 'Main Menu', '', 1, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_mainmenu', 0, 0, 1, 'menutype=mainmenu\nmoduleclass_sfx=_menu\n', 1, 0, '');
INSERT INTO `#__modules` VALUES (2, 'Login', '', 1, 'login', 0, '0000-00-00 00:00:00', 1, 'mod_login', 0, 0, 1, '', 1, 1, '');
INSERT INTO `#__modules` VALUES (3, 'Popular','',3,'cpanel',0,'0000-00-00 00:00:00',1,'mod_popular',0,2,1,'',0, 1, '');
INSERT INTO `#__modules` VALUES (4, 'Recent added Articles','',4,'cpanel',0,'0000-00-00 00:00:00',1,'mod_latest',0,2,1,'ordering=c_dsc\nuser_id=0\ncache=0\n\n',0, 1, '');
INSERT INTO `#__modules` VALUES (5, 'Menu Stats','',5,'cpanel',0,'0000-00-00 00:00:00',1,'mod_stats',0,2,1,'',0, 1, '');
INSERT INTO `#__modules` VALUES (6, 'Unread Messages','',1,'header',0,'0000-00-00 00:00:00',1,'mod_unread',0,2,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (7, 'Online Users','',2,'header',0,'0000-00-00 00:00:00',1,'mod_online',0,2,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (8, 'Toolbar','',1,'toolbar',0,'0000-00-00 00:00:00',1,'mod_toolbar',0,2,1,'',1, 1, '');
INSERT INTO `#__modules` VALUES (9, 'Quick Icons','',1,'icon',0,'0000-00-00 00:00:00',1,'mod_quickicon',0,2,1,'',1,1, '');
INSERT INTO `#__modules` VALUES (10, 'Logged in Users','',2,'cpanel',0,'0000-00-00 00:00:00',1,'mod_logged',0,2,1,'',0,1, '');
INSERT INTO `#__modules` VALUES (11, 'Footer', '', 0, 'footer', 0, '0000-00-00 00:00:00', 1, 'mod_footer', 0, 0, 1, '', 1, 1, '');
INSERT INTO `#__modules` VALUES (12, 'Admin Menu','', 1,'menu', 0,'0000-00-00 00:00:00', 1,'mod_menu', 0, 2, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (13, 'Admin SubMenu','', 1,'submenu', 0,'0000-00-00 00:00:00', 1,'mod_submenu', 0, 2, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (14, 'User Status','', 1,'status', 0,'0000-00-00 00:00:00', 1,'mod_status', 0, 2, 1, '', 0, 1, '');
INSERT INTO `#__modules` VALUES (15, 'Title','', 1,'title', 0,'0000-00-00 00:00:00', 1,'mod_title', 0, 2, 1, '', 0, 1, '');

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

INSERT INTO `#__modules_menu` VALUES (1,0);

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
# Table structure for table `#__poll_data`
#

CREATE TABLE `#__poll_data` (
  `id` int(11) NOT NULL auto_increment,
  `pollid` int(11) NOT NULL default '0',
  `text` text NOT NULL default '',
  `hits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`,`text`(1))
) TYPE=MyISAM CHARACTER SET `utf8`;

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
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__polls`
#

CREATE TABLE `#__polls` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `voters` int(9) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL default '0',
  `access` int(11) NOT NULL default '0',
  `lag` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__poll_menu`
# !!!DEPRECATED!!!
#

CREATE TABLE `#__poll_menu` (
  `pollid` int(11) NOT NULL default '0',
  `menuid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`pollid`,`menuid`)
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
  `description` text NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `hits` int(11) NOT NULL default '0',
  `state` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `archived` tinyint(1) NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '1',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`,`state`,`archived`)
) TYPE=MyISAM CHARACTER SET `utf8`;

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
  UNIQUE KEY `#__section_value_value_aro` (`section_value`(100),`value`(100)),
  KEY `#__gacl_hidden_aro` (`hidden`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__core_acl_aro_map`
#

CREATE TABLE  `#__core_acl_aro_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default '0',
  `value` varchar(100) NOT NULL,
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) TYPE=MyISAM CHARACTER SET `utf8`;

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
) TYPE=MyISAM CHARACTER SET `utf8`;

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
  UNIQUE KEY `group_id_aro_id_groups_aro_map` (`group_id`,`section_value`,`aro_id`),
  INDEX `aro_id_group_id_group_aro_map` USING BTREE(`aro_id`, `group_id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__core_acl_aro_sections`
#
CREATE TABLE `#__core_acl_aro_sections` (
  `id` int(11) NOT NULL auto_increment,
  `value` varchar(230) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `core_acl_value_aro_sections` (`value`),
  KEY `core_acl_hidden_aro_sections` (`hidden`)
) TYPE=MyISAM CHARACTER SET `utf8`;

INSERT INTO `#__core_acl_aro_sections` VALUES (10,'users',1,'Users',0);

--
-- Table structure for table `#__core_acl_acl`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_acl` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `section_value` varchar(100) NOT NULL default 'system',
  `allow` int(1) unsigned NOT NULL default '0',
  `enabled` int(1) unsigned NOT NULL default '0',
  `return_value` varchar(250) default NULL,
  `note` varchar(250) default NULL,
  `updated_date` int(10) unsigned NOT NULL default '0',
  `acl_type` int(1) unsigned NOT NULL default '1' COMMENT 'Defines to what level AXOs apply to the rule',
  PRIMARY KEY  (`id`),
  KEY `core_acl_enabled_acl` (`enabled`),
  KEY `core_acl_section_value_acl` (`section_value`),
  KEY `core_acl_updated_date_acl` (`updated_date`),
  KEY `core_acl_type` USING BTREE (`acl_type`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_acl_sections`
--


CREATE TABLE IF NOT EXISTS `#__core_acl_acl_sections` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `#__core_acl_value_acl_sections` (`value`),
  KEY `core_acl_hidden_acl_sections` (`hidden`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

INSERT IGNORE INTO `#__core_acl_acl_sections` VALUES (1, 'system', 1, 'System', 0);
INSERT IGNORE INTO `#__core_acl_acl_sections` VALUES (2, 'user', 2, 'User', 0);

-- --------------------------------------------------------


--
-- Table structure for table `#__core_acl_aco`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_aco` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  `acl_type` int(1) unsigned NOT NULL default '1' COMMENT 'Defines to what level AXOs apply',
  `note` mediumtext,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `#__core_acl_section_value_aco` (`section_value`,`value`),
  KEY `core_acl_hidden_aco` (`hidden`),
  KEY `core_acl_type_section` USING BTREE (`acl_type`,`section_value`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_aco_map`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_aco_map` (
  `acl_id` int(10) unsigned NOT NULL default '0',
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_aco_sections`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_aco_sections` (
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
-- Table structure for table `#__core_acl_aro_map`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_aro_map` (
  `acl_id` int(10) unsigned NOT NULL default '0',
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS  `#__core_acl_aro_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_axo`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_axo` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `core_acl_section_value_value_axo` (`section_value`,`value`),
  KEY `core_acl_hidden_axo` (`hidden`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_axo_groups`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_axo_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '0',
  `lft` int(10) unsigned NOT NULL default '0',
  `rgt` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`,`value`),
  INDEX `core_acl_value_axo_groups` (`value`),
  KEY `core_acl_parent_id_axo_groups` (`parent_id`),
  KEY `core_acl_lft_rgt_axo_groups` (`lft`,`rgt`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

INSERT IGNORE INTO `#__core_acl_axo_groups` VALUES (1, 0, 1, 8, 'ROOT', -1);
INSERT IGNORE INTO `#__core_acl_axo_groups` VALUES (2, 1, 2, 3, 'Public', '0');
INSERT IGNORE INTO `#__core_acl_axo_groups` VALUES (3, 1, 4, 5, 'Registered', '1');
INSERT IGNORE INTO `#__core_acl_axo_groups` VALUES (4, 1, 6, 7, 'Special', '2');

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_axo_groups_map`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_axo_groups_map` (
  `acl_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_axo_map`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_axo_map` (
  `acl_id` int(10) unsigned NOT NULL default '0',
  `section_value` varchar(100) NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_axo_sections`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_axo_sections` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `value` varchar(100) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `core_acl_value_axo_sections` (`value`),
  KEY `core_acl_hidden_axo_sections` (`hidden`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

-- --------------------------------------------------------

--
-- Table structure for table `#__core_acl_groups_axo_map`
--

CREATE TABLE IF NOT EXISTS `#__core_acl_groups_axo_map` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `axo_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`axo_id`),
  KEY `#__core_acl_axo_id` (`axo_id`),
  INDEX `group_id_axo_id_groups_axo_map` USING BTREE(`axo_id`, `group_id`),
  INDEX `aro_id_group_id_groups_axo_map` USING BTREE(`group_id`, `axo_id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

# --------------------------------------------------------

#
# Table structure for table `#__contactdirectory_contacts`
#
CREATE TABLE IF NOT EXISTS `#__contactdirectory_contacts` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `published` tinyint(1) unsigned NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `params` text,
  `user_id` int(11) NOT NULL default '0',
  `access` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) DEFAULT CHARSET=utf8;

# --------------------------------------------------------

#
# Table structure for table `#__contactdirectory_con_cat_map`
#
CREATE TABLE IF NOT EXISTS `#__contactdirectory_con_cat_map` (
  `contact_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL default '0',
  PRIMARY KEY  (`contact_id`,`category_id`)
) DEFAULT CHARSET=utf8;

# --------------------------------------------------------

#
# Table structure for table `#__contactdirectory_details`
#
CREATE TABLE IF NOT EXISTS `#__contactdirectory_details` (
  `contact_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `data` text character set utf8 NOT NULL,
  `show_contact` tinyint(1) NOT NULL default '1',
  `show_directory` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`contact_id`,`field_id`)
) DEFAULT CHARSET=utf8;

# --------------------------------------------------------

#
# Table structure for table `#__contactdirectory_fields`
#
CREATE TABLE IF NOT EXISTS `#__contactdirectory_fields` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL,
  `description` mediumtext,
  `type` varchar(50) NOT NULL default 'text',
  `published` tinyint(1) unsigned NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  `checked_out` int(11) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `pos` enum('title','top','left','main','right','bottom') NOT NULL default 'main',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `params` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_acl', 0, 'Access Control', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_admin', 0, 'Admin', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_banners', 0, 'Banners', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_cache', 0, 'Cache', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_categories', 0, 'Categories', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_checkin', 0, 'Check In', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_config', 0, 'Config', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_contact', 0, 'Contact', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_content', 0, 'Content', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_installer', 0, 'Installer', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_languages', 0, 'Languages', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_mailto', 0, 'Mail To', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_massmail', 0, 'Massmail', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_media', 0, 'Media Manager', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_menus', 0, 'Menu Manager', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_messages', 0, 'Messages', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_modules', 0, 'Modules', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_newsfeeds', 0, 'Newsfeeds', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_plugins', 0, 'Plugins', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_poll', 0, 'Polls', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_search', 0, 'Search', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_sections', 0, 'Sections', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_templates', 0, 'Templates', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_trash', 0, 'Trash', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_user', 0, 'User Frontend', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_users', 0, 'Users Backend', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_weblinks', 0, 'Weblinks', 0);
INSERT INTO `#__core_acl_acl_sections` VALUES (0, 'com_wrapper', 0, 'Wrapper', 0);

INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'system', 0, 'System', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_acl', 0, 'Access Control', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_admin', 0, 'Admin', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_banners', 0, 'Banners', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_categories', 0, 'Categories', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_cache', 0, 'Cache', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_checkin', 0, 'Check In', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_config', 0, 'Config', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_contact', 0, 'Contact', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_content', 0, 'Content', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_installer', 0, 'Installer', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_languages', 0, 'Languages', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_mailto', 0, 'Mail To', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_massmail', 0, 'Massmail', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_media', 0, 'Media Manager', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_menus', 0, 'Menu Manager', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_messages', 0, 'Messages', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_modules', 0, 'Modules', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_newsfeeds', 0, 'Newsfeeds', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_plugins', 0, 'Plugins', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_poll', 0, 'Polls', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_search', 0, 'Search', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_sections', 0, 'Sections', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_templates', 0, 'Templates', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_trash', 0, 'Trash', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_user', 0, 'User Frontend', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_users', 0, 'Users Backend', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_weblinks', 0, 'Weblinks', 0);
INSERT INTO `#__core_acl_aco_sections` VALUES (0, 'com_wrapper', 0, 'Wrapper', 0);

INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_banners', 0, 'Banners', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_categories', 0, 'Categories', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_contact', 0, 'Contact', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_content', 0, 'Content', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_installer', 0, 'Installer', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_languages', 0, 'Languages', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_massmail', 0, 'Massmail', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_media', 0, 'Media Manager', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_menus', 0, 'Menu Manager', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_messages', 0, 'Messages', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_newsfeeds', 0, 'Newsfeeds', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_plugins', 0, 'Plugins', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_poll', 0, 'Polls', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_user', 0, 'User Frontend', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_users', 0, 'Users Backend', 0);
INSERT INTO `#__core_acl_axo_sections` VALUES (0, 'com_weblinks', 0, 'Weblinks', 0);

-- Type 1 Permissions

INSERT INTO `#__core_acl_aco` VALUES (0, 'system', 'login', 0, 'Login', 0, 1, 'ACO System Login Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'system', 'event.email', 0, 'Email Event', 0, 1, 'ACO System Email Event Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_acl', 'manage', 0, 'Manage', 0, 1, 'ACO Acess Control Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_banners', 'manage', 0, 'Manage', 0, 1, 'ACO Banners Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_checkin', 'manage', 0, 'Manage', 0, 1, 'ACO Checkin Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_cache', 'manage', 0, 'Manage', 0, 1, 'ACO Cache Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_config', 'manage', 0, 'Manage', 0, 1, 'ACO Config Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_categories', 'manage', 0, 'Manage', 0, 1, 'ACO Categories Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_contact', 'manage', 0, 'Manage', 0, 1, 'ACO Contacts Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_content', 'articles.manage', 0, 'Manage Article', 0, 1, 'ACO Content Manage Article Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_content', 'frontpage.manage', 0, 'Manage Frontpage', 0, 1, 'ACO Content Manage Frontpage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_installer', 'manage', 0, 'Manage', 0, 1, 'ACO Installer Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_installer', 'extension.install', 0, 'Install', 0, 1, 'ACO Installer Extension Install Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_installer', 'extension.uninstall', 0, 'Uninstall', 0, 1, 'ACO Installer Extension Uninstall Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_languages', 'manage', 0, 'Manage', 0, 1, 'ACO Language Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_massmail', 'manage', 0, 'Manage', 0, 1, 'ACO Massmail Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_media', 'manage', 0, 'Manage', 0, 1, 'ACO Media Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_menus', 'type.manage', 0, 'Manage Menu Types', 0, 1, 'ACO Menus Manage Types Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_menus', 'menus.manage', 0, 'Manage Menu Items', 0, 1, 'ACO Menus Manage Items Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_modules', 'manage', 0, 'Manage', 0, 1, 'ACO Modules Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_newsfeeds', 'manage', 0, 'Manage', 0, 1, 'ACO Newsfeeds Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_plugins', 'manage', 0, 'Manage', 0, 1, 'ACO Plugin Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_poll', 'manage', 0, 'Manage', 0, 1, 'ACO Poll Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_sections', 'manage', 0, 'Manage', 0, 1, 'ACO Sections Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_templates', 'manage', 0, 'Manage', 0, 1, 'ACO Templates Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_trash', 'manage', 0, 'Manage', 0, 1, 'ACO Trash Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_user', 'profile.edit', 0, 'Edit Profile', 0, 1, 'ACO User Edit Profile Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_users', 'manage', 0, 'Manage', 0, 1, 'ACO Users Manage Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_users', 'user.block', 0, 'Block User', 0, 1, 'ACO Users Block User Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_users', 'event.email', 0, 'Email Event', 0, 1, 'ACO Users Email Event Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_weblinks', 'manage', 0, 'Manage', 0, 1, 'ACO Weblinks Manage Desc');

-- Type 2 Permissions

INSERT INTO `#__core_acl_aco` VALUES (0, 'com_content', 'article.add', 0, 'Add Article', 0, 2, 'ACO Content Add Article Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_content', 'article.edit', 0, 'Edit Article', 0, 2, 'ACO Content Edit Article Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_content', 'article.publish', 0, 'Publish Article', 0, 2, 'ACO Content Publish Article Desc');

-- Type 3 Permissions

INSERT INTO `#__core_acl_aco` VALUES (0, 'com_content', 'article.view', 0, 'View Articles', 0, 3, 'ACO Content View Articles Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_categories', 'category.view', 0, 'View Categories', 0, 3, 'ACO Categories View Categories Desc');
INSERT INTO `#__core_acl_aco` VALUES (0, 'com_sections', 'section.view', 0, 'View Sections', 0, 3, 'ACO Sections View Sections Desc');

# Update Sites
CREATE TABLE  `#__updates` (
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

CREATE TABLE  `#__update_sites` (
  `update_site_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default '',
  `type` varchar(20) default '',
  `location` text,
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
  `description` text,
  `parent` int(11) default '0',
  `updatesite` int(11) default '0',
  PRIMARY KEY  (`categoryid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Categories';


CREATE TABLE  `#__tasks` (
  `taskid` int(10) unsigned NOT NULL auto_increment,
  `tasksetid` int(10) unsigned NOT NULL default '0',
  `data` text,
  `offset` int(11) default '0',
  `total` int(11) default '0',
  PRIMARY KEY  (`taskid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Individual tasks';

CREATE TABLE  `#__tasksets` (
  `tasksetid` int(10) unsigned NOT NULL auto_increment,
  `taskname` varchar(100) default '',
  `extensionid` int(10) unsigned default '0',
  `executionpage` text,
  `landingpage` text,
  PRIMARY KEY  (`tasksetid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Task Sets';


CREATE TABLE  `#__backups` (
  `backupid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `description` text NOT NULL,
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `location` text NOT NULL,
  `data` text NOT NULL,
  `creator` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`backupid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Backups';

CREATE TABLE  `#__backup_entries` (
  `entryid` int(10) unsigned NOT NULL auto_increment,
  `backupid` int(10) unsigned NOT NULL default '0',
  `type` varchar(20) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `source` text NOT NULL,
  `destination` text NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY  (`entryid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Backup Entries';
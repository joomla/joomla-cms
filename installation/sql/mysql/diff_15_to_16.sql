
# 1.5 to 1.6
	
-- ----------------------------------------------------------------
-- #__banners
-- ----------------------------------------------------------------

ALTER TABLE `#__banner`
 RENAME TO `#__banners`;

ALTER TABLE `#__banners`
 CHANGE COLUMN `bid` `id` INTEGER NOT NULL auto_increment;
 
ALTER TABLE `#__banners`
 CHANGE `custombannercode` `custombannercode` varchar(2048) NOT NULL;

ALTER TABLE `#__banners`
 MODIFY COLUMN `type` INTEGER NOT NULL DEFAULT '0';

ALTER TABLE `#__banners`
 CHANGE COLUMN `showBanner` `state` TINYINT(3) NOT NULL DEFAULT '0';

ALTER TABLE `#__banners`
 CHANGE COLUMN `tags` `metakey` TEXT NOT NULL AFTER `state`;

ALTER TABLE `#__banners`
 CHANGE COLUMN `date` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `params`;

ALTER TABLE `#__banners`
 DROP COLUMN `editor`;

ALTER TABLE `#__banners`
 MODIFY COLUMN `catid` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `state`;

ALTER TABLE `#__banners`
 MODIFY COLUMN `description` TEXT NOT NULL AFTER `catid`;

ALTER TABLE `#__banners`
 MODIFY COLUMN `sticky` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `description`;

ALTER TABLE `#__banners`
 MODIFY COLUMN `ordering` INTEGER NOT NULL DEFAULT 0 AFTER `sticky`;

ALTER TABLE `#__banners`
 MODIFY COLUMN `params` TEXT NOT NULL AFTER `metakey`;

ALTER TABLE `#__banners`
 ADD COLUMN `own_prefix` TINYINT(1) NOT NULL DEFAULT '0' AFTER `params`;

ALTER TABLE `#__banners`
 ADD COLUMN `metakey_prefix` VARCHAR(255) NOT NULL DEFAULT '' AFTER `own_prefix`;

ALTER TABLE `#__banners`
 ADD COLUMN `purchase_type` TINYINT NOT NULL DEFAULT '-1' AFTER `metakey_prefix`;

ALTER TABLE `#__banners`
 ADD COLUMN `track_clicks` TINYINT NOT NULL DEFAULT '-1' AFTER `purchase_type`;

ALTER TABLE `#__banners`
 ADD COLUMN `track_impressions` TINYINT NOT NULL DEFAULT '-1' AFTER `track_clicks`;

ALTER TABLE `#__banners`
 ADD COLUMN `reset` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish_down`;

ALTER TABLE `#__banners`
 ADD COLUMN `language` char(7) NOT NULL DEFAULT '' AFTER `created`;

UPDATE `#__banners`
 SET `type`=1 WHERE TRIM(`custombannercode`)!='';

UPDATE `#__banners`
 SET `params` = concat( '"flash":{"', REPLACE( REPLACE( REPLACE( TRIM( '\n' FROM `params` ) , '=', '":"' ) , '\n', '","' ) , '\r', '' ) , '"},' ) WHERE TRIM( `params` ) != '';

UPDATE `#__banners`
 SET `params` = '"flash":{"height":"0","width":"0"},' WHERE TRIM( `params` ) = '';

UPDATE `#__banners`
 SET `params` = CONCAT('{"custom":{"bannercode":"',REPLACE(`custombannercode`,'"','\\"'),'"},"alt":{"alt":""},',`params`,'"image":{"url":"',`imageurl`,'"}}');

ALTER TABLE `#__banners`
 DROP COLUMN `custombannercode`;

ALTER TABLE `#__banners`
 DROP COLUMN `imageurl`;

ALTER TABLE `#__banners`
 DROP INDEX `viewbanner`;

ALTER TABLE `#__banners`
 ADD INDEX `idx_own_prefix` (`own_prefix`);

ALTER TABLE `#__banners`
 ADD INDEX `idx_metakey_prefix` (`metakey_prefix`);

ALTER TABLE `#__banners`
 ADD INDEX `idx_language` (`language`);

-- ----------------------------------------------------------------
-- #__banner_clients
-- ----------------------------------------------------------------

ALTER TABLE `#__bannerclient`
 RENAME TO `#__banner_clients`;

ALTER TABLE `#__banner_clients`
 CHANGE COLUMN `cid` `id` INTEGER NOT NULL auto_increment;

ALTER TABLE `#__banner_clients`
 DROP COLUMN `editor`;

ALTER TABLE `#__banner_clients`
 ADD COLUMN `state` TINYINT(3) NOT NULL DEFAULT '0' AFTER `extrainfo`;

ALTER TABLE `#__banner_clients`
 ADD COLUMN `metakey` TEXT NOT NULL;

ALTER TABLE `#__banner_clients`
 ADD COLUMN `own_prefix` TINYINT NOT NULL DEFAULT '0';

ALTER TABLE `#__banner_clients`
 ADD COLUMN `metakey_prefix` VARCHAR(255) NOT NULL DEFAULT '';

ALTER TABLE `#__banner_clients`
 ADD COLUMN `purchase_type` TINYINT NOT NULL DEFAULT '-1';

ALTER TABLE `#__banner_clients`
 ADD COLUMN `track_clicks` TINYINT NOT NULL DEFAULT '-1';

ALTER TABLE `#__banner_clients`
 ADD COLUMN `track_impressions` TINYINT NOT NULL DEFAULT '-1';

ALTER TABLE `#__banner_clients`
 ADD INDEX `idx_own_prefix` (`own_prefix`);

ALTER TABLE `#__banner_clients`
 ADD INDEX `idx_metakey_prefix` (`metakey_prefix`);

UPDATE `#__banner_clients`
 SET `state`=1;

-- ----------------------------------------------------------------
-- #__banner_tracks
-- ----------------------------------------------------------------

ALTER TABLE `#__bannertrack`
 RENAME TO `#__banner_tracks`;

ALTER TABLE `#__banner_tracks`
 ADD COLUMN `count` INTEGER UNSIGNED NOT NULL DEFAULT '0';

INSERT `#__banner_tracks`
 SELECT `track_date`,`track_type`,`banner_id`,count('*') AS `count`
 FROM `#__banner_tracks`
 GROUP BY `track_date`,`track_type`,`banner_id`;

DELETE FROM `#__banner_tracks`
 WHERE `count`=0;

ALTER TABLE `#__banner_tracks`
 ADD PRIMARY KEY (`track_date`, `track_type`, `banner_id`);

ALTER TABLE `#__banner_tracks`
 ADD INDEX `idx_track_date` (`track_date`);

ALTER TABLE `#__banner_tracks`
 ADD INDEX `idx_track_type` (`track_type`);

ALTER TABLE `#__banner_tracks`
 ADD INDEX `idx_banner_id` (`banner_id`);

-- ----------------------------------------------------------------
-- #_categories
-- ----------------------------------------------------------------

ALTER TABLE `#__categories`
 MODIFY COLUMN `description` VARCHAR(5120) NOT NULL DEFAULT '';

ALTER TABLE `#__categories`
 MODIFY COLUMN `params` VARCHAR(2048) NOT NULL DEFAULT '';

ALTER TABLE `#__categories`
 ADD COLUMN `lft` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set lft.' AFTER `parent_id`;

ALTER TABLE `#__categories`
 ADD COLUMN `rgt` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set rgt.' AFTER `lft`;

 ALTER TABLE `#__categories`
 ADD COLUMN   `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.' AFTER `id`;
 
ALTER TABLE `#__categories`
 ADD COLUMN `level` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `rgt`;

ALTER TABLE `#__categories`
 ADD COLUMN `path` VARCHAR(1024) NOT NULL DEFAULT '' AFTER `level`;

ALTER TABLE `#__categories`
 ADD COLUMN   `extension` varchar(50) NOT NULL default '' AFTER `path`;

 UPDATE #__categories
 SET extension = section WHERE SUBSTR(section,1,3) = 'com';
UPDATE #__categories
 SET extension = 'com_content' WHERE SUBSTR(section,1,3) != 'com';

ALTER TABLE `#__categories`
 ADD COLUMN `note` VARCHAR(255) NOT NULL DEFAULT '' AFTER `alias`;

ALTER TABLE `#__categories`
 ADD COLUMN `metadesc` VARCHAR(1024) NOT NULL COMMENT 'The meta description for the page.' AFTER `params`;

ALTER TABLE `#__categories`
 ADD COLUMN `metakey` VARCHAR(1024) NOT NULL COMMENT 'The meta keywords for the page.' AFTER `metadesc`;

ALTER TABLE `#__categories`
 ADD COLUMN `metadata` VARCHAR(2048) NOT NULL COMMENT 'JSON encoded metadata properties.' AFTER `metakey`;

ALTER TABLE `#__categories`
 ADD COLUMN `created_user_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `metadata`;

ALTER TABLE `#__categories`
 ADD COLUMN `created_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_user_id`;

ALTER TABLE `#__categories`
 ADD COLUMN `modified_user_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `created_time`;

ALTER TABLE `#__categories`
 ADD COLUMN `modified_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `modified_user_id`;

ALTER TABLE `#__categories`
 ADD COLUMN `hits` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_time`;

ALTER TABLE `#__categories`
 ADD COLUMN `language` CHAR(7) NOT NULL AFTER `hits`;

ALTER TABLE `#__categories`
 ADD INDEX idx_alias(`alias`);

ALTER TABLE `#__categories`
 ADD INDEX idx_path(`path`);

ALTER TABLE `#__categories`
 ADD INDEX idx_left_right(`lft`, `rgt`);

ALTER TABLE `#__categories`
 ADD INDEX `idx_language` (`language`);

ALTER TABLE `#__categories`
 DROP COLUMN `ordering`;
 
ALTER TABLE `#__categories`
 DROP COLUMN `image`;
 
ALTER TABLE `#__categories`
 DROP COLUMN `image_position`;
 
ALTER TABLE `#__categories`
 DROP COLUMN `editor`;
 
ALTER TABLE `#__categories`
 DROP COLUMN `count`;
 
ALTER TABLE `#__categories`
 DROP COLUMN `name`;
UPDATE #__categories SET  parent_id=0
	WHERE SUBSTR(section,1,3)='com';
UPDATE #__categories SET  parent_id=section
	WHERE SUBSTR(section,1,3) !='com';
INSERT INTO `#__categories` (parent_id,lft,rgt,level,path,extension,title,alias,note,description,published,checked_out,checked_out_time,access,params,metadesc,metakey,metadata,created_user_id,created_time,modified_user_id,modified_time,hits,language)
VALUES
( 0, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:26:37', 0, '0000-00-00 00:00:00', 0, '*'),
( 0, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"target":"","image":"","foobar":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:27:35', 0, '0000-00-00 00:00:00', 0, '*'),
( 0, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:27:57', 0, '0000-00-00 00:00:00', 0, '*'),
( 0, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:28:15', 0, '0000-00-00 00:00:00', 0, '*'),
( 0, 9, 10, 1, 'uncategorised', 'com_weblinks', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:28:33', 0, '0000-00-00 00:00:00', 0, '*');
INSERT INTO `#__categories` (parent_id,lft,rgt,level,path,extension,title,alias,note,description,published,checked_out,checked_out_time,access,params,metadesc,metakey,metadata,created_user_id,created_time,modified_user_id,modified_time,hits,language)
SELECT
'0',				# parent_id
'',					# lft
'',					# rgt
'1',				# level
alias,				# path
'com_content',		# extension
title,				# title
alias,				# alias
id,					# note
description,		# description
published,			# published
checked_out,		# checked_out
checked_out_time,	# checked_out_time
access,				# access 
params,				# params
'',					# metadesc
'',					# metakey
'',					# metadata
'',					# created_user_id
'',					# created_time
'',					# modified_user_id,
'',					# modified_time
'',					# hits
''					# language
FROM #__sections; 


INSERT INTO `#__categories`
(asset_id,parent_id,lft,rgt,level,path,extension,title,alias,note,description,published,checked_out,checked_out_time,access,params,metadesc,metakey,metadata,created_user_id,created_time,modified_user_id,modified_time,hits,language)
VALUES
( 0, 0, 0, 0, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-10-18 16:07:09', 0, '0000-00-00 00:00:00', 0, '*');
UPDATE  #__categories
 SET parent_id=LAST_INSERT_ID() WHERE parent_id=0 AND id !=LAST_INSERT_ID() ;

-- TODO: Merge from sections and add uncategorised nodes.

-- ----------------------------------------------------------------
-- #__components
-- ----------------------------------------------------------------

ALTER TABLE `#__components`
 MODIFY COLUMN `enabled` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1;

UPDATE `#__components`
 SET admin_menu_link = 'option=com_content&view=articles'
 WHERE link = 'option=com_content';

INSERT INTO `#__components` VALUES
 (null, 'Categories', '', 0, 0, 'option=com_categories&view=categories&extension=com_content', 'com_content_Categories', 'com_content', 2, '', 1, '{}', 1),
 (null, 'Redirects', '', 0, 0, 'option=com_redirect', 'Manage Redirects', 'com_redirect', 0, 'js/ThemeOffice/component.png', 1, '{}', 1),
 (null, 'Checkin', '', 0, 0, 'option=com_checkin', 'Checkin', 'com_checkin', 0, 'js/ThemeOffice/component.png', 1, '{}', 1);

UPDATE `#__components` AS a
 LEFT JOIN `#__components` AS b ON b.link='option=com_content'
 SET a.parent = b.id
 WHERE a.link = ''
  AND a.option = 'com_content';


-- ----------------------------------------------------------------
-- #_contact_details
-- ----------------------------------------------------------------
 ALTER TABLE `#__contact_details`
  ADD COLUMN `sortname1` varchar(255) NOT NULL,
  ADD COLUMN `sortname2` varchar(255) NOT NULL,
  ADD COLUMN `sortname3` varchar(255) NOT NULL,
  ADD COLUMN `language` char(7) NOT NULL,
  ADD COLUMN  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  ADD COLUMN   `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN   `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  ADD COLUMN   `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  ADD COLUMN   `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  ADD COLUMN   `metakey` text NOT NULL,
  ADD COLUMN   `metadesc` text NOT NULL,
  ADD COLUMN   `metadata` text NOT NULL,
  ADD COLUMN   `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  ADD COLUMN   `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  ADD COLUMN   `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  ADD COLUMN   `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  CHANGE `published` `published` tinyint(1) NOT NULL DEFAULT '0',
  DROP INDEX `catid`,
  ADD  KEY `idx_access` (`access`),
  ADD  KEY `idx_checkout` (`checked_out`),
  ADD  KEY `idx_published` (`published`),
  ADD  KEY `idx_catid` (`catid`),
  ADD  KEY `idx_createdby` (`created_by`),
  ADD  KEY `idx_featured_catid` (`featured`,`catid`),
  ADD  KEY `idx_language` (`language`),
  ADD  KEY `idx_xreference` (`xreference`);
-- ----------------------------------------------------------------
-- #_content
-- ----------------------------------------------------------------

ALTER TABLE `#__content`
 ADD COLUMN `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to the #_assets table.' AFTER `id`;

ALTER TABLE `#__content`
 ADD COLUMN `featured` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Set if article is featured.' AFTER `metadata`;

ALTER TABLE `#__content`
 ADD INDEX idx_featured_catid(`featured`, `catid`);

ALTER TABLE `#__content`
 ADD COLUMN `language` CHAR(7) NOT NULL COMMENT 'The language code for the article.' AFTER `featured`;

ALTER TABLE `#__content`
 ADD COLUMN `xreference` VARCHAR(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.' AFTER `language`;

ALTER TABLE `#__content`
 ADD INDEX idx_language(`language`);

ALTER TABLE `#__content`
 ADD INDEX idx_xreference(`xreference`);

UPDATE `#__content` AS a
 SET a.featured = 1
 WHERE a.id IN (
 	SELECT f.content_id
 	FROM `#__content_frontpage` AS f
 );

 ALTER TABLE `#__content` CHANGE `attribs` `attribs` VARCHAR( 5120 ) NOT NULL;

-- ----------------------------------------------------------------
-- #_extensions (new) and migration
-- ----------------------------------------------------------------

CREATE TABLE  `#__extensions` (
  `extension_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  `element` varchar(100) NOT NULL default '',
  `folder` varchar(100) NOT NULL default '',
  `client_id` tinyint(3) NOT NULL default '0',
  `enabled` tinyint(3) NOT NULL default '1',
  `access` tinyint(3) unsigned NOT NULL default '0',
  `protected` tinyint(3) NOT NULL default '0',
  `manifest_cache` text NOT NULL,
  `params` text NOT NULL,
  `custom_data` text NOT NULL,
  `system_data` text NOT NULL,
  `checked_out` int(10) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) default '0',
  `state` int(11) NOT NULL default '0',
  PRIMARY KEY  (`extension_id`),
  KEY `type_element` (`type`,`element`),
  KEY `element_clientid` (`element`,`client_id`),
  KEY `element_folder_clientid` (`element`,`folder`,`client_id`),
  KEY `element_folder` (`element`,`folder`),
  KEY `extension` (`type`,`element`,`folder`,`client_id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

TRUNCATE TABLE #__extensions;
INSERT INTO #__extensions (name,type,element,folder,client_id,enabled,access,protected,
	manifest_cache,params,custom_data,system_data,checked_out,checked_out_time,ordering,
	state) SELECT
     name,						# name
     'plugin',					# type
     element,					# element
     folder,                    # folder
     client_id,                 # client_id
     published,                 # enabled
     access,                    # access
     iscore,                    # protected
     '',                        # manifest_cache
     params,                    # params
     '',                        # custom data
     '',						# system data
     checked_out,            	# checked_out
     checked_out_time,         	# checked_out_time
     ordering,                  # ordering
     0							# state
     FROM #__plugins;         	# #__extensions replaces the old #_plugins table

 INSERT INTO #__extensions (name,type,element,folder,client_id,enabled,access,protected,
	manifest_cache,params,custom_data,system_data,checked_out,checked_out_time,ordering,
	state)
	SELECT
     name,						# name
     'component',				# type
     `option`,					# element
     '',                        # folder
     0,                         # client id (unused for components)
     enabled,                   # enabled
     0,                         # access
     iscore,                    # protected
     '',                        # manifest cache
     params,                    # params
     '',                        # custom data
     '',						# system data
     '0',                       # checked_out
     '0000-00-00 00:00:00',     # checked_out_time
     0,                         # ordering
     0							# state
     FROM #__components			# #__extensions replaces #__components for install uninstall
                                # component menu selection still utilises the #__components table
     WHERE parent = 0;          # only get top level entries

 INSERT INTO #__extensions (name,type,element,folder,client_id,enabled,access,protected,
	manifest_cache,params,custom_data,system_data,checked_out,checked_out_time,ordering,
	state)
	SELECT DISTINCT
     module,                    # name
     'module',                  # type
     `module`,                  # element
     '',                        # folder
     client_id,                 # client id
     1,                         # enabled (module instances may be enabled/disabled in #__modules)
     0,                         # access (module instance access controlled in #__modules)
     iscore,                    # protected
     '',                        # manifest cache
     '',                        # params (module instance params controlled in #__modules)
     '',                        # custom data
     '',						# system data
     '0',                       # checked_out (module instance, see #__modules)
     '0000-00-00 00:00:00',     # checked_out_time (module instance, see #__modules)
     0,                         # ordering (module instance, see #__modules)
     0							# state
     FROM #__modules			# #__extensions provides the install/uninstall control for modules
     WHERE id IN (SELECT id FROM #__modules GROUP BY module ORDER BY id);

	 
	 
-- rename mod_newsflash to mod_articles_news
UPDATE `#__extensions` SET `name` = 'mod_articles_news', `element` = 'mod_articles_news' WHERE `name` = 'mod_newsflash';

-- New extensions

INSERT INTO `#__extensions` ( `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
( 'atomic', 'template', 'atomic', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
( 'rhuk_milkyway', 'template', 'rhuk_milkyway', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
( 'bluestork', 'template', 'bluestork', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
( 'beez_20', 'template', 'beez_20', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
( 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
( 'Beez5', 'template', 'beez5', '', 0, 1, 1, 0, 'a:11:{s:6:"legacy";b:1;s:4:"name";s:5:"Beez5";s:4:"type";s:8:"template";s:12:"creationDate";s:11:"21 May 2010";s:6:"author";s:12:"Angie Radtke";s:9:"copyright";s:72:"Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:23:"a.radtke@derauftritt.de";s:9:"authorUrl";s:26:"http://www.der-auftritt.de";s:7:"version";s:5:"1.6.0";s:11:"description";s:22:"A Easy Version of Beez";s:5:"group";s:0:"";}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"BEEZ 2.0","sitedescription":"Your site name","navposition":"center","html5":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

# Languages
INSERT INTO `#__extensions` ( `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
( 'English (United Kingdom)', 'language', 'en-GB', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
( 'English (United Kingdom)', 'language', 'en-GB', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
( 'XXTestLang', 'language', 'xx-XX', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
( 'XXTestLang', 'language', 'xx-XX', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO `#__extensions` (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
( 'Joomla! CMS', 'file', 'joomla', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
('plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 1, '', '{"autowebpage":"","category":"26","autopublish":"0"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
('plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 1, '', '{"register-require_address1":"0","register-require_address2":"0","register-require_city":"0","register-require_region":"0","register-require_country":"0","register-require_postal_code":"0","register-require_phone":"0","register-require_website":"0","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
('plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 1, '', '{}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
('plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 1, '', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
('plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 1, '', '{"linenumbers":"0","tabmode":"indent"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
('plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 0, '', '{}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
('mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
('mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
('mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
('mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
('mod_weblinks', 'module', 'mod_weblinks', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

-- ----------------------------------------------------------------
-- #__languages (new)
-- ----------------------------------------------------------------

CREATE TABLE `#__languages` (
  `lang_id` int(11) unsigned NOT NULL auto_increment,
  `lang_code` char(7) NOT NULL,
  `title` varchar(50) NOT NULL,
  `title_native` varchar(50) NOT NULL,
  `description` varchar(512) NOT NULL,
  `published` int(11) NOT NULL default '0',
  PRIMARY KEY  (`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__languages` (`lang_code`,`title`,`title_native`,`description`,`published`)
VALUES
	('en_GB','English (UK)','English (UK)','',1);


-- ----------------------------------------------------------------
-- #__menu
-- ----------------------------------------------------------------

ALTER TABLE `#__menu`
 DROP COLUMN `sublevel`,
 DROP COLUMN `pollid`,
 DROP COLUMN `utaccess`;

ALTER TABLE `#__menu`
 MODIFY COLUMN `menutype` VARCHAR(24) NOT NULL COMMENT 'The type of menu this item belongs to. FK to #__menu_types.menutype';

ALTER TABLE `#__menu`
 CHANGE COLUMN `name` `title` VARCHAR(255) NOT NULL COMMENT 'The display title of the menu item.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `alias` VARCHAR(255) NOT NULL COMMENT 'The SEF alias of the menu item.';

ALTER TABLE `#__menu`
 ADD COLUMN `note` VARCHAR(255) NOT NULL DEFAULT '' AFTER `alias`;

ALTER TABLE `#__menu`
 MODIFY COLUMN `link` VARCHAR(1024) NOT NULL COMMENT 'The actually link the menu item refers to.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `type` VARCHAR(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator';

ALTER TABLE `#__menu`
 MODIFY COLUMN `published` TINYINT NOT NULL DEFAULT 0 COMMENT 'The published state of the menu link.';

ALTER TABLE `#__menu`
 CHANGE COLUMN `parent` `parent_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The parent menu item in the menu tree.';

ALTER TABLE `#__menu`
 ADD COLUMN `level` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The relative level in the tree.' AFTER `parent_id`;

ALTER TABLE `#__menu`
 CHANGE COLUMN `componentid` `component_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to #__components.id';

ALTER TABLE `#__menu`
 MODIFY COLUMN `ordering` INTEGER NOT NULL DEFAULT 0 COMMENT 'The relative ordering of the menu item in the tree.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `checked_out` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to #__users.id';

ALTER TABLE `#__menu`
 MODIFY COLUMN `checked_out_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The time the menu item was checked out.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `browserNav` TINYINT NOT NULL DEFAULT 0 COMMENT 'The click behaviour of the link.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `access` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The access level required to view the menu item.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `params` VARCHAR(10240) NOT NULL COMMENT 'JSON encoded data for the menu item.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `lft` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set lft.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `rgt` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set rgt.';

ALTER TABLE `#__menu`
 MODIFY COLUMN `home` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Indicates if this menu item is the home or default page.';

ALTER TABLE `#__menu`
 ADD COLUMN `path` VARCHAR(1024) NOT NULL COMMENT 'The computed path of the menu item based on the alias field.' AFTER `alias`;

ALTER TABLE `#__menu`
 ADD COLUMN `template_style_id` int(11) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__menu`
 ADD COLUMN `language` char(7) NOT NULL DEFAULT '' AFTER `home`;

ALTER TABLE `#__menu` ADD COLUMN `client_id` TINYINT(4) NOT NULL DEFAULT 0 AFTER `language`;

ALTER TABLE `#__menu`
 ADD INDEX idx_language(`language`);

ALTER TABLE `#__menu` ADD UNIQUE `idx_alias_parent_id` (`client_id`,`parent_id`,`alias`);

INSERT INTO `#__menu` VALUES
	('','Menu_Item_Root','root','','','','',1,0,0,0,0,0,'0000-00-00 00:00:00',0,0,'',0,'',0,217,0,'*');


-- TODO: Need to devise how to shift the parent_id's of the existing menus to relate to the new root.
-- UPDATE `#__menu`
--  SET `parent_id` = (SELECT `id` FROM `#__menu` WHERE `alias` = 'root')
--  WHERE `alias` != 'root';

-- ----------------------------------------------------------------
-- #__menu_types
-- ----------------------------------------------------------------

ALTER TABLE `#__menu_types`
 MODIFY COLUMN `menutype` VARCHAR(24) NOT NULL,
 MODIFY COLUMN `title` VARCHAR(48) NOT NULL,
 DROP INDEX `menutype`;

-- ----------------------------------------------------------------
-- #__messages
-- ----------------------------------------------------------------

ALTER TABLE `#__messages`
 CHANGE `subject` `subject` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__messages`
 CHANGE `state` `state` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `#__messages`
 CHANGE `priority` `priority` tinyint(1) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__messages`
 CHANGE `folder_id` `folder_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0';

-- ----------------------------------------------------------------
-- #__modules
-- ----------------------------------------------------------------

ALTER TABLE `#__modules`
 DROP `numnews`;

ALTER TABLE `#__modules`
 DROP `control`;

ALTER TABLE `#__modules`
 DROP `iscore`;

ALTER TABLE `#__modules`
 ADD COLUMN `note` VARCHAR(255) NOT NULL DEFAULT '' AFTER `title`;

ALTER TABLE `#__modules`
 ADD COLUMN `language` CHAR(7) NOT NULL AFTER `client_id`;

ALTER TABLE `#__modules`
 ADD INDEX `idx_language` (`language`);

ALTER TABLE `#__modules`
 CHANGE `title` `title` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `#__modules`
 CHANGE `params` `params` varchar(5120) NOT NULL DEFAULT '';

ALTER TABLE `#__modules`
 ADD COLUMN `publish_up` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `checked_out_time`;

ALTER TABLE `#__modules`
 ADD COLUMN `publish_down` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `publish_up`;

UPDATE `#__modules`
 SET `module` = 'mod_menu'
 WHERE `module` = 'mod_mainmenu';

-- ----------------------------------------------------------------
-- #__newsfeeds
-- ----------------------------------------------------------------

ALTER TABLE `#__newsfeeds`
 CHANGE `id` `id` integer(11) UNSIGNED NOT NULL auto_increment;

ALTER TABLE `#__newsfeeds`
 CHANGE `name` `name` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `#__newsfeeds`
 CHANGE `alias` `alias` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `#__newsfeeds`
 CHANGE `link` `link` varchar(200) NOT NULL DEFAULT '';

ALTER TABLE `#__newsfeeds`
 CHANGE `checked_out` `checked_out` integer(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__newsfeeds`
 ADD `access` tinyint UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__newsfeeds`
 ADD `language` char(7) NOT NULL DEFAULT '';

ALTER TABLE `#__newsfeeds`
ADD `params` TEXT NOT NULL;

ALTER TABLE `#__newsfeeds`
 ADD COLUMN   `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN   `created_by` int(10) unsigned NOT NULL DEFAULT '0',
 ADD COLUMN   `created_by_alias` varchar(255) NOT NULL DEFAULT '',
 ADD COLUMN   `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN   `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
 ADD COLUMN   `metakey` text NOT NULL,
 ADD COLUMN   `metadesc` text NOT NULL,
 ADD COLUMN   `metadata` text NOT NULL,
 ADD COLUMN   `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
 ADD COLUMN   `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN   `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 DROP INDEX `catid`,
 DROP INDEX `published`,
 ADD KEY `idx_access` (`access`),
 ADD KEY `idx_checkout` (`checked_out`),
 ADD KEY `idx_state` (`published`),
 ADD KEY `idx_catid` (`catid`),
 ADD KEY `idx_createdby` (`created_by`),
 ADD KEY `idx_language` (`language`),
 ADD KEY `idx_xreference` (`xreference`);

-- ----------------------------------------------------------------
-- #__plugins
-- ----------------------------------------------------------------

-- ----------------------------------------------------------------
-- #__schemas
-- ----------------------------------------------------------------

CREATE TABLE `#__schemas` (
  `extensionid` int(11) NOT NULL,
  `versionid` varchar(20) NOT NULL,
  PRIMARY KEY (`extensionid`, `versionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------------
-- #__session
-- ----------------------------------------------------------------

ALTER TABLE `#__session`
 MODIFY COLUMN `session_id` VARCHAR(32);

ALTER TABLE `#__session`
 MODIFY COLUMN `guest` TINYINT UNSIGNED DEFAULT 1;

ALTER TABLE `#__session`
 MODIFY COLUMN `client_id` TINYINT UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `#__session`
 MODIFY COLUMN `data` VARCHAR(20480);

-- ----------------------------------------------------------------
-- #__template_styles
-- ----------------------------------------------------------------
-- --------------------------------------------------------
--
-- Table structure for table `#__template_styles`
--

CREATE TABLE IF NOT EXISTS `#__template_styles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(50) NOT NULL DEFAULT '',
  `client_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `home` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `params` varchar(10240) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_template` (`template`),
  KEY `idx_home` (`home`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=115 ;

INSERT INTO #__template_styles (`template`, `client_id`)
SELECT `#__templates_menu`.`template`, `#__templates_menu`.`client_id`
FROM `#__templates_menu`;


-- ----------------------------------------------------------------
-- #__updates (new)
-- ----------------------------------------------------------------

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

-- ----------------------------------------------------------------
-- #__update_sites (new)
-- ----------------------------------------------------------------

CREATE TABLE  `#__update_sites` (
  `update_site_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default '',
  `type` varchar(20) default '',
  `location` text,
  `enabled` int(11) default '0',
  PRIMARY KEY  (`update_site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Sites';

-- ----------------------------------------------------------------
-- #__update_sites_extensions (new)
-- ----------------------------------------------------------------

CREATE TABLE `#__update_sites_extensions` (
  `update_site_id` INT DEFAULT 0,
  `extension_id` INT DEFAULT 0,
  INDEX `newindex`(`update_site_id`, `extension_id`)
) ENGINE = MYISAM CHARACTER SET utf8 COMMENT = 'Links extensions to update sites';

-- ----------------------------------------------------------------
-- #__update_categories (new)
-- ----------------------------------------------------------------

CREATE TABLE  `#__update_categories` (
  `categoryid` int(11) NOT NULL auto_increment,
  `name` varchar(20) default '',
  `description` text,
  `parent` int(11) default '0',
  `updatesite` int(11) default '0',
  PRIMARY KEY  (`categoryid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Categories';

-- ----------------------------------------------------------------
-- #__weblinks
-- ----------------------------------------------------------------
ALTER TABLE `#__weblinks`
 CHANGE COLUMN `published` `state` tinyint (1) NOT NULL DEFAULT '0';

ALTER TABLE `#__weblinks`
 ADD COLUMN `access` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `approved`;

ALTER TABLE `#__weblinks`
 ADD `language` char(7) NOT NULL DEFAULT '';

ALTER TABLE `#__weblinks`
 ADD COLUMN   `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN   `created_by` int(10) unsigned NOT NULL DEFAULT '0',
 ADD COLUMN   `created_by_alias` varchar(255) NOT NULL DEFAULT '',
 ADD COLUMN   `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN   `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
 ADD COLUMN   `metakey` text NOT NULL,
 ADD COLUMN   `metadesc` text NOT NULL,
 ADD COLUMN   `metadata` text NOT NULL,
 ADD COLUMN   `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if link is featured.',
 ADD COLUMN   `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
 ADD COLUMN   `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN   `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 DROP  KEY `catid`,
 ADD   KEY `idx_access` (`access`),
 ADD   KEY `idx_checkout` (`checked_out`),
 ADD   KEY `idx_state` (`state`),
 ADD   KEY `idx_catid` (`catid`),
 ADD   KEY `idx_createdby` (`created_by`),
 ADD   KEY `idx_featured_catid` (`featured`,`catid`),
 ADD   KEY `idx_language` (`language`),
 ADD   KEY `idx_xreference` (`xreference`);
-- ----------------------------------------------------------------
-- Reconfigure the admin module permissions
-- ----------------------------------------------------------------

UPDATE `#__categories`
 SET access = access + 1;

UPDATE `#__contact_details`
 SET access = access + 1;

UPDATE `#__content`
 SET access = access + 1;

UPDATE `#__menu`
 SET access = access + 1;

UPDATE `#__modules`
 SET access = access + 1;

UPDATE `#__plugins`
 SET access = access + 1;

UPDATE `#__sections`
 SET access = access + 1;

-- ----------------------------------------------------------------
-- Table drops.
-- ----------------------------------------------------------------

DROP TABLE `#__groups`;

-- Note, devise the migration
DROP TABLE `#__core_acl_aro`;
DROP TABLE `#__core_acl_aro_map`;
DROP TABLE `#__core_acl_aro_groups`;
DROP TABLE `#__core_acl_groups_aro_map`;
DROP TABLE `#__core_acl_aro_sections`;


-- ----------------------------------------------------------------
-- Add an entry to the extensions (for the app)
-- Add an entry to the schema table (for this migration)
-- ----------------------------------------------------------------
INSERT INTO #__extensions (name, type, element, protected) VALUES ('Joomla! CMS', 'package', 'joomla', 1);
INSERT INTO #__schemas VALUES(LAST_INSERT_ID(), '20090622');

-- Parameter conversions todo

DROP TABLE `#__core_log_items`;
DROP TABLE `#__stats_agents`;


-- ----------------------------------------------------------------
-- #__assets
-- ----------------------------------------------------------------

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
INSERT INTO `#__assets` (`parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)       
SELECT
     '1',						# parent_id
     '',						# lft
     '',						# rgt
     '2',	                    # level
     `option`, 					# name
     name,                 		# title
	'{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}' # rules
     FROM #__components        # Each component is an asset with a parent of 0.	
	WHERE parent = 0; 
UPDATE #__assets 
	SET rules='{}' WHERE NAME='com_admin'  OR NAME='com_wrapper' OR
		NAME='com_login'  OR NAME='com_cpanel';
UPDATE #__assets
	SET rules='{"core.admin":{"7":1},"core.manage":{"7":1},"core.create":{"7":1},"core.delete":{"7":1},"core.edit":{"7":1},"core.edit.state":{"7":1}}'
    WHERE NAME= 'com_mailto' OR NAME='com_massmail' OR NAME='com_config';
INSERT INTO #__assets (`parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) 
SELECT
     '',						# parent_id
     '',						# lft
     '',						# rgt
     '2',	                    # level
     CONCAT(extension,'.category.',id), # name
     section,                 	# title
     '{}'	                    # rules
     FROM #__categories        # Each existing category is an asset with a parent of its component or section.
	WHERE SUBSTR(section,1,3) = 'com';
INSERT INTO #__assets (`parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) 
SELECT
     '',						# parent_id
     '',						# lft
     '',						# rgt
     '3',	                    # level
     CONCAT('com_content.article.',id),   # name
     title,                 	# title
     '{}'	                    # rules
     FROM #__content          	# Uncategorized articles becomes assets in the uncategorized category.
								#other articles becomes assets with parent of their category.
	 ;

-- We can now drop the section column, since we have done the queries above.

#ALTER TABLE `#__categories` DROP COLUMN `section`;


-- issue http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=22606
ALTER TABLE `#__usergroups`
	DROP INDEX `idx_usergroup_title_lookup`,
	ADD INDEX `idx_usergroup_title_lookup` ( `title` ),
	ADD UNIQUE `idx_usergroup_parent_title_lookup` ( `parent_id` , `title` ) 
;


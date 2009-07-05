# $Id$

# 1.5 to 1.6

-- ----------------------------------------------------------------
-- jos_categories
-- ----------------------------------------------------------------

ALTER TABLE `jos_categories`
 MODIFY COLUMN `description` VARCHAR(5120) NOT NULL DEFAULT '';

ALTER TABLE `jos_categories`
 MODIFY COLUMN `params` VARCHAR(2048) NOT NULL DEFAULT '';
 
ALTER TABLE `jos_categories`
 ADD COLUMN `lft` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set lft.' AFTER `parent_id`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `rgt` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set rgt.' AFTER `lft`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `level` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `rgt`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `path` VARCHAR(1024) NOT NULL DEFAULT '' AFTER `level`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `metadesc` VARCHAR(1024) NOT NULL COMMENT 'The meta description for the page.' AFTER `params`;

ALTER TABLE `jos_categories`
 ADD COLUMN `metakey` VARCHAR(1024) NOT NULL COMMENT 'The meta keywords for the page.' AFTER `metadesc`;

ALTER TABLE `jos_categories`
 ADD COLUMN `metadata` VARCHAR(2048) NOT NULL COMMENT 'JSON encoded metadata properties.' AFTER `metakey`;

ALTER TABLE `jos_categories`
 ADD COLUMN `created_user_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `metadata`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `created_time` TIMESTAMP NOT NULL AFTER `created_user_id`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `modified_user_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `created_time`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `modified_time` TIMESTAMP NOT NULL AFTER `modified_user_id`;
 
ALTER TABLE `jos_categories`
 ADD COLUMN `hits` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `modified_time`;

ALTER TABLE `jos_categories` 
 ADD COLUMN `language` VARCHAR(7) NOT NULL AFTER `hits`;

ALTER TABLE `jos_categories`
 ADD INDEX idx_alias(`alias`);

ALTER TABLE `jos_categories`
 ADD INDEX idx_path(`path`);

ALTER TABLE `jos_categories`
 ADD INDEX idx_left_right(`lft`, `rgt`);

ALTER TABLE `jos_categories`
 DROP COLUMN `ordering`;

-- TODO: Merge from sections and add uncategorised nodes.

-- ----------------------------------------------------------------
-- jos_components
-- ----------------------------------------------------------------

ALTER TABLE `jos_components`
 MODIFY COLUMN `enabled` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1;

UPDATE `jos_components`
 SET admin_menu_link = 'option=com_content&view=articles'
 WHERE link = 'option=com_content';

INSERT INTO `#__components` VALUES
 (0, 'Articles', '', 0, 0, 'option=com_content&view=articles', 'com_content_Articles', 'com_content', 1, '', 1, '{}', 1),
 (0, 'Categories', '', 0, 0, 'option=com_categories&view=categories&extension=com_content', 'com_content_Categories', 'com_content', 2, '', 1, '{}', 1),
 (0, 'Featured', '', 0, 0, 'option=com_content&view=featured', 'com_content_Featured', 'com_content', 3, '', 1, '{}', 1);

UPDATE `jos_components` AS a
 LEFT JOIN `jos_components` AS b ON b.link='option=com_content'
 SET a.parent = b.id
 WHERE a.link = ''
  AND a.option = 'com_content';

-- ----------------------------------------------------------------
-- jos_content
-- ----------------------------------------------------------------

ALTER TABLE `jos_content`
 ADD COLUMN `featured` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Set if article is featured.' AFTER `metadata`;

ALTER TABLE `jos_content`
 ADD INDEX idx_featured_catid(`featured`, `catid`);

ALTER TABLE `jos_content`
 ADD COLUMN `language` VARCHAR(10) NOT NULL COMMENT 'The language code for the article.' AFTER `featured`;

ALTER TABLE `jos_content`
 ADD COLUMN `xreference` VARCHAR(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.' AFTER `language`;

ALTER TABLE `jos_content`
 ADD INDEX idx_language(`language`);
 
ALTER TABLE `jos_content`
 ADD INDEX idx_xreference(`xreference`);

UPDATE `jos_content` AS a
 SET a.featured = 1
 WHERE a.id IN (
 	SELECT f.content_id
 	FROM `jos_content_frontpage` AS f
 );

-- ----------------------------------------------------------------
-- jos_extensions (new) and migration
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
  `manifestcache` text NOT NULL,
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
) TYPE=MyISAM CHARACTER SET `utf8`;

TRUNCATE TABLE #__extensions;
INSERT INTO #__extensions SELECT 
     0,							# extension id (regenerate)
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
     '',                        # data
     checked_out,            	# checked_out
     checked_out_time,         	# checked_out_time
     ordering                   # ordering
     FROM #__plugins;         	# #__extensions replaces the old #__plugins table
     
 INSERT INTO #__extensions SELECT 
     0,                         # extension id (regenerate)
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
     '',                        # data
     '0',                       # checked_out
     '0000-00-00 00:00:00',     # checked_out_time
     0                          # ordering
     FROM #__components        # #__extensions replaces #__components for install uninstall
                                # component menu selection still utilises the #__components table
     WHERE parent = 0;          # only get top level entries
     
 INSERT INTO #__extensions SELECT DISTINCT
     0,                         # extension id (regenerate)
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
     '',                        # data
     '0',                       # checked_out (module instance, see #__modules)
     '0000-00-00 00:00:00',     # checked_out_time (module instance, see #__modules)
     0                          # ordering (module instance, see #__modules)
     FROM #__modules			# #__extensions provides the install/uninstall control for modules
     WHERE id IN (SELECT id FROM #__modules GROUP BY module ORDER BY id)     

-- New extensions
INSERT INTO `#__extensions` VALUES(0, 'Editor - CodeMirror', 'plugin', 'codemirror', 'editors', 1, 0, 1, 1, '', 'linenumbers=0\n\n', '', '', 0, '0000-00-00 00:00:00', 7, 0);

-- ----------------------------------------------------------------
-- jos_menu
-- ----------------------------------------------------------------

ALTER TABLE `jos_menu`
 DROP COLUMN `sublevel`,
 DROP COLUMN `pollid`,
 DROP COLUMN `utaccess`;

ALTER TABLE `jos_menu`
 MODIFY COLUMN `menutype` VARCHAR(24) NOT NULL COMMENT 'The type of menu this item belongs to. FK to jos_menu_types.menutype';

ALTER TABLE `jos_menu`
 CHANGE COLUMN `name` `title` VARCHAR(255) NOT NULL COMMENT 'The display title of the menu item.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `alias` VARCHAR(255) NOT NULL COMMENT 'The SEF alias of the menu item.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `link` VARCHAR(1024) NOT NULL COMMENT 'The actually link the menu item refers to.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `type` VARCHAR(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `published` TINYINT NOT NULL DEFAULT 0 COMMENT 'The published state of the menu link.';

ALTER TABLE `jos_menu`
 CHANGE COLUMN `parent` `parent_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The parent menu item in the menu tree.';

ALTER TABLE `jos_menu`
 ADD COLUMN `level` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The relative level in the tree.' AFTER `parent_id`;

ALTER TABLE `jos_menu`
 CHANGE COLUMN `componentid` `component_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to jos_components.id';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `ordering` INTEGER NOT NULL DEFAULT 0 COMMENT 'The relative ordering of the menu item in the tree.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `checked_out` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to jos_users.id';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `checked_out_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The time the menu item was checked out.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `browserNav` TINYINT NOT NULL DEFAULT 0 COMMENT 'The click behaviour of the link.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `access` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The access level required to view the menu item.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `params` VARCHAR(10240) NOT NULL COMMENT 'JSON encoded data for the menu item.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `lft` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set lft.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `rgt` INTEGER NOT NULL DEFAULT 0 COMMENT 'Nested set rgt.';

ALTER TABLE `jos_menu`
 MODIFY COLUMN `home` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Indicates if this menu item is the home or default page.';

ALTER TABLE `jos_menu`
 ADD COLUMN `path` VARCHAR(1024) NOT NULL COMMENT 'The computed path of the menu item based on the alias field.' AFTER `alias`;

INSERT INTO `jos_menu` VALUES 
 (0, '', 'Menu_Item_Root', 'root', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, 0, '', 0, 37, 0);

-- TODO: Need to devise how to shift the parent_id's of the existing menus to relate to the new root.
-- UPDATE `jos_menu`
--  SET `parent_id` = (SELECT `id` FROM `jos_menu` WHERE `alias` = 'root')
--  WHERE `alias` != 'root';

-- ----------------------------------------------------------------
-- jos_menu_types
-- ----------------------------------------------------------------

ALTER TABLE `jos_menu_types`
 MODIFY COLUMN `menutype` VARCHAR(24) NOT NULL,
 MODIFY COLUMN `title` VARCHAR(48) NOT NULL,
 DROP INDEX `menutype`; 

-- ----------------------------------------------------------------
-- jos_modules
-- ----------------------------------------------------------------

UPDATE `#__modules`
 SET `menutype` = 'mod_menu'
 WHERE `menutype` = 'mod_mainmenu';
 
-- ----------------------------------------------------------------
-- jos_plugins
-- ----------------------------------------------------------------

INSERT INTO `#__plugins` VALUES (NULL, 'Editor - CodeMirror', 'codemirror', 'editors', 1, 0, 1, 1, 0, 0, '0000-00-00 00:00:00', 'linenumbers=0\n\n');

-- ----------------------------------------------------------------
-- jos_schemas
-- ----------------------------------------------------------------

CREATE TABLE `#__schemas` (
  `extensionid` int(11) NOT NULL,
  `versionid` varchar(20) NOT NULL,
  PRIMARY KEY (`extensionid`, `versionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------------
-- jos_session
-- ----------------------------------------------------------------

ALTER TABLE `jos_session`
 MODIFY COLUMN `session_id` VARCHAR(32);

ALTER TABLE `jos_session`
 MODIFY COLUMN `guest` TINYINT UNSIGNED DEFAULT 1;

ALTER TABLE `jos_session`
 MODIFY COLUMN `client_id` TINYINT UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `jos_session`
 MODIFY COLUMN `data` VARCHAR(20480);

-- ----------------------------------------------------------------
-- jos_updates (new)
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
-- jos_update_sites (new)
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
-- jos_update_sites_extensions (new)
-- ----------------------------------------------------------------

CREATE TABLE `#__update_sites_extensions` (
  `update_site_id` INT DEFAULT 0,
  `extension_id` INT DEFAULT 0,
  INDEX `newindex`(`update_site_id`, `extension_id`)
) ENGINE = MYISAM CHARACTER SET utf8 COMMENT = 'Links extensions to update sites';

-- ----------------------------------------------------------------
-- jos_update_categories (new)
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
-- jos_weblinks
-- ----------------------------------------------------------------

ALTER TABLE `jos_weblinks`
 ADD COLUMN `access` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `approved`;



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
INSERT INTO #__schema VALUES(LAST_INSERT_ID()), '20090622');

-- Parameter conversions todo

# com_content show_vote -> article-allow_ratings





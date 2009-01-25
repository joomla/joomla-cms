# $Id$

# Joomla! 1.5.x to Joomla! 1.6 upgrade script
# --------------------------------------------------------
# Table structure for table `#__extensions`
CREATE TABLE  `jos_extensions` (
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
) TYPE=MyISAM CHARACTER SET `utf8`;

# Earlier versions of this file didn't have the checkout and ordering fields
ALTER TABLE `jos_extensions` ADD COLUMN `checked_out` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `data`,
 ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT 0 AFTER `checked_out`,
 ADD COLUMN `ordering` INTEGER DEFAULT 0 AFTER `checked_out_time`;

# Earlier versions used 'data' instead of 'custom_data' and 'system_data'
ALTER TABLE `jos_extensions` CHANGE COLUMN `data` `custom_data` TEXT NOT NULL,
 ADD COLUMN `system_data` TEXT  NOT NULL AFTER `custom_data`;

# Earlier versions didn't have a state field; used for discovered extensions
ALTER TABLE `jos_extensions` ADD COLUMN `state` INTEGER  NOT NULL DEFAULT 0 AFTER `ordering`;

# Added some indicies
ALTER TABLE `jos_extensions` ADD INDEX `type_element`(`type`, `element`),
 ADD INDEX `element_clientid`(`element`, `client_id`),
 ADD INDEX `element_folder_clientid`(`element`, `folder`, `client_id`).
 ADD INDEX `extension`(`type`,`element`,`folder`,`client_id`);

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

--
-- Table structure for table `#__access_actions`
--

CREATE TABLE IF NOT EXISTS `#__access_actions` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Primary Key',
  `section_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_sections.id',
  `name` varchar(100) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `description` text,
  `access_type` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_action_name_lookup` (`section_id`,`name`),
  KEY `idx_acl_manager_lookup` (`access_type`,`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__access_action_rule_map`
--

CREATE TABLE IF NOT EXISTS `#__access_action_rule_map` (
  `action_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_actions.id',
  `rule_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_rules.id',
  PRIMARY KEY  (`action_id`,`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

-- --------------------------------------------------------

--
-- Table structure for table `#__access_assetgroup_rule_map`
--

CREATE TABLE IF NOT EXISTS `#__access_assetgroup_rule_map` (
  `group_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_assetgroups.id',
  `rule_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_rules.id',
  PRIMARY KEY  (`group_id`,`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `title` varchar(255) NOT NULL default '',
  `description` varchar(255) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `allow` int(1) unsigned NOT NULL default '0',
  `enabled` int(1) unsigned NOT NULL default '0',
  `access_type` int(1) unsigned NOT NULL default '0',
  `updated_date` int(10) unsigned NOT NULL default '0',
  `return` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_rule_name_lookup` (`section_id`,`name`),
  KEY `idx_access_check` (`enabled`, `allow`),
  KEY `idx_updated_lookup` (`updated_date`),
  KEY `idx_action_section_lookup` (`section`),
  KEY `idx_acl_manager_lookup` (`access_type`,`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

-- --------------------------------------------------------

--
-- Table structure for table `#__usergroup_rule_map`
--

CREATE TABLE IF NOT EXISTS `#__usergroup_rule_map` (
  `group_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__usergroups.id',
  `rule_id` int(10) unsigned NOT NULL default '0' COMMENT 'Foreign Key to #__access_rules.id',
  PRIMARY KEY  (`group_id`,`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

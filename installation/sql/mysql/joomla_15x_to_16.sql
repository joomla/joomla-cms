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

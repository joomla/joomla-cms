CREATE TABLE IF NOT EXISTS `#__djimageslider` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `catid` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL default '0000-00-00 00:00:00',
  `checked_out` int(10) unsigned NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL default '0',
  `params` text DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`,`published`)
) DEFAULT CHARSET=utf8;

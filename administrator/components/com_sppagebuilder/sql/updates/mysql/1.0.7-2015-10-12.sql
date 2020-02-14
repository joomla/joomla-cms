ALTER TABLE `#__sppagebuilder` ADD `catid` int(10) NOT NULL AFTER `published`;
ALTER TABLE `#__sppagebuilder` ADD `ordering` int(11) NOT NULL AFTER `access`;

CREATE TABLE IF NOT EXISTS `#__spmedia` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `alt` varchar(255) NOT NULL,
  `caption` varchar(2048) NOT NULL,
  `description` mediumtext NOT NULL,
  `type` varchar(100) NOT NULL DEFAULT 'image',
  `extension` varchar(100) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
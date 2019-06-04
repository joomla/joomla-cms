DROP TABLE IF EXISTS `#__testtable`;
CREATE TABLE IF NOT EXISTS `#__testtable` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `title` varchar(100) NOT NULL DEFAULT '',
   `asset_id` int(11) NOT NULL DEFAULT 0,
   `hits` int(11) NOT NULL DEFAULT 0,
   `checked_out` int(11) NOT NULL DEFAULT 0,
   `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   `published` tinyint(1) NOT NULL DEFAULT 0,
   `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   `ordering` int(11) NOT NULL DEFAULT 0,
   `params` text NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

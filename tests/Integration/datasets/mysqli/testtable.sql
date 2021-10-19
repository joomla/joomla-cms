DROP TABLE IF EXISTS `#__testtable`;
CREATE TABLE IF NOT EXISTS `#__testtable` (
   `id` int unsigned NOT NULL AUTO_INCREMENT,
   `title` varchar(100) NOT NULL DEFAULT '',
   `asset_id` int NOT NULL DEFAULT 0,
   `hits` int NOT NULL DEFAULT 0,
   `checked_out` int unsigned,
   `checked_out_time` datetime,
   `published` tinyint NOT NULL DEFAULT 0,
   `publish_up` datetime,
   `publish_down` datetime,
   `ordering` int NOT NULL DEFAULT 0,
   `params` text NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

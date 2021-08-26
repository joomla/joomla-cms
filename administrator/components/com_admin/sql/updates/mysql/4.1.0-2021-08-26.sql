-- after 4.0.0 RC1
 CREATE TABLE IF NOT EXISTS `#__draft` (
  `article_id` int unsigned NOT NULL,
  `version_id` int unsigned NOT NULL,
  `state` tinyint NOT NULL DEFAULT '0',
  `hashval` varchar(2083) NOT NULL DEFAULT '',
  `shared_date` datetime DEFAULT NULL,
  PRIMARY KEY(`article_id`, `version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__content` ADD COLUMN `shared` tinyint(3) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__content` ADD COLUMN `draft` tinyint(3) UNSIGNED NOT NULL DEFAULT '0';
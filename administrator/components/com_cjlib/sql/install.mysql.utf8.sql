CREATE TABLE IF NOT EXISTS `#__cjlib_config` (
  `config_name` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY (`config_name`)
) CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__corejoomla_messagequeue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `to_addr` varchar(128) NOT NULL,
  `cc_addr` varchar(128) DEFAULT NULL,
  `bcc_addr` varchar(128) DEFAULT NULL,
  `html` tinyint(1) NOT NULL DEFAULT '1',
  `message_id` int(10) unsigned NOT NULL,
  `params` mediumtext,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `processed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__corejoomla_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL,
  `asset_name` varchar(64) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `params` varchar(999) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__corejoomla_rating`(
  `item_id` int(10) unsigned NOT NULL,
  `asset_id` int(10) unsigned NOT NULL,
  `total_ratings` int(10) unsigned NOT NULL DEFAULT '0',
  `sum_rating` int(10) unsigned NOT NULL DEFAULT '0',
  `rating` decimal(4,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`item_id`,`asset_id`)
) CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__corejoomla_rating_details` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INTEGER UNSIGNED NOT NULL,
  `item_id` INTEGER UNSIGNED NOT NULL,
  `action_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `rating` INTEGER UNSIGNED NOT NULL,
  `created_by` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__corejoomla_assets` (
  `id` INTEGER UNSIGNED NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `version` VARCHAR(32) NOT NULL,
  `released` DATE NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__corejoomla_countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` VARCHAR(3) NOT NULL,
  `country_name` VARCHAR(64) NOT NULL,
  `language` VARCHAR(6) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_corejoomla_countries_uniq` (`country_code`,`language`)
) CHARACTER SET `utf8`;
ALTER TABLE `#__sppagebuilder` ADD `extension` varchar(255) NOT NULL DEFAULT 'com_sppagebuilder' AFTER `text`;
ALTER TABLE `#__sppagebuilder` ADD `extension_view` varchar(255) NOT NULL DEFAULT 'page' AFTER `extension`;
ALTER TABLE `#__sppagebuilder` ADD `view_id` bigint(20) NOT NULL AFTER `extension_view`;
ALTER TABLE `#__sppagebuilder` ADD `active` tinyint(1) NOT NULL DEFAULT '0' AFTER `view_id`;
ALTER TABLE `#__sppagebuilder` CHANGE `created_time` `created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__sppagebuilder` CHANGE `created_user_id` `created_by` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__sppagebuilder` CHANGE `modified_time` `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__sppagebuilder` CHANGE `modified_user_id` `modified_by` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__sppagebuilder` ADD `checked_out` int(10) NOT NULL DEFAULT '0' AFTER `modified_by`;
ALTER TABLE `#__sppagebuilder` ADD `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `checked_out`;
ALTER TABLE `#__sppagebuilder` ADD `css` longtext NOT NULL AFTER `hits`;
ALTER TABLE `#__sppagebuilder` DROP `alias`;
ALTER TABLE `#__sppagebuilder` DROP `page_layout`;

CREATE TABLE IF NOT EXISTS `#__sppagebuilder_integrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `component` varchar(255) NOT NULL,
  `plugin` mediumtext NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

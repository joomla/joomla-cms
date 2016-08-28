ALTER TABLE `#__users` DROP INDEX `usertype`;
ALTER TABLE `#__session` DROP INDEX `whosonline`;

DROP TABLE IF EXISTS `#__update_categories`;

ALTER TABLE `#__contact_details` DROP `imagepos`;
ALTER TABLE `#__content` DROP COLUMN `title_alias`;
ALTER TABLE `#__content` DROP COLUMN `sectionid`;
ALTER TABLE `#__content` DROP COLUMN `mask`;
ALTER TABLE `#__content` DROP COLUMN `parentid`;
ALTER TABLE `#__newsfeeds` DROP COLUMN `filename`;
ALTER TABLE `#__menu` DROP COLUMN `ordering`;
ALTER TABLE `#__session` DROP COLUMN `usertype`;
ALTER TABLE `#__users` DROP COLUMN `usertype`;
ALTER TABLE `#__updates` DROP COLUMN `categoryid`;

UPDATE `#__extensions` SET protected = 0 WHERE
`name` = 'com_search' OR
`name` = 'mod_articles_archive' OR
`name` = 'mod_articles_latest' OR
`name` = 'mod_banners' OR
`name` = 'mod_feed' OR
`name` = 'mod_footer' OR
`name` = 'mod_users_latest' OR
`name` = 'mod_articles_category' OR
`name` = 'mod_articles_categories' OR
`name` = 'plg_content_pagebreak' OR
`name` = 'plg_content_pagenavigation' OR
`name` = 'plg_content_vote' OR
`name` = 'plg_editors_tinymce' OR
`name` = 'plg_system_p3p' OR
`name` = 'plg_user_contactcreator' OR
`name` = 'plg_user_profile';

DELETE FROM `#__extensions` WHERE `extension_id` = 800;

ALTER TABLE `#__assets` ENGINE=InnoDB;
ALTER TABLE `#__associations` ENGINE=InnoDB;
ALTER TABLE `#__banners` ENGINE=InnoDB;
ALTER TABLE `#__banner_clients` ENGINE=InnoDB;
ALTER TABLE `#__banner_tracks` ENGINE=InnoDB;
ALTER TABLE `#__categories` ENGINE=InnoDB;
ALTER TABLE `#__contact_details` ENGINE=InnoDB;
ALTER TABLE `#__content` ENGINE=InnoDB;
ALTER TABLE `#__content_frontpage` ENGINE=InnoDB;
ALTER TABLE `#__content_rating` ENGINE=InnoDB;
ALTER TABLE `#__core_log_searches` ENGINE=InnoDB;
ALTER TABLE `#__extensions` ENGINE=InnoDB;
ALTER TABLE `#__finder_filters` ENGINE=InnoDB;
ALTER TABLE `#__finder_links` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms0` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms1` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms2` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms3` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms4` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms5` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms6` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms7` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms8` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_terms9` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_termsa` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_termsb` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_termsc` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_termsd` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_termse` ENGINE=InnoDB;
ALTER TABLE `#__finder_links_termsf` ENGINE=InnoDB;
ALTER TABLE `#__finder_taxonomy` ENGINE=InnoDB;
ALTER TABLE `#__finder_taxonomy_map` ENGINE=InnoDB;
ALTER TABLE `#__finder_terms` ENGINE=InnoDB;
ALTER TABLE `#__finder_terms_common` ENGINE=InnoDB;
ALTER TABLE `#__finder_types` ENGINE=InnoDB;
ALTER TABLE `#__languages` ENGINE=InnoDB;
ALTER TABLE `#__menu` ENGINE=InnoDB;
ALTER TABLE `#__menu_types` ENGINE=InnoDB;
ALTER TABLE `#__messages` ENGINE=InnoDB;
ALTER TABLE `#__messages_cfg` ENGINE=InnoDB;
ALTER TABLE `#__modules` ENGINE=InnoDB;
ALTER TABLE `#__modules_menu` ENGINE=InnoDB;
ALTER TABLE `#__newsfeeds` ENGINE=InnoDB;
ALTER TABLE `#__overrider` ENGINE=InnoDB;
ALTER TABLE `#__redirect_links` ENGINE=InnoDB;
ALTER TABLE `#__schemas` ENGINE=InnoDB;
ALTER TABLE `#__session` ENGINE=InnoDB;
ALTER TABLE `#__template_styles` ENGINE=InnoDB;
ALTER TABLE `#__updates` ENGINE=InnoDB;
ALTER TABLE `#__update_sites` ENGINE=InnoDB;
ALTER TABLE `#__update_sites_extensions` ENGINE=InnoDB;
ALTER TABLE `#__users` ENGINE=InnoDB;
ALTER TABLE `#__usergroups` ENGINE=InnoDB;
ALTER TABLE `#__user_notes` ENGINE=InnoDB;
ALTER TABLE `#__user_profiles` ENGINE=InnoDB;
ALTER TABLE `#__user_usergroup_map` ENGINE=InnoDB;
ALTER TABLE `#__viewlevels` ENGINE=InnoDB;

ALTER TABLE `#__newsfeeds` ADD COLUMN `description` text NOT NULL;
ALTER TABLE `#__newsfeeds` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__newsfeeds` ADD COLUMN `hits` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__newsfeeds` ADD COLUMN `images` text NOT NULL;
ALTER TABLE `#__contact_details` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__contact_details` ADD COLUMN `hits` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__banners` ADD COLUMN `created_by` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__banners` ADD COLUMN `created_by_alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__banners` ADD COLUMN `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__banners` ADD COLUMN `modified_by` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__banners` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__categories` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
UPDATE  `#__assets` SET name=REPLACE( name, 'com_user.notes.category','com_users.category'  );
UPDATE  `#__categories` SET extension=REPLACE( extension, 'com_user.notes.category','com_users.category'  );

ALTER TABLE `#__finder_terms` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens_aggregate` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';

INSERT INTO `#__extensions`
	(`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`)
	VALUES
	('isis', 'template', 'isis', '', 1, 1, 1, 0, '{"name":"isis","type":"template","creationDate":"3\\/30\\/2012","author":"Kyle Ledbetter","copyright":"Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"","version":"1.0","description":"TPL_ISIS_XML_DESCRIPTION","group":""}', '{"templateColor":"","logoFile":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
	('protostar', 'template', 'protostar', '', 0, 1, 1, 0, '{"name":"protostar","type":"template","creationDate":"4\\/30\\/2012","author":"Kyle Ledbetter","copyright":"Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"","version":"1.0","description":"TPL_PROTOSTAR_XML_DESCRIPTION","group":""}', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
	('beez3', 'template', 'beez3', '', 0, 1, 1, 0, '{"legacy":false,"name":"beez3","type":"template","creationDate":"25 November 2009","author":"Angie Radtke","copyright":"Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.","authorEmail":"a.radtke@derauftritt.de","authorUrl":"http:\\/\\/www.der-auftritt.de","version":"1.6.0","description":"TPL_BEEZ3_XML_DESCRIPTION","group":""}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO `#__template_styles` (`template`, `client_id`, `home`, `title`, `params`) VALUES
	('protostar', 0, '0', 'protostar - Default', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'),
	('isis', 1, '1', 'isis - Default', '{"templateColor":"","logoFile":""}'),
	('beez3', 0, '0', 'beez3 - Default', '{"wrapperSmall":53,"wrapperLarge":72,"logo":"","sitetitle":"","sitedescription":"","navposition":"center","bootstrap":"","templatecolor":"nature","headerImage":"","backgroundcolor":"#eee"}');

UPDATE `#__template_styles`
SET home = (CASE WHEN (SELECT count FROM (SELECT count(`id`) AS count
			FROM `#__template_styles`
			WHERE home = '1'
			AND client_id = 1) as c) = 0
			THEN '1'
			ELSE '0'
			END)
WHERE template = 'isis'
AND home != '1';

UPDATE `#__template_styles`
SET home = 0
WHERE template = 'bluestork';

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(315, 'mod_stats_admin', 'module', 'mod_stats_admin', '', 1, 1, 1, 0, '{"name":"mod_stats_admin","type":"module","creationDate":"September 2012","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"MOD_STATS_XML_DESCRIPTION","group":""}', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

UPDATE `#__update_sites`
SET location = 'http://update.joomla.org/language/translationlist_3.xml'
WHERE location = 'http://update.joomla.org/language/translationlist.xml'
AND name = 'Accredited Joomla! Translations';

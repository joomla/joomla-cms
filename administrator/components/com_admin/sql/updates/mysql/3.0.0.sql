# Remove unused columns
ALTER TABLE `#__contact_details` DROP `imagepos`;
ALTER TABLE `#__content` DROP COLUMN `title_alias`;
ALTER TABLE `#__content` DROP COLUMN `sectionid`;
ALTER TABLE `#__content` DROP COLUMN `mask`;
ALTER TABLE `#__content` DROP COLUMN `parentid`;
ALTER TABLE `#__newsfeeds` DROP COLUMN `filename`;
ALTER TABLE `#__weblinks` DROP COLUMN `sid`;
ALTER TABLE `#__weblinks` DROP COLUMN `date`;
ALTER TABLE `#__weblinks` DROP COLUMN `archived`;
ALTER TABLE `#__weblinks` DROP COLUMN `approved`;
ALTER TABLE `#__menu` DROP COLUMN `ordering`;

# Unprotect a number of extensions
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

# Add new columns to normalise our content tables
ALTER TABLE `#__weblinks` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__weblinks` ADD COLUMN `images` text NOT NULL;
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
ALTER TABLE `#__finder_terms` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';
ALTER TABLE `#__finder_tokens_aggregate` ADD COLUMN `language` char(3) NOT NULL DEFAULT '';

# Add new templates
INSERT INTO `#__extensions`
	(`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`)
	VALUES
	('isis', 'template', 'isis', '', 1, 1, 1, 0, '{"name":"isis","type":"template","creationDate":"3\\/30\\/2012","author":"Kyle Ledbetter","copyright":"Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"","version":"1.0","description":"TPL_ISIS_XML_DESCRIPTION","group":""}', '{"templateColor":"","logoFile":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
	('protostar', 'template', 'protostar', '', 0, 1, 1, 0, '{"name":"protostar","type":"template","creationDate":"4\\/30\\/2012","author":"Kyle Ledbetter","copyright":"Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"","version":"1.0","description":"TPL_PROTOSTAR_XML_DESCRIPTION","group":""}', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO `#__template_styles` (`template`, `client_id`, `home`, `title`, `params`) VALUES
	('protostar', 0, '0', 'protostar - Default', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'),
	('isis', 1, '1', 'isis - Default', '{"templateColor":"","logoFile":""}');

# Deal with removed templates
DELETE FROM `#__extensions`
	WHERE type = 'template' AND name = 'bluestork';

DELETE FROM `#__template_styles`
	WHERE template = 'bluestork';

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

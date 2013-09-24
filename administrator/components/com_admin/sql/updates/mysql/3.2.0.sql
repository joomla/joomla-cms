# Placeholder file for database changes for version 3.2.0
INSERT INTO `#__assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES
(36, 1, 69, 70, 1, 'com_services', 'com_services', '{}');

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(30, 'com_services', 'component', 'com_services', '', 1, 1, 0, 0, '{"name":"com_services","type":"component","creationDate":"2013-06-31","author":"Joomla! Project","copyright":"Copyright Info","authorEmail":"Joomla@joomla.com","authorUrl":"http:\/\/joomla.org","version":"1.0.0","description":"Front End Admin Services Configuration Manager","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(318, 'mod_admin_services', 'module', 'mod_admin_services', '', 0, 1, 0, 0, '{"name":"mod_admin_services","type":"module","creationDate":"June 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2013 Open Source Matters. All rights\n\t\treserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"MOD_ADMIN_SERVICES_XML_DESCRIPTION","group":""}', '{"config_visible":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO  `#__modules` (`title` , `note` , `content` , `ordering` , `position` , `checked_out` , `checked_out_time` ,  `publish_up` , `publish_down` , `published` , `module` , `access` , `showtitle` , `params` , `client_id` , `language`) VALUES 
('Admin Services', '', '', 1,  'position-7', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_admin_services', 1, 1, '{"config_visible":"1","templates_visible":"1","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');

INSERT INTO `#__modules_menu` (`moduleid`, `menuid`) VALUES 
(LAST_INSERT_ID(), 0);

CREATE TABLE IF NOT EXISTS `#__ucm_history` (
  `version_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ucm_item_id` int(10) unsigned NOT NULL,
  `ucm_type_id` int(10) unsigned NOT NULL,
  `version_note` varchar(255) NOT NULL DEFAULT '' COMMENT 'Optional version name',
  `save_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `editor_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `character_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of characters in this version.',
  `sha1_hash` varchar(50) NOT NULL DEFAULT '' COMMENT 'SHA1 hash of the version_data column.',
  `version_data` mediumtext NOT NULL COMMENT 'json-encoded string of version data',
  `keep_forever` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=auto delete; 1=keep',
  PRIMARY KEY (`version_id`),
  KEY `idx_ucm_item_id` (`ucm_type_id`,`ucm_item_id`),
  KEY `idx_save_date` (`save_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(30, 'com_contenthistory', 'component', 'com_contenthistory', '', 1, 1, 1, 0, '{"name":"com_contenthistory","type":"component","creationDate":"May 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.\\n\\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_CONTENTHISTORY_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 1, 0, '{"name":"com_ajax","type":"component","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_AJAX_DESC","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '{"legacy":false,"name":"FOF","type":"library","creationDate":"2013-09-03","author":"Nicholas K. Dionysopoulos \/ Akeeba Ltd","copyright":"(C)2011-2013 Nicholas K. Dionysopoulos","authorEmail":"nicholas@akeebabackup.com","authorUrl":"https:\/\/www.akeebabackup.com","version":"2.1.rc2","description":"Framework-on-Framework (FOF) - A rapid component development framework for Joomla!","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 1, 1, 0, '{"name":"plg_twofactorauth_totp","type":"plugin","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"PLG_TWOFACTORAUTH_TOTP_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

ALTER TABLE `#__content_types` ADD COLUMN `content_history_options` VARCHAR(5120) NOT NULL COMMENT 'JSON string for com_contenthistory options';

UPDATE `#__content_types` SET `content_history_options` = '{"form_file":"administrator\\/components\\/com_content\\/models\\/forms\\/article.xml", "hide_fields":["asset_id","checked_out","checked_out_time","version"], "display_lookup":[{"source_column":"catid","target_table":"#__categories","target_column":"id","display_column":"title"}, {"source_column":"created_by","target_table":"#__users","target_column":"id","display_column":"name"}, {"source_column":"access","target_table":"#__viewlevels","target_column":"id","display_column":"title"}, {"source_column":"modified_by","target_table":"#__users","target_column":"id","display_column":"name"}]}' WHERE `type_alias` = 'com_content.article';

UPDATE `#__content_types` SET `content_history_options` = '{"form_file":"administrator\/components\/com_contact\/models\/forms\/contact.xml", "hide_fields":["default_con","checked_out","checked_out_time","version","xreference"], "display_lookup":[{"source_column":"created_by","target_table":"#__users","target_column":"id","display_column":"name"}, {"source_column":"catid","target_table":"#__categories","target_column":"id","display_column":"title"}, {"source_column":"modified_by","target_table":"#__users","target_column":"id","display_column":"name"}, {"source_column":"access","target_table":"#__viewlevels","target_column":"id","display_column":"title"},{"source_column":"user_id","target_table":"#__users","target_column":"id","display_column":"name"}]}' WHERE `type_alias` = 'com_contact.contact';

UPDATE `#__content_types` SET `content_history_options` = '{"form_file":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hide_fields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "display_lookup":[{"source_column":"created_user_id","target_table":"#__users","target_column":"id","display_column":"name"}, {"source_column":"access","target_table":"#__viewlevels","target_column":"id","display_column":"title"}, {"source_column":"modified_user_id","target_table":"#__users","target_column":"id","display_column":"name"},{"source_column":"parent_id","target_table":"#__categories","target_column":"id","display_column":"title"}]}' WHERE `type_alias` IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_weblinks.category');

ALTER TABLE `#__users` ADD COLUMN `otpKey` varchar(1000) NOT NULL DEFAULT '' COMMENT 'Two factor authentication encrypted keys';

ALTER TABLE `#__users` ADD COLUMN `otep` varchar(1000) NOT NULL DEFAULT '' COMMENT 'One time emergency passwords';

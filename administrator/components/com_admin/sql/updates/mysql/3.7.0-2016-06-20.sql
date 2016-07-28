CREATE TABLE IF NOT EXISTS `#__fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) NOT NULL DEFAULT 0,
  `context` varchar(255) NOT NULL DEFAULT '',
  `catid` int(10) NOT NULL DEFAULT 0,
  `assigned_cat_ids` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) NOT NULL DEFAULT '',
  `default_value` text NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `options` varchar(255) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `fieldparams` text NOT NULL,
  `attributes` text NOT NULL,
  `language` char(7) NOT NULL DEFAULT '',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `version` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(11) NOT NULL DEFAULT '1',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_created_user_id` (`created_user_id`),
  KEY `idx_access` (`access`),
  KEY `idx_context` (`context`),
  KEY `idx_language` (`language`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__fields_values` (
  `field_id` int(10) unsigned NOT NULL,
  `context` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `value` text NOT NULL DEFAULT '',
  KEY (`field_id`),
  KEY (`context`),
  KEY (`item_id`)
) DEFAULT CHARSET=utf8 ;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
('Field', 'com_fields.field', '{"special":{"dbtable":"#__fields","key":"id","type":"Field","prefix":"FieldsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Field","prefix":"FieldsTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_language":"language", "asset_id":"asset_id"}, "special":{}}', '', '{"formFile":"administrator\\/components\\/com_fields\\/models\\/forms\\/field.xml", "hideFields":["asset_id","checked_out", "checked_out_time"],"ignoreChanges":["modified_by", "modified_time", "checked_out", "checked_out_time"],"convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}');

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(33, 'com_fields', 'component', 'com_fields', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);
INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(458, 'plg_system_fields', 'plugin', 'fields', 'system', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);
INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(459, 'plg_fields_gallery', 'plugin', 'gallery', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
SELECT 31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 0, 0, '{"name":"com_ajax","type":"component","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_AJAX_DESC","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 32, 'com_postinstall', 'component', 'com_postinstall', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '{"legacy":false,"name":"FOF","type":"library","creationDate":"2013-10-03","author":"Nicholas K. Dionysopoulos \/ Akeeba Ltd","copyright":"(C)2011-2013 Nicholas K. Dionysopoulos","authorEmail":"nicholas@akeebabackup.com","authorUrl":"https:\/\/www.akeebabackup.com","version":"2.1.rc4","description":"Framework-on-Framework (FOF) - A rapid component development framework for Joomla!","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 449, 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', 0, 1, 1, 0, '{"name":"plg_authentication_cookie","type":"plugin","creationDate":"July 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"PLG_AUTH_COOKIE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT #__extensions  OFF;

ALTER TABLE [#__users] ADD [otpKey] [nvarchar](max) NOT NULL DEFAULT '';

ALTER TABLE [#__users] ADD [otep] [nvarchar](max) NOT NULL DEFAULT '';

SET IDENTITY_INSERT #__assets ON;

INSERT INTO #__assets (id,parent_id,lft,rgt,level,name,title,rules)
SELECT 38,1,73,74,1,'com_postinstall','com_postinstall','{}';

SET IDENTITY_INSERT #__assets OFF;

INSERT INTO #__menu (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id)
SELECT 'menu', 'com_postinstall', 'com_postinstall', '', 'Post-installation messages', 'index.php?option=com_postinstall', 'component', 0, 1, 1, 32, 0, '1900-01-01 00:00:00', 0, 0, 'class:postinstall', 0, '', 45, 46, 0, '*', 1;

CREATE TABLE [#__postinstall_messages] (
  [postinstall_message_id] [bigint] IDENTITY(1,1) NOT NULL,
  [extension_id] [bigint] NOT NULL DEFAULT '700',
  [title_key] [nvarchar](255) NOT NULL DEFAULT '',
  [description_key] [nvarchar](255) NOT NULL DEFAULT '',
  [action_key] [nvarchar](255) NOT NULL DEFAULT '',
  [language_extension] [nvarchar](255) NOT NULL DEFAULT 'com_postinstall',
  [language_client_id] [int] NOT NULL DEFAULT '1',
  [type] [nvarchar](10) NOT NULL DEFAULT 'link',
  [action_file] [nvarchar](255) DEFAULT '',
  [action] [nvarchar](255) DEFAULT '',
  [condition_file] [nvarchar](255) DEFAULT NULL,
  [condition_method] [nvarchar](255) DEFAULT NULL,
  [version_introduced] [nvarchar](50) NOT NULL DEFAULT '3.2.0',
  [enabled] [int] NOT NULL DEFAULT '1',
  CONSTRAINT [PK_#__postinstall_message_id] PRIMARY KEY CLUSTERED
(
	[postinstall_message_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
);

SET IDENTITY_INSERT #__postinstall_messages  ON;

INSERT INTO #__postinstall_messages ([postinstall_message_id], [extension_id], [title_key], [description_key], [action_key], [language_extension], [language_client_id], [type], [action_file], [action], [condition_file], [condition_method], [version_introduced], [enabled])
SELECT 1, 700, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_TITLE', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_BODY', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_ACTION', 'plg_twofactorauth_totp', 1, 'action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_condition', '3.2.0', 1
UNION ALL
SELECT 2, 700, 'COM_CPANEL_MSG_EACCELERATOR_TITLE', 'COM_CPANEL_MSG_EACCELERATOR_BODY', 'COM_CPANEL_MSG_EACCELERATOR_BUTTON', 'com_cpanel', 1, 'action', 'admin://components/com_admin/postinstall/eaccelerator.php', 'admin_postinstall_eaccelerator_action', 'admin://components/com_admin/postinstall/eaccelerator.php', 'admin_postinstall_eaccelerator_condition', '3.2.0', 1;

SET IDENTITY_INSERT #__postinstall_messages  OFF;

SET IDENTITY_INSERT #__content_types  ON;

INSERT INTO #__content_types ([type_id],[type_title],[type_alias],[table],[field_mappings])
SELECT 11, 'Banner', 'com_banners.banner', '{"special":{"dbtable":"#__banners","key":"id","type":"Banner","prefix":"BannersTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"null","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"null"}, "special":{"imptotal":"imptotal", "impmade":"impmade", "clicks":"clicks", "clickurl":"clickurl", "custombannercode":"custombannercode", "cid":"cid", "purchase_type":"purchase_type", "track_impressions":"track_impressions", "track_clicks":"track_clicks"}}'
UNION ALL
SELECT 12,'Banners Category','com_banners.category','{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}';

SET IDENTITY_INSERT #__content_types  OFF;

ALTER TABLE [#__modules] ADD [asset_id] [int](10) NOT NULL DEFAULT 0 AFTER [id];

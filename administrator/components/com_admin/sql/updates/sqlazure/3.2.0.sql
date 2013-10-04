SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
SELECT 31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 0, 0, '{"name":"com_ajax","type":"component","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_AJAX_DESC","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 32, 'com_postinstall', 'component', 'com_postinstall', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '{"legacy":false,"name":"FOF","type":"library","creationDate":"2013-09-03","author":"Nicholas K. Dionysopoulos \/ Akeeba Ltd","copyright":"(C)2011-2013 Nicholas K. Dionysopoulos","authorEmail":"nicholas@akeebabackup.com","authorUrl":"https:\/\/www.akeebabackup.com","version":"2.1.rc2","description":"Framework-on-Framework (FOF) - A rapid component development framework for Joomla!","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 449, 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', 0, 1, 1, 0, '{"name":"plg_authentication_cookie","type":"plugin","creationDate":"July 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"PLG_AUTH_COOKIE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT #__extensions  OFF;

ALTER TABLE [#__users] ADD [otpKey] [nvarchar](max) NOT NULL DEFAULT '';

ALTER TABLE [#__users] ADD [otep] [nvarchar](max) NOT NULL DEFAULT '';

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
SELECT 1, 700, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_TITLE', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_BODY', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_ACTION', 'plg_twofactorauth_totp', 1, 'action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_condition', '3.2.0', 1;

SET IDENTITY_INSERT #__postinstall_messages  OFF;

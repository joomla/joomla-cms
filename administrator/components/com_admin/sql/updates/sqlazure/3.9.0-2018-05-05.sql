SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES
(36, 0, 'com_userlogs', 'component', 'com_userlogs', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0),
(481, 0, 'plg_system_userlogs', 'plugin', 'userlogs', 'system', 0, 0, 1, 0, '', '{"logDeletePeriod":"0","ip_logging":"1","loggable_extensions":["com_banners","com_cache","com_categories","com_config","com_contact","com_content","com_installer","com_media","com_menus","com_messages","com_modules","com_newsfeeds","com_plugins","com_redirect","com_tags","com_templates","com_users"]}', '', '', 0, '1900-01-01 00:00:00', 0, 0);

SET IDENTITY_INSERT #__extensions  OFF;

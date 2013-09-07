SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
SELECT 31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 0, 0, '{"name":"com_ajax","type":"component","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_AJAX_DESC","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0
UNION ALL
SELECT 105, 'FOF', 'library', 'lib_fof', '', 0, 1, 1, 1, '{"legacy":false,"name":"FOF","type":"library","creationDate":"2013-09-03","author":"Nicholas K. Dionysopoulos \/ Akeeba Ltd","copyright":"(C)2011-2013 Nicholas K. Dionysopoulos","authorEmail":"nicholas@akeebabackup.com","authorUrl":"https:\/\/www.akeebabackup.com","version":"2.1.rc2","description":"Framework-on-Framework (FOF) - A rapid component development framework for Joomla!","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT #__extensions  OFF;

ALTER TABLE [#__users] ADD [otpKey] [nvarchar](max) NOT NULL DEFAULT '';

ALTER TABLE [#__users] ADD [otep] [nvarchar](max) NOT NULL DEFAULT '';

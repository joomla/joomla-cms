/****** Object:  Table [#__user_keys] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_keys] (
  [id] int(10) [int] IDENTITY(1,1) NOT NULL,
  [user_id] [nvarchar](255) NOT NULL DEFAULT '',
  [token] [nvarchar](255) NOT NULL DEFAULT '',
  [series] [nvarchar](255) NOT NULL DEFAULT '',
  [invalid] [tinyint] NOT NULL,
  [time] [nvarchar](255) NOT NULL DEFAULT '',
  [uastring] [nvarchar](255) NOT NULL DEFAULT '',
  CONSTRAINT [PK_#__user_keys_id] PRIMARY KEY CLUSTERED
    (
      [id] ASC
    )WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY];
CREATE NONCLUSTERED INDEX [idx_series] ON [#__user_keys]
(
  [series] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
SELECT 31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 0, 0, '{"name":"com_ajax","type":"component","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_AJAX_DESC","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0
UNION ALL
SELECT 105, 'FOF', 'library', 'lib_fof', '', 0, 1, 1, 1, '{"legacy":false,"name":"FOF","type":"library","creationDate":"2013-09-03","author":"Nicholas K. Dionysopoulos \/ Akeeba Ltd","copyright":"(C)2011-2013 Nicholas K. Dionysopoulos","authorEmail":"nicholas@akeebabackup.com","authorUrl":"https:\/\/www.akeebabackup.com","version":"2.1.rc2","description":"Framework-on-Framework (FOF) - A rapid component development framework for Joomla!","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT #__extensions  OFF;

INSERT INTO #__update_sites (name, type, location, enabled, last_check_timestamp) VALUES
('FOF Updates (official releases)', 'extension', 'http://cdn.akeebabackup.com/updates/fof.xml', 1, 0);

INSERT INTO #__update_sites_extensions (update_site_id, extension_id) VALUES
(SCOPE_IDENTITY(), 105);


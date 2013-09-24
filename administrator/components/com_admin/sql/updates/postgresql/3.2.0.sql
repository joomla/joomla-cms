# Placeholder file for database changes for version 3.2.0
INSERT INTO "#__assets" ("id", "parent_id", "lft", "rgt", "level", "name", "title", "rules") VALUES
(36,1,69,70,1, 'com_services', 'com_services', '{}');

INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(30, 'com_services', 'component', 'com_services', '', 1, 1, 0, 0, '{"name":"com_services","type":"component","creationDate":"2013-06-31","author":"Joomla! Project","copyright":"Copyright Info","authorEmail":"Joomla@joomla.com","authorUrl":"http:\/\/joomla.org","version":"1.0.0","description":"Front End Admin Services Configuration Manager","group":""}', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(318, 'mod_admin_services', 'module', 'mod_admin_services', '', 0, 1, 0, 0, '{"name":"mod_admin_services","type":"module","creationDate":"June 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2013 Open Source Matters. All rights\n\t\treserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"MOD_ADMIN_SERVICES_XML_DESCRIPTION","group":""}', '{"config_visible":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "name", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Admin Services', '', '', 1,  'position-7', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_admin_services', 1, 1, '{"config_visible":"1","templates_visible":"1","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES 
(LAST_INSERT_ID(), 0);

INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 0, 0, '{"name":"com_ajax","type":"component","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"COM_AJAX_DESC","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '{"legacy":false,"name":"FOF","type":"library","creationDate":"2013-09-03","author":"Nicholas K. Dionysopoulos \/ Akeeba Ltd","copyright":"(C)2011-2013 Nicholas K. Dionysopoulos","authorEmail":"nicholas@akeebabackup.com","authorUrl":"https:\/\/www.akeebabackup.com","version":"2.1.rc2","description":"Framework-on-Framework (FOF) - A rapid component development framework for Joomla!","group":""}', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 1, 1, 0, '{"name":"plg_twofactorauth_totp","type":"plugin","creationDate":"August 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2013 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.2.0","description":"PLG_TWOFACTORAUTH_TOTP_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0);

ALTER TABLE "#__users" ADD COLUMN "otpKey" varchar(1000) DEFAULT '' NOT NULL;

ALTER TABLE "#__users" ADD COLUMN "otep" varchar(1000) DEFAULT '' NOT NULL;

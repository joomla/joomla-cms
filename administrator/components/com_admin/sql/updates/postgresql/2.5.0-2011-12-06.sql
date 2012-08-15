INSERT INTO  "#__extensions"  ( "extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state" ) VALUES
(436, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 1, '', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(437, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 1, '', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0);

ALTER TABLE "#__update_sites" ADD COLUMN "last_check_timestamp" bigint DEFAULT 0;


UPDATE "#__update_sites" SET ("update_site_id", "name", "type", "location", "enabled") = (1, 'Joomla Core', 'collection', 'http://update.joomla.org/core/list.xml', 1, 0)
WHERE "update_site_id"=1;

INSERT INTO "#__update_sites" 
("update_site_id", "name", "type", "location", "enabled") 
SELECT 
1, 'Joomla Core', 'collection', 'http://update.joomla.org/core/list.xml', 1, 0
WHERE 1 NOT IN (SELECT 1 FROM "#__update_sites" WHERE "update_site_id" = 1);


UPDATE "#__update_sites" SET ("update_site_id", "name", "type", "location", "enabled") = (2, 'Joomla Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', 1, 0)
WHERE "update_site_id"=2;

INSERT INTO "#__update_sites" 
("update_site_id", "name", "type", "location", "enabled") 
SELECT 
2, 'Joomla Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', 1, 0
WHERE 1 NOT IN (SELECT 1 FROM "#__update_sites" WHERE "update_site_id" = 2);
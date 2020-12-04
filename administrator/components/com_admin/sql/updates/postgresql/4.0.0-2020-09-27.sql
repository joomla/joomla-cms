DELETE FROM "#__extensions" WHERE "name" = 'plg_content_imagelazyload' AND "type" = 'plugin' AND "element" = 'imagelazyload' AND "folder" = 'content' AND "client_id" = 0;
DELETE FROM "#__extensions" WHERE "name" = 'plg_authentication_gmail' AND "type" = 'plugin' AND "element" = 'gmail' AND "folder" = 'authentication' AND "client_id" = 0;

--
-- Delete possibly duplicate record for plg_sampledata_multilang
--
DELETE FROM "#__extensions"
 WHERE "name" = 'plg_sampledata_multilang' AND "type" = 'plugin' AND "element" = 'multilang' AND "folder" = 'sampledata' AND "client_id" = 0
   AND "extension_id" < (SELECT MAX("extension_id") FROM "#__extensions" WHERE "name" = 'plg_sampledata_multilang' AND "type" = 'plugin' AND "element" = 'multilang' AND "folder" = 'sampledata' AND "client_id" = 0);

--
-- Enable the remaining plg_sampledata_multilang record in case it has been disabled before
--
UPDATE "#__extensions" SET "enabled" = 1 WHERE "name" = 'plg_sampledata_multilang' AND "type" = 'plugin' AND "element" = 'multilang' AND "folder" = 'sampledata' AND "client_id" = 0;

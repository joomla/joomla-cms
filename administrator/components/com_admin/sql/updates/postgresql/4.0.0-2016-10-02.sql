ALTER TABLE "#__user_keys" DROP COLUMN "invalid";

--
-- Insert the new templates into the database. Set as home if the old template is the active one
--
INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
('atum', 'template', 'atum', '', 1, 1, 1, 0, '{}', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
('cassiopeia', 'template', 'cassiopeia', '', 0, 1, 1, 0, '{}', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0);

INSERT INTO "#__template_styles" ("template", "client_id", "home", "title", "params") VALUES
('atum', 1, (CASE WHEN (SELECT count FROM (SELECT count("id") AS count FROM "#__template_styles" WHERE home = '1' AND client_id = 1 AND "template" IN ('isis', 'hathor')) as c) = 0 THEN '0' ELSE '1' END), 'atum - Default', '{}'),
('cassiopeia', 0, (CASE WHEN (SELECT count FROM (SELECT count("id") AS count FROM "#__template_styles" WHERE home = '1' AND client_id = 0 AND "template" IN ('protostar', 'beez3')) as c) = 0 THEN '0' ELSE '1' END), 'cassiopeia - Default', '{}');

--
-- Now we can clean up the old templates
--
DELETE FROM "#__extensions" WHERE "type" = 'template' AND "element" = 'hathor' AND "client_id" = 1;
DELETE FROM "#__template_styles" WHERE "template" = 'hathor' AND "client_id" = 1;

DELETE FROM "#__template_styles" WHERE "template" = 'isis' AND "client_id" = 1;
DELETE FROM "#__extensions" WHERE "type" = 'template' AND "element" = 'isis' AND "client_id" = 1;

DELETE FROM "#__template_styles" WHERE "template" = 'protostar' AND "client_id" = 0;
DELETE FROM "#__extensions" WHERE "type" = 'template' AND "element" = 'protostar' AND "client_id" = 0;

DELETE FROM "#__template_styles" WHERE "template" = 'beez3' AND "client_id" = 0;
DELETE FROM "#__extensions" WHERE "type" = 'template' AND "element" = 'beez3' AND "client_id" = 0;

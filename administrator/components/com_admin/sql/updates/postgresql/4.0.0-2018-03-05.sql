-- The following statement was moved from below to here and modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE "#__extensions" DROP COLUMN "system_data" /** CAN FAIL **/;

-- From 4.0.0-2016-07-03.sql
-- The following statement was modified for 4.1.1 by removing the "system_data" column.
-- See https://github.com/joomla/joomla-cms/pull/37156
INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
('plg_behaviour_taggable', 'plugin', 'taggable', 'behaviour', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
('plg_behaviour_versionable', 'plugin', 'versionable', 'behaviour', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

-- From 4.0.0-2016-09-22.sql
DELETE FROM "#__extensions" WHERE "type" = 'library' AND "element" = 'phputf8';

-- From 4.0.0-2016-09-28.sql
DELETE FROM "#__extensions" WHERE "type" = 'plugin' AND "element" = 'p3p' AND "folder" = 'system';

-- From 4.0.0-2016-10-02.sql
-- The following statement was modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE "#__user_keys" DROP COLUMN "invalid" /** CAN FAIL **/;

--
-- Insert the new templates into the database. Set as home if the old template is the active one
--

-- The following statement was modified for 4.1.1 by removing the "system_data" column.
-- See https://github.com/joomla/joomla-cms/pull/37156
INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
('atum', 'template', 'atum', '', 1, 1, 1, 0, '{}', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
('cassiopeia', 'template', 'cassiopeia', '', 0, 1, 1, 0, '{}', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

-- The following statement had to be modified for 4.1 by adding the "inheritable" and "parent" columns.
-- See https://github.com/joomla/joomla-cms/pull/36585
INSERT INTO "#__template_styles" ("template", "client_id", "home", "title", "inheritable", "parent", "params") VALUES
('atum', 1, (CASE WHEN (SELECT b."count" FROM (SELECT count(a."id") AS "count" FROM "#__template_styles" a WHERE a."home" = '1' AND a."client_id" = 1 AND a."template" IN ('isis', 'hathor')) AS b) = 0 THEN '0' ELSE '1' END), 'atum - Default', 1, '', '{}'),
('cassiopeia', 0, (CASE WHEN (SELECT d."count" FROM (SELECT count(c."id") AS "count" FROM "#__template_styles" c WHERE c."home" = '1' AND c."client_id" = 0 AND c."template" IN ('protostar', 'beez3')) AS d) = 0 THEN '0' ELSE '1' END), 'cassiopeia - Default', 1, '', '{}');

--
-- Move mod_version to the right position for the atum template
--
UPDATE "#__modules" SET "position" = 'status' WHERE "module" = 'mod_version' AND "client_id" = 1;

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

-- From 4.0.0-2016-10-03.sql
DELETE FROM "#__extensions" WHERE "name" = 'mod_submenu';

-- From 4.0.0-2017-03-18.sql
-- The following statement was moved to the top for 4.1.1.
-- See https://github.com/joomla/joomla-cms/pull/37156
-- ALTER TABLE "#__extensions" DROP COLUMN "system_data";

-- From 4.0.0-2017-04-25.sql
INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
('plg_filesystem_local', 'plugin', 'local', 'filesystem', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
('plg_media-action_crop', 'plugin', 'crop', 'media-action', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
('plg_media-action_resize', 'plugin', 'resize', 'media-action', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
('plg_media-action_rotate', 'plugin', 'rotate', 'media-action', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

-- From 4.0.0-2017-05-31.sql
UPDATE "#__menu" SET "link" = 'index.php?option=com_config&view=config' WHERE "link" = 'index.php?option=com_config&view=config&controller=config.display.config';
UPDATE "#__menu" SET "link" = 'index.php?option=com_config&view=templates' WHERE "link" = 'index.php?option=com_config&view=templates&controller=config.display.templates';

-- From 4.0.0-2017-06-03.sql
-- The following two statements were modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE "#__extensions" ADD COLUMN "changelogurl" text /** CAN FAIL **/;
ALTER TABLE "#__updates" ADD COLUMN "changelogurl" text /** CAN FAIL **/;

-- From 4.0.0-2017-10-10.sql
INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
('plg_system_httpheaders', 'plugin', 'httpheaders', 'system', 0, 0, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

INSERT INTO "#__postinstall_messages" ("extension_id", "title_key", "description_key", "action_key", "language_extension", "language_client_id", "type", "action_file", "action", "condition_file", "condition_method", "version_introduced", "enabled")
SELECT "extension_id", 'PLG_SYSTEM_HTTPHEADERS_POSTINSTALL_INTRODUCTION_TITLE', 'PLG_SYSTEM_HTTPHEADERS_POSTINSTALL_INTRODUCTION_BODY', 'PLG_SYSTEM_HTTPHEADERS_POSTINSTALL_INTRODUCTION_ACTION', 'plg_system_httpheaders', 1, 'action', 'site://plugins/system/httpheaders/postinstall/introduction.php', 'httpheaders_postinstall_action', 'site://plugins/system/httpheaders/postinstall/introduction.php', 'httpheaders_postinstall_condition', '4.0.0', 1 FROM "#__extensions" WHERE "name" = 'files_joomla';

-- From 4.0.0-2018-02-24.sql
DELETE FROM "#__extensions" WHERE "type" = 'library' AND "element" = 'idna_convert';

-- From 4.0.0-2018-03-05.sql
ALTER TABLE "#__modules" ALTER COLUMN "content" DROP NOT NULL;

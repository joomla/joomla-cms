-- From 4.0.0-2018-06-03.sql
-- This has been removed as com_csp has been removed from the final build

-- From 4.0.0-2018-06-26.sql
ALTER TABLE "#__user_notes" ALTER COLUMN "modified_user_id" SET DEFAULT 0;

-- From 4.0.0-2018-07-02.sql
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_extension_namespacemap', 'plugin', 'namespacemap', 'extension', 0, 0, 1, 1, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

-- From 4.0.0-2018-07-19.sql
CREATE TABLE IF NOT EXISTS "#__template_overrides" (
  "id" serial NOT NULL,
  "template" varchar(50) DEFAULT '' NOT NULL,
  "hash_id" varchar(255) DEFAULT '' NOT NULL,
  "extension_id" bigint DEFAULT 0,
  "state" smallint DEFAULT 0 NOT NULL,
  "action" varchar(50) DEFAULT '' NOT NULL,
  "client_id" smallint DEFAULT 0 NOT NULL,
  "created_date" timestamp without time zone NOT NULL,
  "modified_date" timestamp without time zone,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__template_overrides_idx_template" ON "#__template_overrides" ("template");
CREATE INDEX "#__template_overrides_idx_extension_id" ON "#__template_overrides" ("extension_id");

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_installer_override', 'plugin', 'override', 'installer', 0, 1, 1, 1, '', '', '', 0, '1970-01-01 00:00:00', 4, 0),
(0, 'plg_quickicon_overridecheck', 'plugin', 'overridecheck', 'quickicon', 0, 1, 1, 1, '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

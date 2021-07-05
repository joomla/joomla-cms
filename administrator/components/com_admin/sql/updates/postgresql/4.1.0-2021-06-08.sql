--
-- Table structure for table "#__cookiemanager_cookies"
--

CREATE TABLE IF NOT EXISTS "#__cookiemanager_cookies" (
  "id" serial NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(400) NOT NULL,
  "cookie_name" varchar(255) NOT NULL,
  "cookie_desc" varchar(255) NOT NULL,
  "exp_period" varchar(20) NOT NULL,
  "exp_value" integer DEFAULT 0 NOT NULL,
  "catid" integer DEFAULT 0 NOT NULL,
  "published" smallint DEFAULT 1 NOT NULL,
  "ordering" integer DEFAULT 0 NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" integer DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__cookiemanager_cookies_idx_state" on "#__cookiemanager_cookies" ("published");
CREATE INDEX "#__cookiemanager_cookies_idx_catid" on "#__cookiemanager_cookies" ("catid");
CREATE INDEX "#__cookiemanager_cookies_idx_createdby" on "#__cookiemanager_cookies" ("created_by");

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'com_cookiemanager', 'component', 'com_cookiemanager', '', 1, 1, 1, 0, 1, '', '{"policylink":"","position":"bottom"}', '', 0, 0);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_system_cookiemanager', 'plugin', 'cookiemanager', 'system', 0, 1, 1, 0, '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

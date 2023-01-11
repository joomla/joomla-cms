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

--
-- Table structure for table "#__cookiemanager_scripts"
--

CREATE TABLE IF NOT EXISTS "#__cookiemanager_scripts" (
  "id" serial NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(400) NOT NULL,
  "position" integer DEFAULT 4 NOT NULL,
  "type" integer DEFAULT 1 NOT NULL,
  "code" text NOT NULL,
  "catid" integer DEFAULT 0 NOT NULL,
  "published" smallint DEFAULT 1 NOT NULL,
  "ordering" integer DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__cookiemanager_scripts_idx_state" on "#__cookiemanager_scripts" ("published");
CREATE INDEX "#__cookiemanager_scripts_idx_catid" on "#__cookiemanager_scripts" ("catid");

--
-- Table structure for table "#__cookiemanager_consents"
--

CREATE TABLE IF NOT EXISTS "#__cookiemanager_consents" (
  "id" serial NOT NULL,
  "uuid" varchar(32) NOT NULL,
  "ccuuid" varchar(64) NOT NULL,
  "consent_opt_in" varchar(255) NOT NULL,
  "consent_opt_out" varchar(255) NOT NULL,
  "consent_date" varchar(100) NOT NULL,
  "user_agent" varchar(150) NOT NULL,
  "url" varchar(100) NOT NULL,
  PRIMARY KEY ("id")
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'com_cookiemanager', 'component', 'com_cookiemanager', '', 1, 1, 1, 0, 1, '', '{"policylink":"","modal_position":"","consent_expiration":"30"}', '', 0, 0),
(0, 'plg_system_cookiemanager', 'plugin', 'cookiemanager', 'system', 0, 1, 1, 0, 1, '', '', '', 0, 0);

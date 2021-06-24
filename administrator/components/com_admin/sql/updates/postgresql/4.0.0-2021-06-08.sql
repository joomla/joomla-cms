--
-- Table structure for table "#__cookiemanager_cookies"
--

CREATE TABLE IF NOT EXISTS "#__cookiemanager_cookies" (
  "id" serial NOT NULL PRIMARY KEY,
  "title" varchar(255) NOT NULL,
  "alias" varchar(400) NOT NULL,
  "cookies_name" varchar(400) NOT NULL,
  "catid" integer DEFAULT 0 NOT NULL,
  "published" smallint DEFAULT 1 NOT NULL,
  "ordering" integer DEFAULT 0 NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" integer DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL
);
CREATE INDEX "#__cookiemanager_cookies_idx_state" on "#__cookiemanager_cookies" ("published");
CREATE INDEX "#__cookiemanager_cookies_idx_catid" on "#__cookiemanager_cookies" ("catid");
CREATE INDEX "#__cookiemanager_cookies_idx_createdby" on "#__cookiemanager_cookies" ("created_by");

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

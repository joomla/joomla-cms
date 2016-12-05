--
-- Table: #__fields_groups
--
CREATE TABLE "#__fields_groups" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "extension" varchar(255) DEFAULT '' NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__fields_idx_checked_out" ON "#__fields_groups" ("checked_out");
CREATE INDEX "#__fields_idx_state" ON "#__fields_groups" ("state");
CREATE INDEX "#__fields_idx_created_by" ON "#__fields_groups" ("created_by");
CREATE INDEX "#__fields_idx_access" ON "#__fields_groups" ("access");
CREATE INDEX "#__fields_idx_extension" ON "#__fields_groups" ("extension");
CREATE INDEX "#__fields_idx_language" ON "#__fields_groups" ("language");

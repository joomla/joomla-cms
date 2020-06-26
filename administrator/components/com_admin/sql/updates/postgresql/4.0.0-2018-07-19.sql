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


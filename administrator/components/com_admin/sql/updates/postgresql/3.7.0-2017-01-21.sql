-- Drop fields-categories table for com_fields
DROP TABLE `#__fields_categories`;

-- Create forms table for com_fields
CREATE TABLE IF NOT EXISTS `#__fields_forms`
(
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "context" varchar(255) DEFAULT '' NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "is_subform" smallint DEFAULT 0 NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_user_id" bigint DEFAULT 0 NOT NULL,
  "modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__fields_idx_access" ON "#__fields" ("access");
CREATE INDEX "#__fields_idx_checked_out" ON "#__fields" ("checked_out");
CREATE INDEX "#__fields_idx_context" ON "#__fields" ("context");
CREATE INDEX "#__fields_idx_created_user_id" ON "#__fields" ("created_user_id");
CREATE INDEX "#__fields_idx_language" ON "#__fields" ("language");
CREATE INDEX "#__fields_idx_state" ON "#__fields" ("state");

-- Create forms-categories table for com_fields
CREATE TABLE IF NOT EXISTS `#__fields_forms_categories`
(
  "field_id" bigint DEFAULT 0 NOT NULL,
  "category_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("field_id", "category_id")
);

-- Add columns to various com_fields related tables in order to support the forms
ALTER TABLE "#__fields" ADD COLUMN "form_id" int(10) UNSIGNED DEFAULT '0';
ALTER TABLE "#__fields" ADD COLUMN "group_id" int(10) UNSIGNED DEFAULT '0';

ALTER TABLE "#__fields_groups" ADD COLUMN "form_id" int(10) UNSIGNED DEFAULT '0';

ALTER TABLE "#__fields_values" ADD COLUMN "form_id" int(10) UNSIGNED DEFAULT '0';
ALTER TABLE "#__fields_values" ADD COLUMN "index" int(10) UNSIGNED DEFAULT NULL;

-- Insert sub-form plugin

INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(478, 'plg_fields_subform', 'plugin', 'subform', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);

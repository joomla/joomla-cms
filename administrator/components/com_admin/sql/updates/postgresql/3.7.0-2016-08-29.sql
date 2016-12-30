--
-- Table: #__fields
--
CREATE TABLE "#__fields" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "context" varchar(255) DEFAULT '' NOT NULL,
  "group_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "label" varchar(255) DEFAULT '' NOT NULL,
  "default_value" text DEFAULT '' NOT NULL,
  "type" varchar(255) DEFAULT 'text' NOT NULL,
  "options" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "required" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "params" text DEFAULT '' NOT NULL,
  "fieldparams" text DEFAULT '' NOT NULL,
  "attributes" text DEFAULT '' NOT NULL, 
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_user_id" bigint DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__fields_idx_checked_out" ON "#__fields" ("checked_out");
CREATE INDEX "#__fields_idx_state" ON "#__fields" ("state");
CREATE INDEX "#__fields_idx_created_user_id" ON "#__fields" ("created_user_id");
CREATE INDEX "#__fields_idx_access" ON "#__fields" ("access");
CREATE INDEX "#__fields_idx_context" ON "#__fields" ("context");
CREATE INDEX "#__fields_idx_language" ON "#__fields" ("language");

--
-- Table: #__fields_categories
--
CREATE TABLE "#__fields_categories" (
  "field_id" bigint DEFAULT 0 NOT NULL,
  "category_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("field_id", "category_id")
);

--
-- Table: #__fields_groups
--
CREATE TABLE "#__fields_groups" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "context" varchar(255) DEFAULT '' NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
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
CREATE INDEX "#__fields_idx_context" ON "#__fields_groups" ("context");
CREATE INDEX "#__fields_idx_language" ON "#__fields_groups" ("language");

--
-- Table: #__fields_values
--
CREATE TABLE "#__fields_values" (
"field_id" bigint DEFAULT 0 NOT NULL,
"item_id" varchar(255) DEFAULT '' NOT NULL,
"context" varchar(255) DEFAULT '' NOT NULL,
"value" text DEFAULT '' NOT NULL 
);
CREATE INDEX "#__fields_values_idx_field_id" ON "#__fields_values" ("field_id");
CREATE INDEX "#__fields_values_idx_context" ON "#__fields_values" ("context");
CREATE INDEX "#__fields_values_idx_item_id" ON "#__fields_values" ("item_id");

INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(33, 'com_fields', 'component', 'com_fields', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(461, 'plg_system_fields', 'plugin', 'fields', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(462, 'plg_fields_calendar', 'plugin', 'calendar', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(463, 'plg_fields_checkboxes', 'plugin', 'checkboxes', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(464, 'plg_fields_color', 'plugin', 'color', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(465, 'plg_fields_editor', 'plugin', 'editor', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(466, 'plg_fields_gallery', 'plugin', 'gallery', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(467, 'plg_fields_imagelist', 'plugin', 'imagelist', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(468, 'plg_fields_integer', 'plugin', 'integer', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(469, 'plg_fields_list', 'plugin', 'list', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(470, 'plg_fields_media', 'plugin', 'media', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(471, 'plg_fields_radio', 'plugin', 'radio', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(472, 'plg_fields_sql', 'plugin', 'sql', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(473, 'plg_fields_text', 'plugin', 'text', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(474, 'plg_fields_textarea', 'plugin', 'textarea', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(475, 'plg_fields_url', 'plugin', 'url', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(476, 'plg_fields_user', 'plugin', 'user', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(477, 'plg_fields_usergrouplist', 'plugin', 'usergrouplist', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(462, 'plg_fields_gallery', 'plugin', 'gallery', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);


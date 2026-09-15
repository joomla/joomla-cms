DROP TABLE IF EXISTS "#__tags";
CREATE TABLE IF NOT EXISTS "#__tags" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" varchar(255) DEFAULT '' NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "access" bigint DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "metadesc" varchar(1024) NOT NULL,
  "metakey" varchar(1024) DEFAULT '' NOT NULL,
  "metadata" varchar(2048) NOT NULL,
  "created_user_id" integer DEFAULT 0 NOT NULL,
  "created_time" timestamp without time zone NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified_user_id" integer DEFAULT 0 NOT NULL,
  "modified_time" timestamp without time zone NOT NULL,
  "images" text NOT NULL,
  "urls" text NOT NULL,
  "hits" integer DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__tags_cat_idx" ON "#__tags" ("published", "access");
CREATE INDEX "#__tags_idx_access" ON "#__tags" ("access");
CREATE INDEX "#__tags_idx_checkout" ON "#__tags" ("checked_out");
CREATE INDEX "#__tags_idx_path" ON "#__tags" ("path");
CREATE INDEX "#__tags_idx_left_right" ON "#__tags" ("lft", "rgt");
CREATE INDEX "#__tags_idx_alias" ON "#__tags" ("alias");
CREATE INDEX "#__tags_idx_language" ON "#__tags" ("language");

DROP TABLE IF EXISTS "#__contentitem_tag_map";
CREATE TABLE IF NOT EXISTS "#__contentitem_tag_map" (
  "type_alias" varchar(255) NOT NULL DEFAULT '',
  "core_content_id" integer NOT NULL,
  "content_item_id" integer NOT NULL,
  "tag_id" integer NOT NULL,
  "tag_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "type_id" integer NOT NULL,
 PRIMARY KEY ("type_id", "content_item_id", "tag_id")
);
CREATE INDEX "#__contentitem_tag_map_idx_tag_type" ON "#__contentitem_tag_map" ("tag_id", "type_id");
CREATE INDEX "#__contentitem_tag_map_idx_date_id" ON "#__contentitem_tag_map" ("tag_date", "tag_id");
CREATE INDEX "#__contentitem_tag_map_idx_core_content_id" ON "#__contentitem_tag_map" ("core_content_id");

DROP TABLE IF EXISTS "#__content_types";
CREATE TABLE IF NOT EXISTS "#__content_types" (
  "type_id" serial NOT NULL,
  "type_title" varchar(255) NOT NULL DEFAULT '',
  "type_alias" varchar(255) NOT NULL DEFAULT '',
  "table" varchar(2048) NOT NULL DEFAULT '',
  "rules" text NOT NULL,
  "field_mappings" text NOT NULL,
  "router" varchar(255) NOT NULL DEFAULT '',
  "content_history_options" varchar(5120) DEFAULT NULL,
  PRIMARY KEY ("type_id")
);
CREATE INDEX "#__content_types_idx_alias" ON "#__content_types" ("type_alias");

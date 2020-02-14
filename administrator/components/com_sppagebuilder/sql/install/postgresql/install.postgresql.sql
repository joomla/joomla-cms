--
-- Table: #__sppagebuilder
--
CREATE TABLE IF NOT EXISTS "#__sppagebuilder" (
  "id" serial NOT NULL,
  "asset_id" integer DEFAULT 0 NOT NULL,
  "title" character varying(255) NOT NULL,
  "text" text NOT NULL,
  "extension" character varying(255) DEFAULT 'com_sppagebuilder' NOT NULL,
  "extension_view" character varying(255) DEFAULT 'page' NOT NULL,
  "view_id" bigint DEFAULT 0 NOT NULL,
  "active" smallint DEFAULT 0 NOT NULL,
  "published" smallint DEFAULT 1 NOT NULL,
  "catid" integer DEFAULT 0 NOT NULL,
  "access" integer DEFAULT 0 NOT NULL,
  "ordering" integer DEFAULT 0 NOT NULL,
  "created_on" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "og_title" character varying(255) NOT NULL,
  "og_image" character varying(255) NOT NULL,
  "og_description" character varying(255) NOT NULL,
  "language" character varying(7) NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL,
  "css" text NOT NULL,
  PRIMARY KEY ("id")
);

--
-- Table: #__sppagebuilder_integrations
--
CREATE TABLE IF NOT EXISTS "#__sppagebuilder_integrations" (
  "id" serial NOT NULL,
  "title" character varying(255) NOT NULL,
  "description" text NOT NULL,
  "component" character varying(255) NOT NULL,
  "plugin" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);

--
-- Table: #__spmedia
--
CREATE TABLE IF NOT EXISTS "#__spmedia" (
  "id" serial NOT NULL,
  "title" character varying(255) NOT NULL,
  "path" character varying(255) NOT NULL,
  "thumb" character varying(255) NOT NULL,
  "alt" character varying(255) NOT NULL,
  "caption" character varying(2048) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "type" character varying(100) NOT NULL DEFAULT 'image',
  "extension" character varying(100) NOT NULL,
  "created_on" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified_on" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);

--
-- Table: #__sppagebuilder_languages
--
CREATE TABLE IF NOT EXISTS "#__sppagebuilder_languages" (
  "id" serial NOT NULL,
  "title" character varying(255) NOT NULL,
  "description" text NOT NULL,
  "lang_tag" character varying(255) NOT NULL DEFAULT '',
  "lang_key" character varying(100) DEFAULT NULL,
  "version" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);

--
-- Table: #__sppagebuilder_sections
--
CREATE TABLE IF NOT EXISTS "#__sppagebuilder_sections" (
  "id" serial NOT NULL,
  "title" character varying(255) NOT NULL,
  "section" text NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);

--
-- Table: #__sppagebuilder_addons
--
CREATE TABLE IF NOT EXISTS "#__sppagebuilder_addons" (
  "id" serial NOT NULL,
  "title" character varying(255) NOT NULL,
  "code" text NOT NULL,
  "created" timestamp NOT NULL DEFAULT '1970-01-01 00:00:00',
  "created_by" bigint NOT NULL DEFAULT 0,
  PRIMARY KEY ("id")
);
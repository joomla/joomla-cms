ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "catid" integer DEFAULT 0 NOT NULL;
ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "ordering" integer DEFAULT 0 NOT NULL;

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
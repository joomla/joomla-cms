ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "extension" character varying(255) DEFAULT 'com_sppagebuilder' NOT NULL;
ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "extension_view" character varying(255) DEFAULT 'page' NOT NULL;
ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "view_id" bigint DEFAULT 0 NOT NULL;
ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "active" smallint DEFAULT 0 NOT NULL;
-- ALTER TABLE #__sppagebuilder RENAME IF EXISTS "created_time" TO "created_on";
-- ALTER TABLE #__sppagebuilder RENAME IF EXISTS "created_user_id" TO "created_by";
-- ALTER TABLE #__sppagebuilder RENAME IF EXISTS "modified_time" TO "modified";
-- ALTER TABLE #__sppagebuilder RENAME IF EXISTS "modified_user_id" TO "modified_by";
ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "checked_out" integer DEFAULT 0 NOT NULL;
ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL;
ALTER TABLE #__sppagebuilder ADD IF NOT EXISTS "css" text NOT NULL;
ALTER TABLE #__sppagebuilder DROP IF EXISTS "alias";
ALTER TABLE #__sppagebuilder DROP IF EXISTS "page_layout";

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

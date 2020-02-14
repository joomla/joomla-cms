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
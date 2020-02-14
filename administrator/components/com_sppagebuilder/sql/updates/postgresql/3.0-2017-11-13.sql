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
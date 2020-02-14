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

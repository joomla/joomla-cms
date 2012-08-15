CREATE TABLE "#__overrider" (
  -- Primary Key
  "id" serial NOT NULL,
  "constant" character varying(255) NOT NULL,
  "string" text NOT NULL,
  "file" character varying(255) NOT NULL,
  PRIMARY KEY ("id")
);
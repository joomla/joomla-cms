--
-- Table structure for table "#__schemaorg"
--

CREATE TABLE IF NOT EXISTS "#__schemaorg"
(
  "id" serial NOT NULL,
  "itemId" integer,
  "context" varchar(100),
  "schemaType" varchar(100),
  "schemaForm" text,
  "schema" text,
  PRIMARY KEY ("id")
);
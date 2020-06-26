--
-- Table structure for table `#__csp`
--

CREATE TABLE IF NOT EXISTS "#__csp" (
  "id" serial NOT NULL,
  "document_uri" varchar(500) NOT NULL DEFAULT '',
  "blocked_uri" varchar(500) NOT NULL DEFAULT '',
  "directive" varchar(500) NOT NULL DEFAULT '',
  "client" varchar(500) NOT NULL DEFAULT '',
  "created" timestamp without time zone NOT NULL,
  "modified"  timestamp without time zone NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);


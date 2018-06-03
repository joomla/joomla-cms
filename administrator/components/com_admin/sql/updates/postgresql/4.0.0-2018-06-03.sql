--
-- Table structure for table `#__csp`
--

CREATE TABLE IF NOT EXISTS "#__csp" (
  "id" int(11) NOT NULL AUTO_INCREMENT,
  "document_uri" varchar(500) NOT NULL DEFAULT '',
  "blocked_uri" varchar(500) NOT NULL DEFAULT '',
  "directive" varchar(500) NOT NULL DEFAULT '',
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified"  timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
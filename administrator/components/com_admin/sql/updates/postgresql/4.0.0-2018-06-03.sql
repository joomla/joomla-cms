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

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'com_csp', 'component', 'com_csp', '', 1, 1, 1, 0, '', '{}', 0, '1970-01-01 00:00:00', 0, 0);

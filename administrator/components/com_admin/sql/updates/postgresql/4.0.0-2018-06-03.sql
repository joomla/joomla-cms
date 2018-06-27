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
);

INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state", "namespace") VALUES
(35, 'com_csp', 'component', 'com_csp', ' ', 0, 0, 1, 0, '', '{}', 0, '1970-01-01 00:00:00', 0, 0, 'Joomla\\Component\\Csp');

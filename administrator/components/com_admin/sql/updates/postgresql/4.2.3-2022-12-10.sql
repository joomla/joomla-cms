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

-- Add plugins to "#__extensions"
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'plg_schemaorg_book', 'plugin', 'book', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 0, 0),
(0, 'plg_schemaorg_organization', 'plugin', 'organization', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 0, 0);

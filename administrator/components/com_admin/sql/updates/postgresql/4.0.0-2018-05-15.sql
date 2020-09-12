--
-- Table structure for table "#__workflows"
--

CREATE TABLE IF NOT EXISTS "#__workflows" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "description" text NOT NULL,
  "extension" varchar(50) NOT NULL,
  "default" smallint NOT NULL  DEFAULT 0,
  "ordering" bigint NOT NULL DEFAULT 0,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
 );

CREATE INDEX "#__workflows_idx_asset_id" ON "#__workflows" ("asset_id");
CREATE INDEX "#__workflows_idx_title" ON "#__workflows" ("title");
CREATE INDEX "#__workflows_idx_extension" ON "#__workflows" ("extension");
CREATE INDEX "#__workflows_idx_default" ON "#__workflows" ("default");
CREATE INDEX "#__workflows_idx_created" ON "#__workflows" ("created");
CREATE INDEX "#__workflows_idx_created_by" ON "#__workflows" ("created_by");
CREATE INDEX "#__workflows_idx_modified" ON "#__workflows" ("modified");
CREATE INDEX "#__workflows_idx_modified_by" ON "#__workflows" ("modified_by");
CREATE INDEX "#__workflows_idx_checked_out" ON "#__workflows" ("checked_out");

INSERT INTO "#__workflows" ("id", "asset_id", "published", "title", "description", "extension", "default", "ordering", "created", "created_by", "modified", "modified_by", "checked_out_time", "checked_out") VALUES
(1, 0, 1, 'COM_WORKFLOW_BASIC_WORKFLOW', '', 'com_content.article', 1, 1, CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0);

SELECT setval('#__workflows_id_seq', 2, false);

--
-- Table structure for table "#__workflow_associations"
--

CREATE TABLE IF NOT EXISTS "#__workflow_associations" (
  "item_id" bigint DEFAULT 0 NOT NULL,
  "stage_id" bigint DEFAULT 0 NOT NULL,
  "extension" varchar(50) NOT NULL,
  PRIMARY KEY ("item_id", "extension")
);
CREATE INDEX "#__workflow_associations_idx_item_stage_extension" ON "#__workflow_associations" ("item_id", "stage_id", "extension");
CREATE INDEX "#__workflow_associations_idx_item_id" ON "#__workflow_associations" ("item_id");
CREATE INDEX "#__workflow_associations_idx_stage_id" ON "#__workflow_associations" ("stage_id");
CREATE INDEX "#__workflow_associations_idx_extension" ON "#__workflow_associations" ("extension");

COMMENT ON COLUMN "#__workflow_associations"."item_id" IS 'Extension table id value';
COMMENT ON COLUMN "#__workflow_associations"."stage_id" IS 'Foreign Key to #__workflow_stages.id';

--
-- Table structure for table "#__workflow_stages"
--

CREATE TABLE IF NOT EXISTS "#__workflow_stages" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "workflow_id" bigint DEFAULT 0 NOT NULL,
  "published" smallint NOT NULL  DEFAULT 0,
  "title" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "default" smallint NOT NULL  DEFAULT 0,
  "checked_out_time" timestamp without time zone,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__workflow_stages_idx_workflow_id" ON "#__workflow_stages" ("workflow_id");
CREATE INDEX "#__workflow_stages_idx_title" ON "#__workflow_stages" ("title");
CREATE INDEX "#__workflow_stages_idx_asset_id" ON "#__workflow_stages" ("asset_id");
CREATE INDEX "#__workflow_stages_idx_default" ON "#__workflow_stages" ("default");
CREATE INDEX "#__workflow_stages_idx_checked_out" ON "#__workflow_stages" ("checked_out");

--
-- Dumping data for table "#__workflow_stages"
--

INSERT INTO "#__workflow_stages" ("id", "asset_id", "ordering", "workflow_id", "published", "title", "description", "default", "checked_out_time", "checked_out") VALUES
(1, 0, 1, 1, 1, 'COM_WORKFLOW_BASIC_STAGE', '', 1, NULL, 0);

SELECT setval('#__workflow_stages_id_seq', 2, false);

--
-- Table structure for table "#__workflow_transitions"
--

CREATE TABLE IF NOT EXISTS "#__workflow_transitions" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "workflow_id" bigint DEFAULT 0 NOT NULL,
  "published" smallint NOT NULL  DEFAULT 0,
  "title" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "from_stage_id" bigint DEFAULT 0 NOT NULL,
  "to_stage_id" bigint DEFAULT 0 NOT NULL,
  "options" text NOT NULL,
  "checked_out_time" timestamp without time zone,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
 );
CREATE INDEX "#__workflow_transitions_idx_title" ON "#__workflow_transitions" ("title");
CREATE INDEX "#__workflow_transitions_idx_asset_id" ON "#__workflow_transitions" ("asset_id");
CREATE INDEX "#__workflow_transitions_idx_from_stage_id" ON "#__workflow_transitions" ("from_stage_id");
CREATE INDEX "#__workflow_transitions_idx_to_stage_id" ON "#__workflow_transitions" ("to_stage_id");
CREATE INDEX "#__workflow_transitions_idx_workflow_id" ON "#__workflow_transitions" ("workflow_id");
CREATE INDEX "#__workflow_transitions_idx_checked_out" ON "#__workflow_transitions" ("checked_out");

INSERT INTO "#__workflow_transitions" ("id", "asset_id", "published", "ordering", "workflow_id", "title", "description", "from_stage_id", "to_stage_id", "options", "checked_out_time", "checked_out") VALUES
(1, 0, 1, 1, 1, 'Unpublish', '', -1, 1, '{"publishing":"0"}', NULL, 0),
(2, 0, 1, 2, 1, 'Publish', '', -1, 1, '{"publishing":"1"}', NULL, 0),
(3, 0, 1, 3, 1, 'Trash', '', -1, 1, '{"publishing":"-2"}', NULL, 0),
(4, 0, 1, 4, 1, 'Archive', '', -1, 1, '{"publishing":"2"}', NULL, 0),
(5, 0, 1, 5, 1, 'Feature', '', -1, 1, '{"featuring":"1"}', NULL, 0),
(6, 0, 1, 6, 1, 'Unfeature', '', -1, 1, '{"featuring":"0"}', NULL, 0),
(7, 0, 1, 7, 1, 'Publish & Feature', '', -1, 1, '{"publishing":"1","featuring":"1"}', NULL, 0);

SELECT setval('#__workflow_transitions_id_seq', 8, false);

--
-- Creating extension entry
--
-- Note that the old pseudo null dates have to be used for the "checked_out_time"
-- column because the conversion to real null dates will be done with a later
-- update SQL script.
--

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'com_workflow', 'component', 'com_workflow', '', 1, 1, 0, 1, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_workflow_publishing', 'plugin', 'publishing', 'workflow', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_workflow_featuring', 'plugin', 'featuring', 'workflow', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_workflow_notification', 'plugin', 'notification', 'workflow', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

--
-- Creating Associations for existing content
--
INSERT INTO "#__workflow_associations" ("item_id", "stage_id", "extension")
SELECT "id", 1, 'com_content.article' FROM "#__content";

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
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
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

INSERT INTO "#__workflows" ("id", "asset_id", "published", "title", "description", "extension", "default", "ordering", "created", "created_by", "modified", "modified_by") VALUES
(1, 0, 1, 'Joomla! Default', '', 'com_content', 1, 1, '1970-01-01 00:00:00', 0, '1970-01-01 00:00:00', 0);

--
-- Table structure for table "#__workflow_associations"
--

CREATE TABLE IF NOT EXISTS "#__workflow_associations" (
  "item_id" bigint DEFAULT 0 NOT NULL,
  "stage_id" bigint DEFAULT 0 NOT NULL,
  "extension" varchar(50) NOT NULL,
  PRIMARY KEY ("item_id", "stage_id", "extension")
);
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
  "condition" bigint DEFAULT 0 NOT NULL,
  "default" smallint NOT NULL  DEFAULT 0,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__workflow_stages_idx_workflow_id" ON "#__workflow_stages" ("workflow_id");
CREATE INDEX "#__workflow_stages_idx_title" ON "#__workflow_stages" ("title");
CREATE INDEX "#__workflow_stages_idx_asset_id" ON "#__workflow_stages" ("asset_id");
CREATE INDEX "#__workflow_stages_idx_default" ON "#__workflow_stages" ("default");

--
-- Dumping data for table "#__workflow_stages"
--

INSERT INTO "#__workflow_stages" ("id", "asset_id", "ordering", "workflow_id", "published", "title", "description", "condition", "default") VALUES
(1, 0, 1, 1, 1, 'Unpublished', '', 0, 0),
(2, 0, 2, 1, 1, 'Published', '', 1, 1),
(3, 0, 3, 1, 1, 'Trashed', '', -2, 0),
(4, 0, 4, 1, 1, 'Archived', '', 2, 0);

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
  PRIMARY KEY ("id")
 );
CREATE INDEX "#__workflow_transitions_idx_title" ON "#__workflow_transitions" ("title");
CREATE INDEX "#__workflow_transitions_idx_asset_id" ON "#__workflow_transitions" ("asset_id");
CREATE INDEX "#__workflow_transitions_idx_from_stage_id" ON "#__workflow_transitions" ("from_stage_id");
CREATE INDEX "#__workflow_transitions_idx_to_stage_id" ON "#__workflow_transitions" ("to_stage_id");
CREATE INDEX "#__workflow_transitions_idx_workflow_id" ON "#__workflow_transitions" ("workflow_id");

INSERT INTO "#__workflow_transitions" ("id", "asset_id", "published", "ordering", "workflow_id", "title", "description", "from_stage_id", "to_stage_id") VALUES
(1, 0, 1, 1, 1, 'Unpublish', '', -1, 1),
(2, 0, 1, 2, 1, 'Publish', '', -1, 2),
(3, 0, 1, 3, 1, 'Trash', '', -1, 3),
(4, 0, 1, 4, 1, 'Archive', '', -1, 4);

--
-- Creating extension entry
--

INSERT INTO "#__extensions" ("extension_id", "package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state", "namespace") VALUES
(35, 0, 'com_workflow', 'component', 'com_workflow', '', 1, 1, 0, 0, '', '{}', 0, '1970-01-01 00:00:00', 0, 0, 'Joomla\\Component\\Workflow');

--
-- Creating Associations for existing content
--
INSERT INTO "#__workflow_associations" ("item_id", "stage_id", "extension")
SELECT "id", CASE WHEN "state" = -2 THEN 3 WHEN "state" = 0 THEN 1 WHEN "state" = 2 THEN 4 ELSE 2 END, 'com_content' FROM "#__content";

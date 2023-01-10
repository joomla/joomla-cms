--
-- Insert a new workflow for com_modules
--
INSERT INTO "#__workflows" ("id", "asset_id", "published", "title", "description", "extension", "default", "ordering", "created", "created_by", "modified", "modified_by", "checked_out_time", "checked_out") VALUES
(NULL, 0, 1, 'COM_WORKFLOW_BASIC_WORKFLOW_MODULES', '', 'com_modules.module', 1, 1, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, NULL, 0);

--
-- Inserting the default stage for the new workflow
--
INSERT INTO "#__workflow_stages" ("id", "asset_id", "ordering", "workflow_id", "published", "title", "description", "default", "checked_out_time", "checked_out")
SELECT NULL, 0, 1, "id", 1, 'COM_WORKFLOW_BASIC_STAGE', '', 1, NULL, 0 FROM "#__workflows" WHERE "title" = 'COM_WORKFLOW_BASIC_WORKFLOW_MODULES';

--
-- Inserting the transitions for the new workflow
--
INSERT INTO "#__workflow_transitions" ("id", "asset_id", "published", "ordering", "workflow_id", "title", "description", "from_stage_id", "to_stage_id", "options")
SELECT NULL, 0, 1, NULL, "w"."id", 'Unpublish', '', -1, "s"."id", '{"publishing":"0"}' FROM "#__workflows" as "w" INNER JOIN "#__workflow_stages" as "s" ON "w"."title" = 'COM_WORKFLOW_BASIC_WORKFLOW_MODULES' AND "s"."title" = 'COM_WORKFLOW_BASIC_STAGE' AND "s"."extension" = 'com_modules.module';

INSERT INTO "#__workflow_transitions" ("id", "asset_id", "published", "ordering", "workflow_id", "title", "description", "from_stage_id", "to_stage_id", "options")
SELECT NULL, 0, 1, NULL, "w"."id", 'Publish', '', -1, "s"."id", '{"publishing":"1"}' FROM "#__workflows" as "w" INNER JOIN "#__workflow_stages" as "s" ON "w"."title" = 'COM_WORKFLOW_BASIC_WORKFLOW_MODULES' AND "s"."title" = 'COM_WORKFLOW_BASIC_STAGE' AND "s"."extension" = 'com_modules.module';

INSERT INTO "#__workflow_transitions" ("id", "asset_id", "published", "ordering", "workflow_id", "title", "description", "from_stage_id", "to_stage_id", "options")
SELECT NULL, 0, 1, NULL, "w"."id", 'Trash', '', -1, "s"."id", '{"publishing":"-2"}' FROM "#__workflows" as "w" INNER JOIN "#__workflow_stages" as "s" ON "w"."title" = 'COM_WORKFLOW_BASIC_WORKFLOW_MODULES' AND "s"."title" = 'COM_WORKFLOW_BASIC_STAGE' AND "s"."workflow_id" = "w"."id";

--
-- Creating Associations for existing modules
--
INSERT INTO "#__workflow_associations" ("item_id", "stage_id", "extension") 
SELECT "m"."id", "s"."id", 'com_modules.module'
FROM "#__modules" as "m",
(
   "#__workflow_stages" as "s"
   INNER JOIN "#__workflows" as "w"
   ON "w"."title" = 'COM_WORKFLOW_BASIC_WORKFLOW_MODULES'
   AND "s"."title" = 'COM_WORKFLOW_BASIC_STAGE'
   AND "s"."workflow_id" = "w"."id"
);
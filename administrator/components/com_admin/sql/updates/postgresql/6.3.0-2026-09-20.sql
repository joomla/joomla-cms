CREATE TABLE IF NOT EXISTS "#__workflow_automation_rules" (
    "id" serial NOT NULL,
    "transition_id" integer NOT NULL,
    "published" smallint DEFAULT 0 NOT NULL,
    "ordering" integer DEFAULT 0 NOT NULL,
    "rule_type" varchar(20) DEFAULT 'delay' NOT NULL,
    "delay_value" integer,
    "delay_unit" varchar(10),
    "cron_expression" varchar(100),
    "item_filter" text,
    "fire_condition" text,
    "run_as_user_id" integer DEFAULT 0 NOT NULL,
    "created" timestamp without time zone NOT NULL,
    "created_by" integer DEFAULT 0 NOT NULL,
    "modified" timestamp without time zone NOT NULL,
    "modified_by" integer DEFAULT 0 NOT NULL,
    PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "#__workflow_automation_rules_idx_transition" ON "#__workflow_automation_rules" ("transition_id");

CREATE INDEX "#__workflow_automation_rules_idx_published" ON "#__workflow_automation_rules" ("published");

COMMENT ON COLUMN "#__workflow_automation_rules"."transition_id" IS 'Foreign Key to #__workflow_transitions.id';

COMMENT ON COLUMN "#__workflow_automation_rules"."rule_type" IS 'delay or cron';

COMMENT ON COLUMN "#__workflow_automation_rules"."delay_unit" IS 'minutes, hours, days, months';

COMMENT ON COLUMN "#__workflow_automation_rules"."item_filter" IS 'JSON filter tree: which items this rule applies to (evaluated at selection)';

COMMENT ON COLUMN "#__workflow_automation_rules"."fire_condition" IS 'JSON expression tree: gate evaluated live at fire time';

COMMENT ON COLUMN "#__workflow_automation_rules"."run_as_user_id" IS 'User identity used to execute the transition';

CREATE TABLE IF NOT EXISTS "#__workflow_item_state" (
    "id" serial NOT NULL,
    "item_id" integer DEFAULT 0 NOT NULL,
    "extension" varchar(50) NOT NULL,
    "stage_id" integer NOT NULL,
    "entered_at" timestamp without time zone NOT NULL,
    "triggered_by" varchar(20) DEFAULT 'manual' NOT NULL,
    "requires_intervention" smallint DEFAULT 0 NOT NULL,
    "last_checked_at" timestamp without time zone,
	"last_failure_at" timestamp without time zone,
	"last_failure_reason" varchar(500),
    PRIMARY KEY ("id"),
    CONSTRAINT "#__workflow_item_state_idx_item_extension" UNIQUE ("item_id", "extension")
);

CREATE INDEX "#__workflow_item_state_idx_stage_entered" ON "#__workflow_item_state" ("stage_id", "entered_at");

CREATE INDEX "#__workflow_item_state_idx_requires_intervention" ON "#__workflow_item_state" ("requires_intervention");

COMMENT ON COLUMN "#__workflow_item_state"."item_id" IS 'Extension table id value';

COMMENT ON COLUMN "#__workflow_item_state"."stage_id" IS 'Foreign Key to #__workflow_stages.id';

COMMENT ON COLUMN "#__workflow_item_state"."entered_at" IS 'When the item arrived in stage_id';

COMMENT ON COLUMN "#__workflow_item_state"."triggered_by" IS 'Determine if a transition was triggered manually or by the automation';

COMMENT ON COLUMN "#__workflow_item_state"."requires_intervention" IS 'Set when an automated transition failed; excluded from the scheduler until an admin clears it';

COMMENT ON COLUMN "#__workflow_item_state"."last_checked_at" IS 'When the scheduler last considered this item; null means never';

COMMENT ON COLUMN "#__workflow_item_state"."last_failure_at" IS 'When this item last could not be evaluated; null means it evaluated cleanly';

COMMENT ON COLUMN "#__workflow_item_state"."last_failure_reason" IS 'Why it could not be evaluated, so the same failure is only reported once';

CREATE TABLE IF NOT EXISTS "#__workflow_automation_log" (
    "id" serial NOT NULL,
    "rule_id" integer,
    "item_id" integer DEFAULT 0 NOT NULL,
    "extension" varchar(50) NOT NULL,
    "transition_id" integer NOT NULL,
    "from_stage_id" integer DEFAULT 0 NOT NULL,
    "to_stage_id" integer DEFAULT 0 NOT NULL,
    "run_as_user_id" integer DEFAULT 0 NOT NULL,
    "trigger_type" varchar(20) DEFAULT 'rule' NOT NULL,
    "exit_code" smallint DEFAULT 0 NOT NULL,
    "note" varchar(500),
    "executed_at" timestamp without time zone NOT NULL,
    PRIMARY KEY ("id")
);

CREATE INDEX "#__workflow_automation_log_idx_item_id" ON "#__workflow_automation_log" ("item_id");

CREATE INDEX "#__workflow_automation_log_idx_executed_at" ON "#__workflow_automation_log" ("executed_at");

CREATE INDEX "#__workflow_automation_log_idx_exit_code" ON "#__workflow_automation_log" ("exit_code");

COMMENT ON COLUMN "#__workflow_automation_log"."rule_id" IS 'Foreign Key to #__workflow_automation_rules.id';

COMMENT ON COLUMN "#__workflow_automation_log"."exit_code" IS '0 ok, 1 permission denied, 2 invalid transition, 3 exception, 4 no longer applicable';

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'plg_task_workflowtransition', 'plugin', 'workflowtransition', 'task', 0, 1, 1, 0, 1, '', '{}', '', 10, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" e WHERE e."type" = 'plugin' AND e."element" = 'workflowtransition' AND e."folder" = 'task' AND e."client_id" = 0);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'plg_workflow_automation', 'plugin', 'automation', 'workflow', 0, 1, 1, 0, 1, '', '{}', '', 4, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" e WHERE e."type" = 'plugin' AND e."element" = 'automation' AND e."folder" = 'workflow' AND e."client_id" = 0);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'plg_workflow_remotecheck', 'plugin', 'remotecheck', 'workflow', 0, 0, 1, 0, 1, '', '{}', '', 5, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" e WHERE e."type" = 'plugin' AND e."element" = 'remotecheck' AND e."folder" = 'workflow' AND e."client_id" = 0);

-- See the MySQL file for why entered_at is the upgrade time rather than a guess.
INSERT INTO "#__workflow_item_state" ("item_id", "extension", "stage_id", "entered_at", "triggered_by", "requires_intervention")
SELECT a."item_id", a."extension", a."stage_id", timezone('UTC', now()), 'manual', 0
FROM "#__workflow_associations" a
LEFT JOIN "#__workflow_item_state" s ON s."item_id" = a."item_id" AND s."extension" = a."extension"
WHERE s."id" IS NULL;

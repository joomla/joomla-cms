--
-- Table structure for table "#__scheduler_tasks"
--

CREATE TABLE IF NOT EXISTS "#__scheduler_tasks" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) NOT NULL,
  "type" varchar(128) NOT NULL,
  "execution_rules" text,
  "cron_rules" text,
  "state" smallint DEFAULT 0 NOT NULL,
  "last_exit_code" integer DEFAULT 0 NOT NULL,
  "last_execution" timestamp without time zone,
  "next_execution" timestamp without time zone,
  "times_executed" integer DEFAULT 0 NOT NULL,
  "times_failed" integer DEFAULT 0,
  "locked" timestamp without time zone,
  "priority" smallint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "cli_exclusive" smallint DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "note" text,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__scheduler_tasks_idx_type" ON "#__scheduler_tasks" ("type");
CREATE INDEX "#__scheduler_tasks_idx_state" ON "#__scheduler_tasks" ("state");
CREATE INDEX "#__scheduler_tasks_idx_last_exit" ON "#__scheduler_tasks" ("last_exit_code");
CREATE INDEX "#__scheduler_tasks_idx_next_exec" ON "#__scheduler_tasks" ("next_execution");
CREATE INDEX "#__scheduler_tasks_idx_locked" ON "#__scheduler_tasks" ("locked");
CREATE INDEX "#__scheduler_tasks_idx_priority" ON "#__scheduler_tasks" ("priority");
CREATE INDEX "#__scheduler_tasks_idx_cli_exclusive" ON "#__scheduler_tasks" ("cli_exclusive");
CREATE INDEX "#__scheduler_tasks_idx_checked_out" ON "#__scheduler_tasks" ("checked_out");

-- Add "com_scheduler" to "#__extensions"
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'com_scheduler', 'component', 'com_scheduler', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0);

-- Add plugins to "#__extensions"
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'plg_system_schedulerunner', 'plugin', 'schedulerunner', 'system', 0, 1, 1, 0, 0, '', '{}', '', 15, 0),
(0, 'plg_system_tasknotification', 'plugin', 'tasknotification', 'system', 0, 1, 1, 0, 1, '', '', '', 22, 0),
(0, 'plg_task_checkfiles', 'plugin', 'checkfiles', 'task', 0, 1, 1, 0, 0, '', '{}', '', 15, 0),
(0, 'plg_task_demotasks', 'plugin', 'demotasks', 'task', 0, 1, 1, 0, 0, '', '{}', '', 15, 0),
(0, 'plg_task_requests', 'plugin', 'requests', 'task', 0, 1, 1, 0, 0, '', '{}', '', 15, 0),
(0, 'plg_task_sitestatus', 'plugin', 'sitestatus', 'task', 0, 1, 1, 0, 0, '', '{}', '', 15, 0);

-- Add com_scheduler to action logs extensions
INSERT INTO "#__action_logs_extensions" ("extension") VALUES ('com_scheduler');

INSERT INTO "#__action_log_config" ("type_title", "type_alias", "id_holder", "title_holder", "table_name", "text_prefix") VALUES
('task', 'com_scheduler.task', 'id', 'title', '#__scheduler_tasks', 'PLG_ACTIONLOG_JOOMLA');

-- Add mail templates
INSERT INTO "#__mail_templates" ("template_id", "extension", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES
('plg_system_tasknotification.failure_mail', 'plg_system_tasknotification', '', 'PLG_SYSTEM_TASK_NOTIFICATION_FAILURE_MAIL_SUBJECT', 'PLG_SYSTEM_TASK_NOTIFICATION_FAILURE_MAIL_BODY', '', '', '{"tags": ["task_id", "task_title", "exit_code", "exec_data_time", "task_output"]}'),
('plg_system_tasknotification.fatal_recovery_mail', 'plg_system_tasknotification', '', 'PLG_SYSTEM_TASK_NOTIFICATION_FATAL_MAIL_SUBJECT', 'PLG_SYSTEM_TASK_NOTIFICATION_FATAL_MAIL_BODY', '', '', '{"tags": ["task_id", "task_title"]}'),
('plg_system_tasknotification.orphan_mail', 'plg_system_tasknotification', '', 'PLG_SYSTEM_TASK_NOTIFICATION_ORPHAN_MAIL_SUBJECT', 'PLG_SYSTEM_TASK_NOTIFICATION_ORPHAN_MAIL_BODY', '', '', '{"tags": ["task_id", "task_title"]}'),
('plg_system_tasknotification.success_mail', 'plg_system_tasknotification', '', 'PLG_SYSTEM_TASK_NOTIFICATION_SUCCESS_MAIL_SUBJECT', 'PLG_SYSTEM_TASK_NOTIFICATION_SUCCESS_MAIL_BODY', '', '', '{"tags":["task_id", "task_title", "exec_data_time", "task_output"]}');

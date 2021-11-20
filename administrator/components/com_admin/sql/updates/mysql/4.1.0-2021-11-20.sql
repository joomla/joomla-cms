--
-- Table structure for table `#__scheduler_tasks`
--

CREATE TABLE IF NOT EXISTS `#__scheduler_tasks` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `asset_id` int NOT NULL UNIQUE DEFAULT '0',
    `title` varchar(255) NOT NULL DEFAULT '',
    `type` varchar(128) NOT NULL COMMENT 'unique identifier for job defined by plugin',
    `execution_rules` text COMMENT 'Execution Rules, Unprocessed',
    `cron_rules` text COMMENT 'Processed execution rules, crontab-like JSON form',
    `state` tinyint NOT NULL DEFAULT FALSE,
    `last_exit_code` int NOT NULL DEFAULT '0' COMMENT 'Exit code when job was last run',
    `last_execution` datetime COMMENT 'Timestamp of last run',
    `next_execution` datetime COMMENT 'Timestamp of next (planned) run, referred for execution on trigger',
    `times_executed` int DEFAULT '0' COMMENT 'Count of successful triggers',
    `times_failed` int DEFAULT '0' COMMENT 'Count of failures',
    `locked` datetime,
    `priority` smallint NOT NULL DEFAULT '0',
    `ordering` int NOT NULL DEFAULT 0 COMMENT 'Configurable list ordering',
    `params` text NOT NULL,
    `note` text,
    `created` datetime NOT NULL,
    `created_by` int UNSIGNED NOT NULL DEFAULT '0',
    `checked_out` int unsigned,
    `checked_out_time` datetime,
    PRIMARY KEY (id),
    KEY `idx_type` (`type`),
    KEY `idx_state` (`state`),
    KEY `idx_last_exit` (`last_exit_code`),
    KEY `idx_next_exec` (`next_execution`),
    KEY `idx_locked` (`locked`),
    KEY `idx_priority` (`priority`),
    KEY `idx_checked_out` (`checked_out`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
('plg_system_tasknotification.failure_mail', 'plg_system_tasknotification', '', 'PLG_SYSTEM_TASK_NOTIFICATION_FAILURE_MAIL_SUBJECT', 'PLG_SYSTEM_TASK_NOTIFICATION_FAILURE_MAIL_BODY', '', '', '{"tags": ["task_id", "task_title", "exit_code", "exec_data_time", "task_output"]}'),
('plg_system_tasknotification.fatal_recovery_mail', 'plg_system_tasknotification', '', 'PLG_SYSTEM_TASK_NOTIFICATION_FATAL_MAIL_SUBJECT', 'PLG_SYSTEM_TASK_NOTIFICATION_FATAL_MAIL_BODY', '', '', '{"tags": ["task_id", "task_title"]}'),
('plg_system_tasknotification.orphan_mail', 'plg_system_tasknotification', '', 'PLG_SYSTEM_TASK_NOTIFICATION_ORPHAN_MAIL_SUBJECT', 'PLG_SYSTEM_TASK_NOTIFICATION_ORPHAN_MAIL_BODY', '', '', '{"tags": ["task_id", "task_title", ""]}'),
('plg_system_tasknotification.success_mail', 'plg_system_tasknotification', '', 'PLG_SYSTEM_TASK_NOTIFICATION_SUCCESS_MAIL_SUBJECT', 'PLG_SYSTEM_TASK_NOTIFICATION_SUCCESS_MAIL_BODY', '', '', '{"tags":["task_id", "task_title", "exec_data_time", "task_output"]}');

-- Add `com_scheduler` to `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`,
							 `protected`, `locked`, `manifest_cache`, `params`, `custom_data`)
VALUES (0, 'com_scheduler', 'component', 'com_scheduler', '', 1, 1, 1, 0, 1, '', '', '');

-- Add `plg_task_demotasks` to `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`,
							 `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
VALUES (0, 'plg_task_demotasks', 'plugin', 'demotasks', 'task', 0, 1, 1, 0, 0, '', '{}', '', 15, 0);

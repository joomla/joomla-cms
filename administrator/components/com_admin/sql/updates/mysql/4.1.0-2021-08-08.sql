--
-- Table structure for table `#__scheduler_tasks`
--

CREATE TABLE IF NOT EXISTS `#__scheduler_tasks` (
	`id` int NOT NULL AUTO_INCREMENT,
	`asset_id` int NOT NULL UNIQUE DEFAULT '0',
	`title` varchar(128) NOT NULL UNIQUE,
	-- Job type. Can execute a script or plugin routine
	`type` varchar(1024) NOT NULL COMMENT 'unique identifier for job defined by plugin',
	-- Trigger type, default to PseudoCron (compatible everywhere).
	`trigger` enum ('pseudo_cron', 'cron', 'visit_count') NOT NULL DEFAULT 'pseudo_cron' COMMENT 'Defines how the task is triggered',
	`execution_rules` text COMMENT 'Execution Rules, Unprocessed',
	`cron_rules` text COMMENT 'Processed execution rules, crontab-like JSON form',
	`state` tinyint NOT NULL DEFAULT FALSE,
	`last_exit_code` int NOT NULL DEFAULT '0' COMMENT 'Exit code when job was last run',
	`last_execution` datetime COMMENT 'Timestamp of last run',
	`next_execution` datetime COMMENT 'Timestamp of next (planned) run, referred for execution on trigger',
	`times_executed` int DEFAULT '0' COMMENT 'Count of successful triggers',
	`times_failed` int DEFAULT '0' COMMENT 'Count of failures',
	`locked` datetime,
	`ordering` int NOT NULL DEFAULT 0 COMMENT 'Configurable list ordering',
	`params` text NOT NULL,
	`note` text,
	`created` datetime NOT NULL,
	`created_by` int UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id),
	KEY `idx_type` (`type`),
	KEY `idx_state` (`state`),
	KEY `idx_last_exit` (`last_exit_code`),
	KEY `idx_next_exec` (`next_execution`),
	KEY `idx_locked` (`locked`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

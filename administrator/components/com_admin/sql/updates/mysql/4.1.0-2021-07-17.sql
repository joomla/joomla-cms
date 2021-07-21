--
-- Table structure for table `#__cronjobs`
--

CREATE TABLE IF NOT EXISTS `#__cronjobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL UNIQUE DEFAULT '0',
  `title` varchar(128) NOT NULL UNIQUE,
  -- Job type. Can execute a script or plugin routine
  `type` enum ('script', 'plugin') NOT NULL DEFAULT 'script',
  -- Trigger type, default to PseudoCron (compatible everywhere).
  `trigger` enum ('pseudo_cron', 'cron', 'visit_count') NOT NULL DEFAULT 'pseudo_cron' COMMENT 'Defines how job is triggered',
  `execution_interval` time NOT NULL COMMENT 'Configured time between executions,in HH:MM:SS format',
  `state` tinyint NOT NULL DEFAULT FALSE,
  `last_exit_code` int NOT NULL DEFAULT '0' COMMENT 'Exit code when job was last run',
  `last_execution` datetime COMMENT 'Timestamp of last run',
  `next_execution` datetime COMMENT 'Timestamp of next (planned) run, referred for execution on trigger',
  `times_executed` int DEFAULT '0' COMMENT 'Count of successful triggers',
  `times_failed` int DEFAULT '0' COMMENT 'Count of failures',
  `note` text,
  `created` datetime NOT NULL,
  `created_by` int UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__cronjobs_scripts`
--

CREATE TABLE IF NOT EXISTS `#__cronjobs_scripts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_id` int NOT NULL COMMENT 'Cronjob ID', -- References `#__cronjobs`(id)
  `directory` varchar(1024) NOT NULL,
  `file` varchar(256) NOT NULL,
  PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

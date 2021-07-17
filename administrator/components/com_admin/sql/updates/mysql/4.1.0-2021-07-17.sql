--
-- Table structure for table `#__cronjobs`
--

CREATE TABLE IF NOT EXISTS `#__cronjobs`
(
    `id`                 INT(4)                                      NOT NULL AUTO_INCREMENT,
    `asset_id`           INT(10)                                     NOT NULL UNIQUE DEFAULT '0',
    `title`              varchar(128)                                NOT NULL UNIQUE,
    -- Job type. Can execute a script or plugin routine
    `type`               ENUM ('script', 'plugin')                   NOT NULL        DEFAULT 'script',
    -- Trigger type, default to PseudoCron (compatible everywhere).
    `trigger`            ENUM ('pseudo_cron', 'cron', 'visit_count') NOT NULL        DEFAULT 'pseudo_cron' COMMENT 'Defines how job is triggered',
    `execution_interval` TIME                                        NOT NULL COMMENT 'Configured time between executions,in HH:MM:SS format',
    `state`              TINYINT                                     NOT NULL        DEFAULT FALSE,
    `last_exit_code`     INT(11)                                     NOT NULL        DEFAULT '0' COMMENT 'Exit code when job was last run',
    `last_execution`     DATETIME COMMENT 'Timestamp of last run',
    `next_execution`     DATETIME COMMENT 'Timestamp of next (planned) run, referred for execution on trigger',
    `times_executed`     INT(11)                                                     DEFAULT '0' COMMENT 'Count of successful triggers',
    `times_failed`       INT(11)                                                     DEFAULT '0' COMMENT 'Count of failures',
    `note`               TEXT,
    `created`            DATETIME                                    NOT NULL        DEFAULT '0000-00-00 00:00:00',
    `created_by`         INT(10) UNSIGNED                            NOT NULL        DEFAULT '0',
    PRIMARY KEY (id)
    )
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__cronjobs_scripts`
--

CREATE TABLE IF NOT EXISTS `#__cronjobs_scripts`
(
    `id`        int(4)        NOT NULL AUTO_INCREMENT,
    `job_id`    int(4)        NOT NULL COMMENT 'Cronjob ID', -- References `#__cronjobs`(id)
    `directory` varchar(1024) NOT NULL,
    `file`      varchar(256)  NOT NULL,
    PRIMARY KEY (id)
    )
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table Structure `#__cronjobs` [Main]

CREATE TABLE IF NOT EXISTS `#__cronjobs`
(
    `job_id`         int(4)                                     NOT NULL AUTO_INCREMENT,
    `name`           varchar(128)                               NOT NULL UNIQUE,
    -- Job type. Can execute a script or plugin routine
    `type`           ENUM ('script', 'plugin')                  NOT NULL DEFAULT 'script',
    -- Trigger type, default to PseudoCron (compatible everywhere).
    `trigger`        ENUM ('pseudo_cron', 'cron', 'visit_count') NOT NULL DEFAULT 'pseudo_cron' COMMENT 'Defines how job is triggered',
    `enabled`        BOOL                                       NOT NULL DEFAULT FALSE,
    `last_exit_code` INT(11)                                    NOT NULL DEFAULT 0 COMMENT 'Exit code when job was last run',
    `last_execution` DATETIME                                   NOT NULL COMMENT 'Timestamp of when job was last run',
    `next_execution` DATETIME                                   NOT NULL COMMENT 'Timestamp of when job should next run, referred for execution on trigger',
    `times_executed` INT(11)                                             DEFAULT 0 COMMENT 'Count of  times job has been triggered to run',
    `times_failed`   INT(11)                                             DEFAULT 0 COMMENT 'Count of times job has failed',
    `note`           varchar(128),
    PRIMARY KEY (job_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;


-- Table structure `#__cronjobs_scripts`

CREATE TABLE IF NOT EXISTS `#__cronjobs_scripts`
(
    `script_id` int(4)        NOT NULL AUTO_INCREMENT,
    `job_id`    int(4)        NOT NULL COMMENT 'Cronjob ID',
    `directory` varchar(1024) NOT NULL,
    `file`      varchar(256)  NOT NULL,
    PRIMARY KEY (script_id),
    FOREIGN KEY (job_id) REFERENCES `#__cronjobs` (job_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;


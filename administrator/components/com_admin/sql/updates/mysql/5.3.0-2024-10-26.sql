--
-- Table structure for table `#__scheduler_logs`
--

CREATE TABLE IF NOT EXISTS `#__scheduler_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `taskname` varchar(255) NOT NULL DEFAULT '',
  `tasktype` varchar(128) NOT NULL COMMENT 'unique identifier for job defined by plugin',
  `duration` DECIMAL(5,3) NOT NULL,
  `jobid` int UNSIGNED NOT NULL,
  `taskid` int UNSIGNED NOT NULL,
  `exitcode` int NOT NULL,
  `lastdate` datetime COMMENT 'Timestamp of last run',
  `nextdate` datetime COMMENT 'Timestamp of next (planned) run, referred for execution on trigger',
  PRIMARY KEY (id),
  KEY `idx_taskname` (`taskname`),
  KEY `idx_tasktype` (`tasktype`),
  KEY `idx_lastdate` (`lastdate`),
  KEY `idx_nextdate` (`nextdate`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

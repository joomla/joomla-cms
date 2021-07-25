ALTER TABLE `#__cronjobs`
    MODIFY COLUMN `execution_interval` text COMMENT 'Configured execution interval, cron format';

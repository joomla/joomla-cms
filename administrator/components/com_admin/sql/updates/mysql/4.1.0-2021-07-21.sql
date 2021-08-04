DROP TABLE IF EXISTS `#__cronjobs_scripts`;
ALTER TABLE `#__cronjobs`
    MODIFY `type` varchar(1024) NOT NULL COMMENT 'unique identifier for job defined by plugin',
    ADD COLUMN `params` text NOT NULL AFTER `times_failed`;

-- Modify execution_interval column, changes type to TIME [⚠ experimental]
ALTER TABLE `#__cronjobs`
    MODIFY `execution_interval` TIME;

-- Remove NOT NULL constraint from last_execution
ALTER TABLE `#__cronjobs`
    MODIFY `last_execution` DATETIME COMMENT 'Timestamp of last run';

-- Remove NOT NULL constraint from next_execution
ALTER TABLE `#__cronjobs`
    MODIFY `next_execution` DATETIME COMMENT 'Timestamp of next (planned) run, referred for execution on trigger';

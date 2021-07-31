ALTER TABLE `#__cronjobs`
	-- Rename `execution_interval` to `execution_rules`
	CHANGE `execution_interval` `execution_rules` text COMMENT 'Execution Rules, Unprocessed',
	-- Add column `cron_rules`
	ADD `cron_rules` text COMMENT 'Processed execution rules, crontab-like JSON form';

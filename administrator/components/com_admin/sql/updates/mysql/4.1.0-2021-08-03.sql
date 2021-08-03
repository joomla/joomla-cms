-- Add column `ordering`
ALTER TABLE `#__cronjobs`
	ADD `ordering` int NOT NULL DEFAULT 0 COMMENT 'Configurable list ordering';

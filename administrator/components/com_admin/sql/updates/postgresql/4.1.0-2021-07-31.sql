-- Rename "execution_interval" to "execution_rules"
ALTER TABLE "#__cronjobs"
	RENAME COLUMN "execution_interval" TO "execution_rules";
-- Add column "cron_rules"
ALTER TABLE "#__cronjobs"
	ADD COLUMN "cron_rules" text;

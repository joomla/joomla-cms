-- Modify execution_interval column, changes type to INTERVAL [⚠ experimental]
ALTER TABLE IF EXISTS "#__cronjobs"
    ALTER COLUMN "execution_interval" TYPE INTERVAL;

-- Remove NOT NULL constraint from last_execution
ALTER TABLE IF EXISTS "#__cronjobs"
    ALTER COLUMN "last_execution" TYPE TIMESTAMP;

-- Remove NOT NULL constraint from next_execution
ALTER TABLE IF EXISTS "#__cronjobs"
    ALTER COLUMN "next_execution" TYPE TIMESTAMP;


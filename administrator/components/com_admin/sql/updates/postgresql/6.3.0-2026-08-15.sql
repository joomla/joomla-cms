--
-- Add outdated column to associations table
--

ALTER TABLE "#__associations" ADD COLUMN "outdated" smallint DEFAULT 0 NOT NULL /** CAN FAIL **/;

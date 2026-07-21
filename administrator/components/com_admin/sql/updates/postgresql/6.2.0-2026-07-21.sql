--
-- Add security column to updates table
--

ALTER TABLE "#__updates" ADD "security" smallint NULL DEFAULT NULL;

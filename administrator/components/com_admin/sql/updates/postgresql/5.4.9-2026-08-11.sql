--
-- Add security column to updates table
--

ALTER TABLE "#__updates" ADD COLUMN "security" smallint NULL DEFAULT NULL /** CAN FAIL **/;

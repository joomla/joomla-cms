--
-- Add position column to workflow stages table
--

ALTER TABLE "#__workflow_stages" ADD COLUMN "position" text NULL /** CAN FAIL **/;

--
-- Add previousvisitDate to #__users
--

ALTER TABLE "#__users"
    ADD COLUMN "previousvisitDate" timestamp without time zone /** CAN FAIL **/;

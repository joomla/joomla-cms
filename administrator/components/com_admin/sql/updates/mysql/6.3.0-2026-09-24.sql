--
-- Add previousvisitDate to #__users
--

ALTER TABLE `#__users`
    ADD COLUMN `previousvisitDate` datetime AFTER `lastvisitDate` /** CAN FAIL **/;

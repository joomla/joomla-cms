ALTER TABLE `#__action_logs` MODIFY `log_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `#__action_logs` SET `log_date` = '2005-08-17 00:00:00' WHERE `created_date` = '0000-00-00 00:00:00';

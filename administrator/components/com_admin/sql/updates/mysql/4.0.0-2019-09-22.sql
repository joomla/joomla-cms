ALTER TABLE `#__messages` MODIFY `date_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `#__messages` SET `date_time` = '2005-08-17 00:00:00' WHERE `date_time` = '0000-00-00 00:00:00';

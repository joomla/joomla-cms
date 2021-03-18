ALTER TABLE `#__extensions` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__extensions` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

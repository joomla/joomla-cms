ALTER TABLE `#__newsfeeds` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__newsfeeds` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__newsfeeds` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__newsfeeds` SET `publish_up` =  NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__newsfeeds` SET `publish_down` =  NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__newsfeeds` SET `checked_out_time` =  NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

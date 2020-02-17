ALTER TABLE `#__redirect_links` MODIFY `created_date` datetime NOT NULL;
ALTER TABLE `#__redirect_links` MODIFY `modified_date` datetime NOT NULL;

UPDATE `#__redirect_links` SET `modified_date` = `created_date` WHERE `modified_date` = '0000-00-00 00:00:00';

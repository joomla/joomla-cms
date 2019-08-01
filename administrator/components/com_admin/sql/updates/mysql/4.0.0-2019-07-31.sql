ALTER TABLE `#__content`
    MODIFY `created` DATETIME NULL DEFAULT NULL,
    MODIFY `modified` DATETIME NULL DEFAULT NULL,
    MODIFY `publish_up` DATETIME NULL DEFAULT NULL,
    MODIFY `publish_down` DATETIME NULL DEFAULT NULL;

UPDATE `#__content` SET `created` = NULL WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `modified` = NULL WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';

ALTER TABLE `#__cronjobs`
    ADD COLUMN `asset_id` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__cronjobs`
    ADD COLUMN `created` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__cronjobs`
    ADD COLUMN `created_by` INT(10) NOT NULL DEFAULT '0';

ALTER TABLE `#__history`
    ADD COLUMN `is_current` TINYINT NOT NULL DEFAULT 0 /** CAN FAIL **/;
ALTER TABLE `#__history`
    ADD COLUMN `is_legacy` TINYINT NOT NULL DEFAULT 0 /** CAN FAIL **/;
UPDATE `#__history` SET `is_legacy` = 1;

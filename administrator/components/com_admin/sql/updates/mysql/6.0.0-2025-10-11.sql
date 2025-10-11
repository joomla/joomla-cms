ALTER TABLE `#__history`
    ADD COLUMN `is_current` TINYINT NOT NULL DEFAULT 0 /** CAN FAIL **/;

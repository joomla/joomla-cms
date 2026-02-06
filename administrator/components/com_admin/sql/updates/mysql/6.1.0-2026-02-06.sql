ALTER TABLE `#__guidedtours` 
    ADD COLUMN `url_type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `extensions`;

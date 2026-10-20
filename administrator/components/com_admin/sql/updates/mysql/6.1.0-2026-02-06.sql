ALTER TABLE `#__guidedtours` 
    ADD COLUMN `url_type` VARCHAR(255) NOT NULL DEFAULT '' AFTER `extensions`;

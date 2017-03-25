ALTER TABLE `#__redirect_links` DROP INDEX `idx_link_old`;
ALTER TABLE `#__redirect_links` MODIFY `old_url` VARCHAR(2048) NOT NULL;

--
-- The following statement had to be modified for 3.6.0 by removing the
-- NOT NULL, which was wrong because not consistent with new install.
-- See also 3.6.0-2016-04-06.sql for updating 3.5.0 or 3.5.1
--
ALTER TABLE `#__redirect_links` MODIFY `new_url` VARCHAR(2048);

ALTER TABLE `#__redirect_links` MODIFY `referer` VARCHAR(2048) NOT NULL;
ALTER TABLE `#__redirect_links` ADD INDEX `idx_old_url` (`old_url`(100));

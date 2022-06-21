ALTER TABLE `#__redirect_links` DROP INDEX `idx_link_modifed`;
ALTER TABLE `#__redirect_links` ADD INDEX `idx_link_modified` (`modified_date`);

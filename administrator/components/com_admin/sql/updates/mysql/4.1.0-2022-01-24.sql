ALTER TABLE `#__redirect_links` DROP INDEX `idx_link_modifed` /** CAN FAIL **/;
ALTER TABLE `#__redirect_links` ADD INDEX `idx_link_modified` (`modified_date`) /** CAN FAIL **/;

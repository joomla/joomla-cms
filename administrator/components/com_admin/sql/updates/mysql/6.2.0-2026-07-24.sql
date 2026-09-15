ALTER TABLE `#__finder_links` ADD COLUMN `cat_access` int NOT NULL DEFAULT 1 AFTER `access` /** CAN FAIL **/;
ALTER TABLE `#__finder_links` DROP INDEX `idx_published_list` /** CAN FAIL **/;
ALTER TABLE `#__finder_links` DROP INDEX `idx_published_sale` /** CAN FAIL **/;
ALTER TABLE `#__finder_links` ADD KEY `idx_published_list` (`published`,`state`,`access`,`cat_access`,`publish_start_date`,`publish_end_date`,`list_price`) /** CAN FAIL **/;
ALTER TABLE `#__finder_links` ADD KEY `idx_published_sale` (`published`,`state`,`access`,`cat_access`,`publish_start_date`,`publish_end_date`,`sale_price`) /** CAN FAIL **/;

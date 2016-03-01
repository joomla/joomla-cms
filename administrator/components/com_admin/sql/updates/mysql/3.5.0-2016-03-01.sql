ALTER TABLE `#__redirect_links` DROP KEY `idx_link_old`;
ALTER TABLE `#__redirect_links` CHANGE `old_url` `old_url` VARCHAR(2048) NOT NULL;
ALTER TABLE `#__redirect_links` CHANGE `new_url` `new_url` VARCHAR(2048) NOT NULL;
ALTER TABLE `#__redirect_links` CHANGE `referer` `referer` VARCHAR(2048) NOT NULL;
ALTER TABLE `#__redirect_links` ADD KEY `idx_old_url` (`old_url`(100));

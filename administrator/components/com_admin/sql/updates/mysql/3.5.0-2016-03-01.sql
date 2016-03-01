ALTER TABLE `#__redirect_links` DROP KEY `idx_link_old`;
ALTER TABLE `#__redirect_links` MODIFY `old_url` VARCHAR(2048) NOT NULL;
ALTER TABLE `#__redirect_links` MODIFY `new_url` VARCHAR(2048) NOT NULL;
ALTER TABLE `#__redirect_links` MODIFY `referer` VARCHAR(2048) NOT NULL;
ALTER TABLE `#__redirect_links` ADD KEY `idx_old_url` (`old_url`(100));

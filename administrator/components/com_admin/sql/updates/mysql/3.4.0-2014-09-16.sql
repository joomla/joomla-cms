ALTER TABLE `#__redirect_links` ADD COLUMN `header` smallint(3) NOT NULL DEFAULT 301;
ALTER TABLE `#__redirect_links` MODIFY `new_url` varchar(255);

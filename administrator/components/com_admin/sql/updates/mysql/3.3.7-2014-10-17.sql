ALTER TABLE `#__redirect_links` DROP INDEX `idx_link_old`;

ALTER TABLE `#__redirect_links` CHANGE `old_url` `old_url` varchar(2083) NOT NULL;
ALTER TABLE `#__redirect_links` CHANGE `new_url` `new_url` varchar(2083) NOT NULL;
ALTER TABLE `#__redirect_links` CHANGE `referer` `referer` varchar(2083) NOT NULL;

ALTER TABLE `#__redirect_links` ADD header smallint(3) NOT NULL DEFAULT 301;
ALTER TABLE `#__redirect_links` MODIFY new_url varchar(255);

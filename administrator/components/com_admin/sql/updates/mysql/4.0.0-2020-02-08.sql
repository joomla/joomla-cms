ALTER TABLE `#__banners` MODIFY `metakey` text;
ALTER TABLE `#__banner_clients` MODIFY `metakey` text;
ALTER TABLE `#__contact_details` MODIFY `metakey` text;
ALTER TABLE `#__content` MODIFY `metakey` text;
ALTER TABLE `#__languages` MODIFY `metakey` text;
ALTER TABLE `#__newsfeeds` MODIFY `metakey` text;
ALTER TABLE `#__tags` MODIFY `metakey` varchar(1024) NOT NULL DEFAULT '';

-- Normalize categories table default values.
ALTER TABLE `#__categories` MODIFY `title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__categories` MODIFY `description` mediumtext NOT NULL DEFAULT '';
ALTER TABLE `#__categories` MODIFY `params` text NOT NULL DEFAULT '';
ALTER TABLE `#__categories` MODIFY `metadesc` varchar(1024) NOT NULL DEFAULT '' COMMENT 'The meta description for the page.';
ALTER TABLE `#__categories` MODIFY `metakey` varchar(1024) NOT NULL DEFAULT '' COMMENT 'The meta keywords for the page.';
ALTER TABLE `#__categories` MODIFY `metadata` varchar(2048) NOT NULL DEFAULT '' COMMENT 'JSON encoded metadata properties.';
ALTER TABLE `#__categories` MODIFY `language` char(7) NOT NULL DEFAULT '';

ALTER TABLE `#__languages` ADD INDEX `idx_published_ordering` (`published`,`ordering`);
ALTER TABLE `#__session` DROP PRIMARY KEY, ADD PRIMARY KEY (`session_id`(32));
ALTER TABLE `#__session` DROP INDEX `time`, ADD INDEX `time` (`time`(10));
ALTER TABLE `#__template_styles` ADD INDEX `idx_client_id` (`client_id`);
ALTER TABLE `#__contentitem_tag_map` ADD INDEX `idx_alias_item_id` (`type_alias`(100),`content_item_id`);
ALTER TABLE `#__extensions` ADD INDEX `idx_type_ordering` (`type`,`ordering`);
ALTER TABLE `#__menu` ADD INDEX `idx_client_id_published_lft` (`client_id`,`published`,`lft`);
ALTER TABLE `#__viewlevels` ADD INDEX `idx_ordering_title` (`ordering`,`title`);

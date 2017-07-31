-- Normalize ucm_content_table default values.
ALTER TABLE `#__ucm_content` MODIFY `core_title` varchar(400) NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_body` mediumtext NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_checked_out_time` varchar(255) NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__ucm_content` MODIFY `core_params` text NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_metadata` varchar(2048) NOT NULL DEFAULT '' COMMENT 'JSON encoded metadata properties.';
ALTER TABLE `#__ucm_content` MODIFY `core_language` char(7) NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__ucm_content` MODIFY `core_publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__ucm_content` MODIFY `core_content_item_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'ID from the individual type table';
ALTER TABLE `#__ucm_content` MODIFY `asset_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.';
ALTER TABLE `#__ucm_content` MODIFY `core_images` text NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_urls` text NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_metakey` text NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_metadesc` text NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_xreference` varchar(50) NOT NULL DEFAULT '' COMMENT 'A reference to enable linkages to external data sets.';
ALTER TABLE `#__ucm_content` MODIFY `core_type_id` int(10) unsigned NOT NULL DEFAULT 0;

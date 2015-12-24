-- Index and field changes to cater for UTF-8 Multibyte (utf8mb4)
ALTER TABLE `#__menu` DROP KEY `idx_client_id_parent_id_alias_language`, ADD UNIQUE KEY `idx_client_id_parent_id_alias_language` (`client_id`,`parent_id`,`alias`(191),`language`);
ALTER TABLE `#__redirect_links` DROP KEY `idx_link_old`, ADD UNIQUE KEY `idx_link_old` (`old_url`(191));
ALTER TABLE `#__menu` DROP  KEY `idx_path`, ADD KEY `idx_path` (`path`(191));
ALTER TABLE `#__session` MODIFY `session_id` varchar(191) NOT NULL DEFAULT '';
ALTER TABLE `#__user_keys` MODIFY `series` varchar(191) NOT NULL;
ALTER TABLE `#__update_sites_extensions` ENGINE=InnoDB;
ALTER TABLE `#__banners` MODIFY `alias` varchar(191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__categories` MODIFY `alias` varchar(191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__contact_details` MODIFY `alias` varchar(191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__content` MODIFY `alias` varchar(191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__menu` MODIFY `alias` varchar(191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'The SEF alias of the menu item.';
ALTER TABLE `#__newsfeeds` MODIFY `alias` varchar(191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__tags` MODIFY `alias` varchar(191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_alias` varchar(191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';

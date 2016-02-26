--
-- Changes to be compatible with UTF-8 Multibyte (utf8mb4) for MySQL
--

-- Step 1: Limit indexes to first 100 so their max allowed lengths would not get exceeded with utf8mb4
ALTER TABLE `#__categories` DROP KEY `idx_alias`, ADD KEY `idx_alias` (`alias`(100));
ALTER TABLE `#__menu` DROP KEY `idx_alias`, ADD KEY `idx_alias` (`alias`(100));
ALTER TABLE `#__menu` DROP KEY `idx_client_id_parent_id_alias_language`, ADD UNIQUE KEY `idx_client_id_parent_id_alias_language` (`client_id`,`parent_id`,`alias`(100),`language`);
ALTER TABLE `#__redirect_links` DROP KEY `idx_link_old`, ADD UNIQUE KEY `idx_link_old` (`old_url`(100));
ALTER TABLE `#__tags` DROP KEY `idx_alias`, ADD KEY `idx_alias` (`alias`(100));
ALTER TABLE `#__ucm_content` DROP KEY `idx_alias`, ADD KEY `idx_alias` (`core_alias`(100));

-- Step 2: Enlarge columns to avoid data loss on a later conversion to utf8mb4
ALTER TABLE `#__banners` MODIFY `alias` varchar(400) NOT NULL DEFAULT '';
ALTER TABLE `#__categories` MODIFY `alias` varchar(400) NOT NULL DEFAULT '';
ALTER TABLE `#__contact_details` MODIFY `alias` varchar(400) NOT NULL DEFAULT '';
ALTER TABLE `#__content` MODIFY `alias` varchar(400) NOT NULL DEFAULT '';
ALTER TABLE `#__menu` MODIFY `alias` varchar(400) NOT NULL COMMENT 'The SEF alias of the menu item.';
ALTER TABLE `#__newsfeeds` MODIFY `alias` varchar(400) NOT NULL DEFAULT '';
ALTER TABLE `#__session` MODIFY `session_id` varchar(191) NOT NULL DEFAULT '';
ALTER TABLE `#__tags` MODIFY `alias` varchar(400) NOT NULL DEFAULT '';
ALTER TABLE `#__ucm_content` MODIFY `core_alias` varchar(400) NOT NULL DEFAULT '';
ALTER TABLE `#__user_keys` MODIFY `series` varchar(191) NOT NULL;

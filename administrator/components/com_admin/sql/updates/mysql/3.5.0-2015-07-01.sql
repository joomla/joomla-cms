-- WARNING: Do not rename this file with a different date. It MUST run before any other table updates when upgrading to Joomla! 3.5.0

-- Index and field changes to cater for UTF-8 Multibyte (utf8mb4)
ALTER TABLE `#__menu` DROP KEY `idx_client_id_parent_id_alias_language`, ADD UNIQUE KEY `idx_client_id_parent_id_alias_language` (`client_id`,`parent_id`,`alias`(191),`language`);

ALTER TABLE `#__redirect_links` DROP KEY `idx_link_old`, ADD UNIQUE KEY `idx_link_old` (`old_url`(191));

ALTER TABLE `#__menu` DROP  KEY `idx_path`, ADD KEY `idx_path` (`path`(191));

ALTER TABLE `#__session` MODIFY `session_id` varchar(191) NOT NULL DEFAULT '';

ALTER TABLE `#__user_keys` MODIFY `series` varchar(191) NOT NULL;

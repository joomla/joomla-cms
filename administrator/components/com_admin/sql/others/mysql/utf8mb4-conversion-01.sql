--
-- Step 1 of the UTF-8 Multibyte (utf8mb4) conversion for MySQL
--
-- Drop indexes which will be added again in step 2, utf8mb4-conversion-02.sql.
--
-- Do not rename this file or any other of the utf8mb4-conversion-*.sql
-- files unless you want to change PHP code, too.
--
-- This file here will be processed ignoring any exceptions caused by indexes
-- to be dropped do not exist.
--
-- The file for step 2 will the be processed with reporting exceptions.
--

ALTER TABLE `#__banners` DROP KEY `idx_metakey_prefix`;
ALTER TABLE `#__banner_clients` DROP KEY `idx_metakey_prefix`;
ALTER TABLE `#__categories` DROP KEY `idx_path`;
ALTER TABLE `#__categories` DROP KEY `idx_alias`;
ALTER TABLE `#__content_types` DROP KEY `idx_alias`;
ALTER TABLE `#__finder_links` DROP KEY `idx_title`;
ALTER TABLE `#__menu` DROP KEY `idx_alias`;
ALTER TABLE `#__menu` DROP KEY `idx_client_id_parent_id_alias_language`;
ALTER TABLE `#__redirect_links` DROP KEY `idx_old_url`;
ALTER TABLE `#__tags` DROP KEY `idx_path`;
ALTER TABLE `#__tags` DROP KEY `idx_alias`;
ALTER TABLE `#__ucm_content` DROP KEY `idx_alias`;
ALTER TABLE `#__ucm_content` DROP KEY `idx_title`;
ALTER TABLE `#__ucm_content` DROP KEY `idx_content_type`;

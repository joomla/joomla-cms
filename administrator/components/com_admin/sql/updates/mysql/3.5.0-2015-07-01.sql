--
-- Enlarge columns to avoid data loss on a later UTF-8 Multibyte (utf8mb4) conversion for MySQL
--

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

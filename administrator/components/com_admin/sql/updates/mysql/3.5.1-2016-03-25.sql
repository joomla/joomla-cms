--
-- Make #__user_keys.user_id fit to #__users.username
--

ALTER TABLE `#__user_keys` MODIFY `user_id` varchar(150) NOT NULL;

--
-- Extend structure of #__utf8_conversion for statement change detection and
-- for beign used for extensions, too
--

ALTER TABLE `#__utf8_conversion` ADD COLUMN `extension_id` int(11) NOT NULL DEFAULT 0, ADD PRIMARY KEY(`extension_id`);
ALTER TABLE `#__utf8_conversion` ADD COLUMN `md5_file1` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';
ALTER TABLE `#__utf8_conversion` ADD COLUMN `md5_file2` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';

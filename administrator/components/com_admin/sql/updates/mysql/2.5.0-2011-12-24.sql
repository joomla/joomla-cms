ALTER TABLE `#__menu` DROP INDEX `idx_client_id_parent_id_alias`;

--
-- The following statment had to be modified for utf8mb4 in Joomla! 3.5.1, changing
-- `alias` to `alias`(100)
--

ALTER TABLE `#__menu` ADD UNIQUE `idx_client_id_parent_id_alias_language` ( `client_id` , `parent_id` , `alias`(100) , `language` );
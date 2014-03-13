ALTER TABLE `#__menu` DROP INDEX `idx_client_id_parent_id_alias`;

ALTER TABLE `#__menu` ADD UNIQUE `idx_client_id_parent_id_alias_language` ( `client_id` , `parent_id` , `alias` , `language` );
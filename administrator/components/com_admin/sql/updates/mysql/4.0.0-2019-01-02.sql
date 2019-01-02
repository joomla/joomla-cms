RENAME TABLE `#__ucm_history` TO `#__history`;
ALTER TABLE `#__history` CHANGE COLUMN `ucm_item_id` `item_id` VARCHAR(50) NOT NULL DEFAULT '' AFTER `version_id`;
UPDATE #__history AS h INNER JOIN #__content_types AS c ON h.ucm_type_id = c.type_id SET h.item_id = CONCAT(c.type_alias, '.', h.item_id);
ALTER TABLE `#__history` DROP COLUMN `ucm_type_id`;
ALTER TABLE `#__extensions` ADD COLUMN `package_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Parent package ID for extensions installed as a package.' AFTER `extension_id`;

UPDATE `#__extensions` AS `e1`
INNER JOIN (SELECT `extension_id` FROM `#__extensions` WHERE `type` = 'package' AND `element` = 'pkg_en-GB') AS `e2`
SET `e1`.`package_id` = `e2`.`extension_id`
WHERE `e1`.`type`= 'language' AND `e1`.`element` = 'en-GB';

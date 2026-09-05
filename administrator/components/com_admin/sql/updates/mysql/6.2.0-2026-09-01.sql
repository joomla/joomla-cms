ALTER TABLE `#__modules_menu` ADD COLUMN `inherit` tinyint NOT NULL DEFAULT 0 AFTER `menuid` /** CAN FAIL **/;

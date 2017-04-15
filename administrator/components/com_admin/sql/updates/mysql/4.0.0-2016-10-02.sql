DELETE FROM `#__extensions` WHERE `extension_id` = 504;
DELETE FROM `#__template_styles` WHERE `template` = 'hathor';
ALTER TABLE `#__user_keys` DROP COLUMN `invalid`;
